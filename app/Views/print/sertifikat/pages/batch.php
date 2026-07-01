<?php
/**
 * Batch sertifikat view — render banyak sertifikat sekaligus.
 *
 * Dua jalur:
 *  - iframe ($is_iframe=true):  browser-side render (QRCode.js → html2canvas → JSZip → saveAs)
 *  - plain HTML:                 untuk render lokal via Playwright (tools/sertifikat-renderer.js)
 *
 * @var array  $cards          [type, peserta, nomor, nama, kategori, kontingen, sekolah, qrcode_url, filename]
 * @var array  $layout         Layout config dari SertifikatService
 * @var string $background_url
 * @var bool   $hide_bg
 * @var bool   $is_iframe
 */
$bgUrl = (string) ($background_url ?? '');
$hideB = !empty($hide_bg);
?>
<style>
    .sertifikat-batch {
        display: flex;
        flex-wrap: wrap;
        gap: 4mm;
        padding: 4mm;
        align-items: flex-start;
        background: #f1f3f5;
    }

    .sertifikat-export {
        page-break-inside: avoid;
    }

    /* Override body background untuk batch */
    body {
        background: #f1f3f5;
    }
</style>

<div class="sertifikat-batch">
    <?php foreach (($cards ?? []) as $i => $card) : ?>
        <?php
        $filename = $card['filename'] ?? ('sertifikat_' . ($i + 1));
        $idPeserta = $card['type'] === 'tanding'
            ? (int) ($card['peserta']->id_peserta_tanding ?? 0)
            : (int) ($card['peserta']->id_peserta_seni ?? 0);
        ?>
        <div class="sertifikat-export"
             data-filename="<?= esc($filename) ?>"
             data-card-index="<?= $i ?>"
             data-card-id="<?= $idPeserta ?>"
             data-card-type="<?= esc($card['type']) ?>">
            <div class="sertifikat"<?= (!$hideB && $bgUrl) ? ' style="background-image:url(' . esc($bgUrl) . ')"' : '' ?>>
                <div class="nomor"><h1><?= esc($card['nomor'] ?? '') ?></h1></div>
                <div class="nama"><h1><?= esc($card['nama'] ?? '') ?></h1></div>
                <div class="kategori"><h2><?= esc($card['kategori'] ?? '') ?></h2></div>
                <div class="kontingen"><h3><?= esc($card['kontingen'] ?? '') ?></h3></div>
                <div class="sekolah"><h3><?= esc($card['sekolah'] ?? '') ?></h3></div>
                <div class="qrcode"><div class="qr-placeholder" id="qr_<?= $i ?>"></div></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($is_iframe ?? false) : ?>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script src="<?= base_url('assets/qrcode/js/qrcode.min.js') ?>"></script>

<script>
(function () {
    'use strict';

    var SCALE = 2;
    var cards = document.querySelectorAll('.sertifikat-export');
    var total = cards.length;
    var CHUNK_SIZE = total > 20 ? 20 : total; // A4 lebih besar dr A6 → chunk kecil
    var processed = 0;
    var failed = [];
    var zip = new JSZip();
    var chunkIndex = 1;

    var qrData = <?= json_encode(array_values(array_map(function ($c, $idx) {
        return [
            'index'  => $idx,
            'url'    => (string) ($c['qrcode_url'] ?? ''),
            'type'   => (string) $c['type'],
            'id'     => $c['type'] === 'tanding'
                ? (int) ($c['peserta']->id_peserta_tanding ?? 0)
                : (int) ($c['peserta']->id_peserta_seni ?? 0),
        ];
    }, $cards ?? [], array_keys($cards ?? []))), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function notifyParent(payload) {
        try {
            parent.postMessage(payload, '*');
        } catch (e) { /* noop */ }
    }

    function renderQRCodes() {
        if (typeof QRCode === 'undefined') return;
        qrData.forEach(function (q) {
            if (!q.url) return;
            var el = document.getElementById('qr_' + q.index);
            if (!el) return;
            try {
                new QRCode(el, {
                    text: q.url,
                    width: 256,
                    height: 256,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            } catch (e) { /* skip */ }
        });
    }

    function captureCard(card, attempt) {
        attempt = attempt || 0;
        // Sembunyikan gambar gagal load
        var imgs = card.querySelectorAll('img');
        Array.prototype.forEach.call(imgs, function (img) {
            if (!img.complete || img.naturalWidth === 0) {
                img.style.display = 'none';
            }
        });
        return html2canvas(card, {
            scale: SCALE,
            useCORS: true,
            allowTaint: false,
            backgroundColor: '#ffffff',
            imageTimeout: 15000,
            logging: false,
            letterRendering: true,
            foreignObjectRendering: false,
            removeContainer: true
        }).catch(function (err) {
            if (attempt < 2) {
                return new Promise(function (resolve) {
                    setTimeout(function () {
                        resolve(captureCard(card, attempt + 1));
                    }, 250);
                });
            }
            throw err;
        });
    }

    function canvasToBlob(canvas) {
        return new Promise(function (resolve, reject) {
            try {
                if (canvas.toBlob) {
                    canvas.toBlob(function (blob) {
                        if (blob) resolve(blob);
                        else reject(new Error('toBlob null'));
                    }, 'image/png');
                } else {
                    var dataUrl = canvas.toDataURL('image/png');
                    var base64 = dataUrl.replace(/^data:image\/png;base64,/, '');
                    resolve(base64);
                }
            } catch (e) { reject(e); }
        });
    }

    function processCard(idx) {
        if (idx >= total) return finish();

        var card = cards[idx];
        var filename = card.dataset.filename || ('sertifikat_' + (idx + 1));

        captureCard(card, 0)
            .then(function (canvas) {
                return canvasToBlob(canvas).then(function (data) {
                    if (typeof data === 'string') {
                        zip.file(filename + '.png', data, { base64: true });
                    } else {
                        zip.file(filename + '.png', data);
                    }
                    processed++;
                    notifyParent({
                        type: 'sertifikat-progress',
                        processed: processed,
                        total: total,
                        failed: failed.length,
                        current: filename
                    });
                    if (CHUNK_SIZE > 0 && processed > 0 && processed % CHUNK_SIZE === 0 && idx < total - 1) {
                        flushZip(false).then(function () {
                            hideProcessedCards(idx);
                            return new Promise(function (r) { setTimeout(r, 800); });
                        }).then(function () {
                            processCard(idx + 1);
                        }).catch(function (err) {
                            notifyParent({ type: 'sertifikat-error', message: 'Gagal ZIP: ' + (err && err.message ? err.message : err) });
                        });
                        return;
                    }
                    processCard(idx + 1);
                });
            })
            .catch(function (err) {
                failed.push(filename);
                console.error('Gagal: ' + filename, err);
                notifyParent({ type: 'sertifikat-progress', processed: processed, total: total, failed: failed.length, current: filename });
                processCard(idx + 1);
            });
    }

    function chunkName() {
        var stamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
        var totalChunks = CHUNK_SIZE > 0 ? Math.ceil(total / CHUNK_SIZE) : 1;
        if (totalChunks <= 1) return 'sertifikat-batch-' + stamp + '.zip';
        return 'sertifikat-batch-' + stamp + '-part-' + String(chunkIndex).padStart(2, '0') + '-of-' + String(totalChunks).padStart(2, '0') + '.zip';
    }

    function hideProcessedCards(lastIdx) {
        for (var i = 0; i <= lastIdx; i++) {
            if (cards[i]) cards[i].remove();
        }
    }

    function flushZip(isFinal) {
        return zip.generateAsync({ type: 'blob' })
            .then(function (blob) {
                try { saveAs(blob, chunkName()); } catch (e) { console.error('saveAs gagal:', e); }
                zip = new JSZip();
                chunkIndex++;
                notifyParent({
                    type: isFinal ? 'sertifikat-complete' : 'sertifikat-chunk',
                    processed: processed, failed: failed, total: total,
                    chunk_size: CHUNK_SIZE, chunk_index: chunkIndex - 1
                });
            });
    }

    function finish() {
        if (processed === 0) {
            notifyParent({ type: 'sertifikat-error', message: 'Tidak ada sertifikat yang berhasil diproses.' });
            return;
        }
        flushZip(true).catch(function (err) {
            console.error('generateAsync gagal:', err);
            notifyParent({ type: 'sertifikat-error', message: 'Gagal membuat ZIP: ' + (err && err.message ? err.message : err) });
        });
    }

    function waitForAssets() {
        var fontReady = document.fonts && document.fonts.ready ? document.fonts.ready.catch(function () {}) : Promise.resolve();
        var imageReady = Promise.all(Array.prototype.map.call(document.images || [], function (img) {
            if (img.complete) return Promise.resolve();
            if (img.decode) return img.decode().catch(function () {});
            return new Promise(function (resolve) {
                img.onload = resolve;
                img.onerror = resolve;
            });
        }));
        return Promise.all([fontReady, imageReady]);
    }

    window.addEventListener('load', function () {
        notifyParent({ type: 'sertifikat-start', total: total });
        if (total === 0) {
            notifyParent({ type: 'sertifikat-error', message: 'Tidak ada peserta untuk dicetak.' });
            return;
        }
        waitForAssets()
            .then(function () {
                renderQRCodes();
                return new Promise(function (r) { setTimeout(r, 300); });
            })
            .then(function () { processCard(0); });
    });
})();
</script>
<?php endif; ?>
