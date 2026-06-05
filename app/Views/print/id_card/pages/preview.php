<?= view('print/id_card/components/card_tanding', [
    'peserta'         => $peserta ?? null,
    'partai'          => $partai ?? [],
    'background_url'  => $background_url ?? '',
]) ?>
