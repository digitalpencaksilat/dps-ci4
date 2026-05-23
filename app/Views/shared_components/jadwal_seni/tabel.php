<?php if ($this->agent->is_mobile()) : ?>
    <table width="100%" class="table table-hover" id="tabel_jadwal_seni">
        <thead>
            <tr>
                <th><?= lang('arena') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            <?php foreach ($data_jadwal_seni as $data) : ?>
                <tr>
                    <td>
                        Arena <?= $data->nama_gelanggang ?>
                        <p class="small">
                            Notes<br>
                            <?= $data->keterangan_jadwal ?>
                        </p>
                        <p class="small">
                            Number of Performances : <?= $data->jumlah_penampilan ?>
                        </p>
                    </td>
                    <td class="text-end">
                        <?php $this->load->view('shared_components/jadwal_seni/tombol_tabel', ['jadwal' => $data]) ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php else : ?>

    <table width="100%" class="table table-hover" id="tabel_jadwal_seni">
        <thead>
            <tr>
                <?php if ($this->session->userdata('level') == 'super_admin' || $this->session->userdata('level') == 'sekretariat'): ?>
                    <th class="text-center" style="width: 40px;">
                        <input type="checkbox" id="checkAllJadwalSeni" title="Select all on this page">
                    </th>
                <?php endif; ?>
                <th><?= lang('arena') ?></th>
                <th><?= lang('tanggal') ?></th>
                <th><?= lang('jumlah_penampilan') ?></th>
                <th><?= lang('penampilan_pertama') ?></th>
                <th><?= lang('penampilan_terakhir') ?></th>
                <th><?= lang('keterangan') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            <?php foreach ($data_jadwal_seni as $data) : ?>
                <tr>
                    <?php if ($this->session->userdata('level') == 'super_admin' || $this->session->userdata('level') == 'sekretariat'): ?>
                        <td class="text-center">
                            <input type="checkbox" class="checkbox-jadwal-seni" 
                                   data-id="<?= $data->id_jadwal_seni ?>"
                                   data-nama="Arena <?= $data->nama_gelanggang ?> - <?= $data->keterangan_jadwal ?>">
                        </td>
                    <?php endif; ?>
                    <td>
                        Arena <?= $data->nama_gelanggang ?>
                    </td>
                    <td><?= $data->tanggal_formatted ?></td>
                    <td class="text-end"><?= $data->jumlah_penampilan ?></td>
                    <td class="text-end"><?= $data->nomor_partai_awal ?></td>
                    <td class="text-end"><?= $data->nomor_partai_akhir ?></td>
                    <td><?= $data->keterangan_jadwal ?></td>
                    <td class="text-end">
                        <?php $this->load->view('shared_components/jadwal_seni/tombol_tabel', ['jadwal' => $data]) ?>
                    </td>
                </tr>
                <?php $this->load->view('shared_components/jadwal_seni/modal_ubah_keterangan', ['jadwal' => $data]) ?>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif; ?>
<script>
    $(document).ready(function() {

        if ($('#tabel_jadwal_seni').length != 0) {
            var hasCheckbox = $('#tabel_jadwal_seni thead th:first-child input[type="checkbox"]').length > 0;
            
            $('#tabel_jadwal_seni').DataTable({
                "language": {
                    "paginate": {
                        "next": ">",
                        "previous": "<"
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
            $('#checkAllJadwalSeni').on('change', function() {
                var isChecked = $(this).prop('checked');
                $('#tabel_jadwal_seni tbody tr:visible .checkbox-jadwal-seni').prop('checked', isChecked).trigger('change');
            });

            // Update counter saat checkbox berubah
            $(document).on('change', '.checkbox-jadwal-seni', function() {
                updateSelectedCounterSeni();
            });

            function updateSelectedCounterSeni() {
                var count = $('.checkbox-jadwal-seni:checked').length;
                $('#btnUpdateSelectedSeniCount').text('(' + count + ')');
                $('#btnUpdateSelectedSeniScoreCount').text('(' + count + ')');
                
                // Enable/disable tombol
                if (count > 0) {
                    $('#btnUpdateSelectedSeni, #btnUpdateSelectedSeniScore').prop('disabled', false);
                } else {
                    $('#btnUpdateSelectedSeni, #btnUpdateSelectedSeniScore').prop('disabled', true);
                }
            }

            // Expose function untuk akses dari all.php
            window.getSelectedJadwalSeni = function() {
                var selected = [];
                $('.checkbox-jadwal-seni:checked').each(function() {
                    selected.push({
                        id: $(this).data('id'),
                        nama: $(this).data('nama')
                    });
                });
                return selected;
            };

            // Reset selection setelah update
            window.resetSelectionJadwalSeni = function() {
                $('.checkbox-jadwal-seni').prop('checked', false);
                $('#checkAllJadwalSeni').prop('checked', false);
                updateSelectedCounterSeni();
            };
        }

        // Loading state untuk form ubah keterangan - tutup modal, tampilkan loading overlay
        $('.form-ubah-keterangan-seni').on('submit', function() {
            var modal = $(this).closest('.modal');
            modal.modal('hide');
            waitingDialog.show('Memperbarui keterangan dan PDF jadwal...');
        });
    });
</script>
