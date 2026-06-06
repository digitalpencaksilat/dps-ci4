<?php
/**
 * @var object|null $peserta
 * @var array       $partai
 * @var array       $data_penampilan
 * @var array       $data_battle
 * @var string      $background_url
 * @var string      $barcode_value
 * @var string|null $card_filename
 * @var string|null $card_type
 * @var bool|null   $is_preview
 */
?>
<div class="kartu-id-export"
     data-filename="<?= esc($card_filename ?? ('Kartu_Seni_' . (int) ($peserta->id_peserta_seni ?? 0))) ?>">
    <?= view('print/id_card/components/card_seni', [
        'peserta'          => $peserta ?? null,
        'partai'           => $partai ?? [],
        'data_penampilan'  => $data_penampilan ?? [],
        'data_battle'      => $data_battle ?? [],
        'background_url'   => $background_url ?? '',
    ]) ?>
</div>

<?php if (! ($is_preview ?? false)) : ?>
<style>
    body { background: #f1f3f5; padding: 16px; }
    .kartu-id-toolbar {
        max-width: 110mm;
        margin: 0 auto 16px auto;
        display: flex;
        gap: 8px;
        justify-content: center;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    }
    .kartu-id-toolbar button {
        background: #b71c1c;
        color: #fff;
        border: none;
        padding: 10px 18px;
        border-radius: 999px;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(183,28,28,0.2);
    }
    .kartu-id-toolbar button:disabled { background: #9e9e9e; cursor: wait; }
    .kartu-id-export { margin: 0 auto; max-width: 110mm; }
    @media print { .kartu-id-toolbar { display: none; } }
</style>

<div class="kartu-id-toolbar">
    <button type="button" id="btnDownloadPng">Download PNG</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script>
(function () {
    'use strict';

    var FILENAME = <?= json_encode($card_filename ?? ('Kartu_Seni_' . (int) ($peserta->id_peserta_seni ?? 0)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var BARCODE_VALUE = <?= json_encode($barcode_value ?? '', JSON_UNESCAPED_SLASHES) ?>;
    var BARCODE_SELECTOR = '#bar_seni_<?= (int) ($peserta->id_peserta_seni ?? 0) ?>';

    function renderBarcode() {
        if (typeof JsBarcode === 'undefined' || !BARCODE_VALUE) return;
        var el = document.querySelector(BARCODE_SELECTOR);
        if (!el) return;
        try {
            JsBarcode(el, BARCODE_VALUE, {
                format: 'ean8', width: 1, height: 30, displayValue: false, margin: 0
            });
        } catch (e) { /* skip */ }
    }

    function downloadPng() {
        var btn = document.getElementById('btnDownloadPng');
        var card = document.querySelector('.kartu-id-export');
        if (!card) return;
        btn.disabled = true;
        btn.textContent = 'Memproses…';

        html2canvas(card, {
            scale: 3,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false
        }).then(function (canvas) {
            canvas.toBlob(function (blob) {
                if (blob) {
                    saveAs(blob, FILENAME + '.png');
                }
                btn.disabled = false;
                btn.textContent = 'Download PNG';
            }, 'image/png');
        }).catch(function () {
            btn.disabled = false;
            btn.textContent = 'Coba Lagi';
        });
    }

    window.addEventListener('load', function () {
        renderBarcode();
        document.getElementById('btnDownloadPng').addEventListener('click', downloadPng);
        setTimeout(downloadPng, 500);
    });
})();
</script>
<?php endif; ?>
