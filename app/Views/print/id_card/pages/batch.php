<?php
/**
 * @var array $cards          List<array{type, peserta, partai?, data_penampilan?, data_battle?, barcode_value, filename}>
 * @var int   $scale          html2canvas scale (2-10, default 4)
 * @var bool  $is_iframe      true bila view ini di-load via iframe dari halaman parent
 * @var string $background_url
 */
?>
<style>
    .kartu-id-batch {
        display: flex;
        flex-wrap: wrap;
        gap: 4mm;
        padding: 4mm;
        align-items: flex-start;
    }

    .kartu-id-export {
        page-break-inside: avoid;
        background: #fff;
    }

    @media screen {
        body {
            background: #f1f3f5;
        }
    }
</style>

<div class="kartu-id-batch">
    <?php foreach (($cards ?? []) as $i => $card) : ?>
        <?php
        $filename = $card['filename'] ?? ('kartu_' . ($i + 1));
        $idCard = $card['type'] === 'tanding'
            ? (int) ($card['peserta']->id_peserta_tanding ?? 0)
            : (int) ($card['peserta']->id_peserta_seni ?? 0);
        ?>
        <div class="kartu-id-export"
             data-filename="<?= esc($filename) ?>"
             data-card-index="<?= $i ?>"
             data-card-id="<?= $idCard ?>"
             data-card-type="<?= esc($card['type']) ?>">
            <?php if ($card['type'] === 'tanding') : ?>
                <?= view('print/id_card/components/card_tanding', [
                    'peserta'         => $card['peserta'],
                    'partai'          => $card['partai'] ?? [],
                    'background_url'  => $background_url ?? '',
                ]) ?>
            <?php else : ?>
                <?= view('print/id_card/components/card_seni', [
                    'peserta'          => $card['peserta'],
                    'partai'           => $card['partai'] ?? [],
                    'data_penampilan'  => $card['data_penampilan'] ?? [],
                    'data_battle'      => $card['data_battle'] ?? [],
                    'background_url'   => $background_url ?? '',
                ]) ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($is_iframe ?? false) : ?>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>
(function () {
    'use strict';

    var SCALE = <?= (int) ($scale ?? 4) ?>;
    var MAX_RETRY = 2;

    var barcodeMeta = <?= json_encode(array_map(static function ($c) {
        $id = $c['type'] === 'tanding'
            ? (int) ($c['peserta']->id_peserta_tanding ?? 0)
            : (int) ($c['peserta']->id_peserta_seni ?? 0);
        return [
            'type'  => (string) $c['type'],
            'id'    => $id,
            'value' => (string) ($c['barcode_value'] ?? ''),
        ];
    }, $cards ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    var cards = document.querySelectorAll('.kartu-id-export');
    var total = cards.length;
    var CHUNK_SIZE = total > 50 ? 50 : total;
    var processed = 0;
    var failed = [];
    var zip = new JSZip();
    var chunkIndex = 1;

    function notifyParent(payload) {
        try {
            parent.postMessage(payload, '*');
        } catch (e) {
            /* noop — top-level navigation may have changed */
        }
    }

    function renderBarcodes() {
        if (typeof JsBarcode === 'undefined') {
            return;
        }
        barcodeMeta.forEach(function (b) {
            if (!b.value) {
                return;
            }
            var selector = '#bar_' + b.type + '_' + b.id;
            var el = document.querySelector(selector);
            if (!el) {
                return;
            }
            try {
                JsBarcode(el, b.value, {
                    format: 'ean8',
                    width: 1,
                    height: 30,
                    displayValue: false,
                    margin: 0
                });
            } catch (e) {
                /* element may not be SVG-compatible — skip silently */
            }
        });
    }

    function captureCard(card, attempt) {
        attempt = attempt || 0;
        // Sembunyikan gambar yang gagal load supaya html2canvas tidak gagal /
        // membuat canvas ter-taint.
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
            if (attempt < MAX_RETRY) {
                return new Promise(function (resolve) {
                    setTimeout(function () {
                        resolve(captureCard(card, attempt + 1));
                    }, 250);
                });
            }
            throw err;
        });
    }

    // Convert canvas → Blob (lebih hemat memori & menghindari error decode base64
    // di JSZip). Fallback ke toDataURL bila toBlob tidak tersedia.
    function canvasToBlob(canvas) {
        return new Promise(function (resolve, reject) {
            try {
                if (canvas.toBlob) {
                    canvas.toBlob(function (blob) {
                        if (blob) {
                            resolve(blob);
                        } else {
                            reject(new Error('toBlob menghasilkan null (canvas mungkin ter-taint).'));
                        }
                    }, 'image/png');
                } else {
                    var dataUrl = canvas.toDataURL('image/png');
                    var base64 = dataUrl.replace(/^data:image\/png;base64,/, '');
                    resolve(base64);
                }
            } catch (e) {
                reject(e);
            }
        });
    }

    function processCard(idx) {
        if (idx >= total) {
            return finish();
        }

        var card = cards[idx];
        var filename = card.dataset.filename || ('kartu_' + (idx + 1));

        captureCard(card, 0)
            .then(function (canvas) {
                return canvasToBlob(canvas).then(function (data) {
                    // data bisa Blob atau base64 string (fallback)
                    if (typeof data === 'string') {
                        zip.file(filename + '.png', data, { base64: true });
                    } else {
                        zip.file(filename + '.png', data);
                    }
                    processed++;
                    notifyParent({
                        type: 'id-card-progress',
                        processed: processed,
                        total: total,
                        failed: failed.length,
                        current: filename
                    });
                    if (CHUNK_SIZE > 0 && processed > 0 && processed % CHUNK_SIZE === 0 && idx < total - 1) {
                        flushZip(false).then(function () {
                            hideProcessedCards(idx);
                            return pauseBetweenChunks();
                        }).then(function () {
                            processCard(idx + 1);
                        }).catch(function (err) {
                            notifyParent({
                                type: 'id-card-error',
                                message: 'Gagal membuat file ZIP chunk: ' + (err && err.message ? err.message : err)
                            });
                        });
                        return;
                    }

                    processCard(idx + 1);
                });
            })
            .catch(function (err) {
                failed.push(filename);
                if (window.console) {
                    console.error('Gagal memproses kartu "' + filename + '":', err);
                }
                notifyParent({
                    type: 'id-card-progress',
                    processed: processed,
                    total: total,
                    failed: failed.length,
                    current: filename
                });
                processCard(idx + 1);
            });
    }

    function chunkName() {
        var stamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
        var totalChunks = CHUNK_SIZE > 0 ? Math.ceil(total / CHUNK_SIZE) : 1;
        if (totalChunks <= 1) {
            return 'id-card-batch-' + stamp + '.zip';
        }

        return 'id-card-batch-' + stamp + '-part-' + String(chunkIndex).padStart(2, '0') + '-of-' + String(totalChunks).padStart(2, '0') + '.zip';
    }

    function hideProcessedCards(lastIdx) {
        for (var i = 0; i <= lastIdx; i++) {
            if (cards[i]) {
                cards[i].remove();
            }
        }
    }

    function pauseBetweenChunks() {
        return new Promise(function (resolve) {
            setTimeout(resolve, 800);
        });
    }

    function flushZip(isFinal) {
        return zip.generateAsync({ type: 'blob' })
            .then(function (blob) {
                try {
                    saveAs(blob, chunkName());
                } catch (e) {
                    if (window.console) { console.error('saveAs gagal:', e); }
                }

                zip = new JSZip();
                chunkIndex++;

                notifyParent({
                    type: isFinal ? 'id-card-complete' : 'id-card-chunk',
                    processed: processed,
                    failed: failed,
                    total: total,
                    chunk_size: CHUNK_SIZE,
                    chunk_index: chunkIndex - 1
                });
            });
    }

    function finish() {
        if (processed === 0) {
            notifyParent({
                type: 'id-card-error',
                message: 'Tidak ada kartu yang berhasil diproses (semua ' + total + ' kartu gagal di-render). Cek Console browser untuk detail.'
            });
            return;
        }

        flushZip(true)
            .catch(function (err) {
                if (window.console) { console.error('generateAsync gagal:', err); }
                notifyParent({
                    type: 'id-card-error',
                    message: 'Gagal membuat file ZIP: ' + (err && err.message ? err.message : err)
                });
            });
    }

    function waitForAssets() {
        var fontReady = document.fonts && document.fonts.ready ? document.fonts.ready.catch(function () {}) : Promise.resolve();
        var imageReady = Promise.all(Array.prototype.map.call(document.images || [], function (img) {
            if (img.complete) {
                return Promise.resolve();
            }
            if (img.decode) {
                return img.decode().catch(function () {});
            }
            return new Promise(function (resolve) {
                img.onload = resolve;
                img.onerror = resolve;
            });
        }));

        return Promise.all([fontReady, imageReady]);
    }

    // Mulai proses setelah font, image, dan barcode siap supaya retry lebih sedikit.
    window.addEventListener('load', function () {
        notifyParent({ type: 'id-card-start', total: total });
        if (total === 0) {
            notifyParent({
                type: 'id-card-error',
                message: 'Tidak ada peserta untuk dicetak.'
            });
            return;
        }

        waitForAssets()
            .then(function () {
                renderBarcodes();
                return new Promise(function (resolve) { setTimeout(resolve, 200); });
            })
            .then(function () { processCard(0); });
    });
})();
</script>
<?php endif; ?>
