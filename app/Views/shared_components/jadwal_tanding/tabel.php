<?php if ($this->agent->is_mobile()) : ?>
    <table width="100%" class="table table-hover" id="tabel_jadwal_tanding">
        <thead>
            <tr>
                <th><?= lang('arena') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            <?php foreach ($data_jadwal_tanding as $data) : ?>
                <tr>
                    <td>
                        <?= lang('arena') ?> <?= $data->nama_gelanggang ?>
                        <p class="small">
                            <?= lang('keterangan_jadwal') ?>: <br>
                            <?= $data->keterangan_jadwal ?>
                        </p>
                        <p class="small">
                            <?= lang('jumlah_partai') ?>: <?= $data->jumlah_partai ?>
                        </p>
                    </td>
                    <td class="text-end">
                        <?php $this->load->view('shared_components/jadwal_tanding/tombol_tabel', ['jadwal' => $data]) ?>
                    </td>
                </tr>   
            <?php endforeach ?>
        </tbody>
    </table>
<?php else : ?>
    <table width="100%" class="table table-hover" id="tabel_jadwal_tanding">
        <thead>
            <tr>
                <?php if ($this->session->userdata('level') == 'super_admin' || $this->session->userdata('level') == 'sekretariat'): ?>
                    <th class="text-center" style="width: 40px;">
                        <input type="checkbox" id="checkAllJadwalTanding" title="Select all on this page">
                    </th>
                <?php endif; ?>
                <th><?= lang('arena') ?></th>
                <th><?= lang('tanggal') ?></th>
                <th class="text-wrap"><?= lang('jumlah_partai') ?></th>
                <th class="text-wrap"><?= lang('nomor_partai_awal') ?></th>
                <th class="text-wrap"><?= lang('nomor_partai_akhir') ?></th>
                <th class="text-wrap"><?= lang('keterangan_jadwal') ?></th>
                <th><?= lang('aksi') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            <?php foreach ($data_jadwal_tanding as $data) : ?>
                <tr>
                    <?php if ($this->session->userdata('level') == 'super_admin' || $this->session->userdata('level') == 'sekretariat'): ?>
                        <td class="text-center">
                            <input type="checkbox" class="checkbox-jadwal-tanding" 
                                   data-id="<?= $data->id_jadwal_tanding ?>"
                                   data-nama="Arena <?= $data->nama_gelanggang ?> - <?= $data->keterangan_jadwal ?>">
                        </td>
                    <?php endif; ?>
                    <td> <?= lang('arena') ?> <?= $data->nama_gelanggang ?></td>
                    <td><?= $data->tanggal_formatted ?></td>
                    <td><?= $data->jumlah_partai ?></td>
                    <td class="text-end"><?= $data->nomor_partai_awal ?></td>
                    <td class="text-end"><?= $data->nomor_partai_akhir ?></td>
                    <td><?= $data->keterangan_jadwal ?></td>
                    <td class="text-end">
                        <?php $this->load->view('shared_components/jadwal_tanding/tombol_tabel', ['jadwal' => $data]) ?>
                    </td>
                </tr>
                <?php $this->load->view('shared_components/jadwal_tanding/modal_ubah_keterangan', ['jadwal' => $data]) ?>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif; ?>
<script>
    $(document).ready(function() {

        if ($('#tabel_jadwal_tanding').length != 0) {
            var hasCheckbox = $('#tabel_jadwal_tanding thead th:first-child input[type="checkbox"]').length > 0;
            
            $('#tabel_jadwal_tanding').DataTable({
                "language": {
                    "paginate": {
                        "next": " >",
                        "previous": " <"
                    }
                },
                'autoWidth': false,
                "columnDefs": [
                    hasCheckbox ? {
                        orderable: false,
                        searchable: false,
                        width: '40px',
                        targets: 0
                    } : {},
                    {
                        orderable: false,
                        width: '10%',
                        targets: -1
                    }
                ],
                'paging': true,
                'searching': true,
                'ordering': true,
                'info': true,
                'responsive': true
            });

            // Select all checkbox - hanya pada halaman aktif DataTables
            $('#checkAllJadwalTanding').on('change', function() {
                var isChecked = $(this).prop('checked');
                $('#tabel_jadwal_tanding tbody tr:visible .checkbox-jadwal-tanding').prop('checked', isChecked).trigger('change');
            });

            // Update counter saat checkbox berubah
            $(document).on('change', '.checkbox-jadwal-tanding', function() {
                updateSelectedCounterTanding();
            });

            function updateSelectedCounterTanding() {
                var count = $('.checkbox-jadwal-tanding:checked').length;
                $('#btnUpdateSelectedTandingCount').text('(' + count + ')');
                $('#btnUpdateSelectedTandingScoreCount').text('(' + count + ')');
                
                // Enable/disable tombol
                if (count > 0) {
                    $('#btnUpdateSelectedTanding, #btnUpdateSelectedTandingScore').prop('disabled', false);
                } else {
                    $('#btnUpdateSelectedTanding, #btnUpdateSelectedTandingScore').prop('disabled', true);
                }
            }

            // Expose function untuk akses dari all.php
            window.getSelectedJadwalTanding = function() {
                var selected = [];
                $('.checkbox-jadwal-tanding:checked').each(function() {
                    selected.push({
                        id: $(this).data('id'),
                        nama: $(this).data('nama')
                    });
                });
                return selected;
            };

            // Reset selection setelah update
            window.resetSelectionJadwalTanding = function() {
                $('.checkbox-jadwal-tanding').prop('checked', false);
                $('#checkAllJadwalTanding').prop('checked', false);
                updateSelectedCounterTanding();
            };
        }

        // Loading state untuk form ubah keterangan - tutup modal, tampilkan loading overlay
        $('.form-ubah-keterangan-tanding').on('submit', function() {
            var modal = $(this).closest('.modal');
            modal.modal('hide');
            waitingDialog.show('Memperbarui keterangan dan PDF jadwal...');
        });
    });
</script>
