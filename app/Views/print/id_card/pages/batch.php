<div class="kartu-id-batch">
    <?php foreach (($cards ?? []) as $card) : ?>
        <div class="kartu-id-export">
            <?php if ($card['type'] === 'tanding') : ?>
                <?= view('print/id_card/components/card_tanding', [
                    'peserta'         => $card['peserta'],
                    'partai'          => $card['partai'],
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
<script src="<?= online_asset('jquery_js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>
(function() {
    var cards = document.querySelectorAll('.kartu-id-export');
    var zip = new JSZip();
    var total = cards.length;
    var processed = 0;
    var failed = [];
    var barcodeValues = <?= json_encode(array_column($cards, 'barcode_value')) ?>;

    // Generate barcodes first
    <?php foreach ($cards as $i => $card) : ?>
    <?php $id = $card['peserta']->id_peserta_tanding ?? $card['peserta']->id_peserta_seni ?? 0; ?>
    <?php $type = $card['type']; ?>
    try {
        JsBarcode("#bar_<?= $type ?>_<?= $id ?>", "<?= esc($card['barcode_value']) ?>", {
            format: "ean8", width: 1, height: 30, displayValue: false, margin: 0
        });
    } catch(e) {}
    <?php endforeach; ?>

    function processNext(index) {
        if (index >= total) {
            finish();
            return;
        }

        var card = cards[index];
        html2canvas(card, { scale: 2, useCORS: true, allowTaint: true }).then(function(canvas) {
            var dataUrl = canvas.toDataURL('image/png');
            var base64 = dataUrl.replace(/^data:image\/png;base64,/, '');
            zip.file('kartu_' + (index + 1) + '.png', base64, { base64: true });
            processed++;
            parent.postMessage({ type: 'id-card-progress', processed: processed, total: total }, '*');
            processNext(index + 1);
        }).catch(function() {
            failed.push(index + 1);
            processNext(index + 1);
        });
    }

    function finish() {
        zip.generateAsync({ type: 'blob' }).then(function(blob) {
            saveAs(blob, 'id-card-batch.zip');
            parent.postMessage({ type: 'id-card-complete', processed: processed, failed: failed, total: total }, '*');
        });
    }

    processNext(0);
})();
</script>
<?php endif; ?>
