<?php $isMobile = service('request')->getUserAgent()->isMobile(); ?>

<?php if ($isMobile): ?>
    <table width="100%" class="table admin-table w-100 text-sm table-striped" id="tabelPertandingan" caption="Match">
        <thead>
            <tr>
                <th></th>
                <th class="min-desktop">Match<br>Number</th>
                <th class="none">No</th>
                <th class="none">Next<br>No</th>
                <th class="text-center">Athletes</th>
                <th class="none">Arena</th>
                <th class="none">Round</th>
                <th class="none">Score</th>
                <th class="none">Blue</th>
                <th class="none">Red</th>
                <th width="15%"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($data_detail_jadwal_seni ?? []) as $detail_jadwal_seni): ?>
                <tr>
                    <td></td>
                    <td class="align-middle text-capitalize fw-bolder text-center"><?= esc((string) ($detail_jadwal_seni->nomor_partai ?? '-')) ?></td>
                    <td class="align-middle"><?= esc((string) ($detail_jadwal_seni->nomor_battle ?? '-')) ?></td>
                    <td class="align-middle"><?= esc((string) ($detail_jadwal_seni->nomor_battle_selanjutnya ?? '-')) ?></td>
                    <td class="py-3">
                        <p class="my-1 text-sm text-center fw-bolder"><?= esc(($detail_jadwal_seni->babak_battle ?? '-') . ' Arena ' . ($detail_jadwal_seni->nama_gelanggang ?? '-') . ' Match ' . ($detail_jadwal_seni->nomor_partai ?? '-')) ?></p>
                        <?php foreach (['merah' => 'red', 'biru' => 'blue'] as $corner => $class): ?>
                            <?php $name = $detail_jadwal_seni->{'anggota_kelompok_peserta_seni_' . $corner} ?? null; ?>
                            <?php if (empty($name)): ?>
                                <span class="mb-0 d-block text-capitalize bg-<?= esc($class) ?> text-white px-2 py-1 text-wrap">
                                    <u class="d-block fw-bold">Winner of Match No. <?= esc((string) ($detail_jadwal_seni->{'calon_anggota_kelompok_peserta_seni_' . $corner} ?? '-')) ?></u>
                                    From Arena <?= esc((string) ($detail_jadwal_seni->{'gelanggang_calon_anggota_kelompok_peserta_seni_' . $corner} ?? '-')) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-wrap mb-0 d-block text-capitalize bg-<?= esc($class) ?> text-white px-2 py-1 text-center">
                                    <?= esc($name) ?> (<?= esc((string) ($detail_jadwal_seni->{'nilai_akhir_' . $corner} ?? 0)) ?>)
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </td>
                    <td class="align-middle text-capitalize"><span class="fw-bolder"><?= esc($detail_jadwal_seni->nama_gelanggang ?? '-') ?></span></td>
                    <td class="align-middle text-capitalize"><span class="fw-bolder"><?= esc($detail_jadwal_seni->babak_battle ?? '-') ?></span></td>
                    <td class="align-middle text-capitalize"><span class="badge bg-blue"><?= esc((string) ($detail_jadwal_seni->nilai_akhir_biru ?? 0)) ?></span> - <span class="badge bg-red"><?= esc((string) ($detail_jadwal_seni->nilai_akhir_merah ?? 0)) ?></span></td>
                    <td class="align-middle text-capitalize"><?= view('shared_components/detail_jadwal_seni/peserta_battle', ['partai_seni' => $detail_jadwal_seni, 'corner' => 'merah']) ?></td>
                    <td class="align-middle text-capitalize"><?= view('shared_components/detail_jadwal_seni/peserta_battle', ['partai_seni' => $detail_jadwal_seni, 'corner' => 'biru']) ?></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <table width="100%" class="table admin-table admin-datatable w-100 table-striped text-sm" id="tabelPertandingan" caption="Pertandingan">
        <thead>
            <tr>
                <th>No</th>
                <th>No Partai</th>
                <th class="min-desktop text-center align-middle">Kategori</th>
                <th class="min-desktop bg-info-gradient text-white text-center align-middle col-3">Biru</th>
                <th class="min-desktop text-center align-middle">Babak</th>
                <th class="min-desktop bg-danger-gradient text-white text-center align-middle col-3">Merah</th>
                <th class="min-desktop text-center align-middle">Pemenang</th>
                <th class="min-desktop"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($data_detail_jadwal_seni ?? []) as $detail_jadwal_seni): ?>
                <tr>
                    <td></td>
                    <td class="text-center align-middle"><?= esc((string) ($detail_jadwal_seni->nomor_partai ?? '-')) ?></td>
                    <td class="align-middle text-capitalize text-center">
                        <strong><?= esc(ucwords(($detail_jadwal_seni->jenis_seni_battle ?? '') . ' - ' . ($detail_jadwal_seni->nama_seni_battle ?? ''))) ?></strong>
                        <i class="d-block"><?= esc(ucwords(($detail_jadwal_seni->nama_kategori_usia_battle ?? '') . ' ' . ($detail_jadwal_seni->jenis_kelamin_battle ?? ''))) ?></i>
                    </td>
                    <td class="align-middle text-capitalize text-center"><?= view('shared_components/detail_jadwal_seni/peserta_battle', ['partai_seni' => $detail_jadwal_seni, 'corner' => 'biru']) ?></td>
                    <td class="align-middle text-capitalize text-center">
                        <span class="fw-bolder d-block text-decoration-underline"><?= esc($detail_jadwal_seni->babak_battle ?? '-') ?></span>
                        <span class="badge bg-blue"><?= esc((string) ($detail_jadwal_seni->nilai_akhir_biru ?? 0)) ?></span> - <span class="badge bg-red"><?= esc((string) ($detail_jadwal_seni->nilai_akhir_merah ?? 0)) ?></span>
                    </td>
                    <td class="align-middle text-capitalize text-center"><?= view('shared_components/detail_jadwal_seni/peserta_battle', ['partai_seni' => $detail_jadwal_seni, 'corner' => 'merah']) ?></td>
                    <td class="align-middle text-center">
                        <?php if (!empty($detail_jadwal_seni->id_penampilan_seni_biru) && !empty($detail_jadwal_seni->id_penampilan_seni_merah) && ($detail_jadwal_seni->id_penampilan_seni_pemenang ?? null) == ($detail_jadwal_seni->id_penampilan_seni_biru ?? null)): ?>
                            <span class="badge bg-blue text-white">Biru</span>
                        <?php elseif (!empty($detail_jadwal_seni->id_penampilan_seni_biru) && !empty($detail_jadwal_seni->id_penampilan_seni_merah) && ($detail_jadwal_seni->id_penampilan_seni_pemenang ?? null) == ($detail_jadwal_seni->id_penampilan_seni_merah ?? null)): ?>
                            <span class="badge bg-red text-white">Merah</span>
                        <?php endif; ?>
                    </td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
