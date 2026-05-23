<?php if ($this->agent->is_mobile()) : ?>
  <table width="100%" class="table table-hover" id="tabel_cek_data">
    <thead>
      <tr>
        <th><?= $this->lang->line('nama_kontingen') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data_kontingen as $data) : ?>
        <tr>
          <td>
            <p class="mb-2"><?= $data->nama_kontingen ?></p>
            <div class="mb-3">
              <a target="_blank" href="<?= base_url('kontingen/ringkasan-data/') . $data->id_kontingen ?>" class="btn btn-primary btn-sm">
                <?= $this->lang->line('cetak_data') ?>
              </a>
              <a target="_blank" href="<?= base_url('kontingen/diagram-jadwal/') . $data->id_kontingen ?>" class="btn btn-info btn-sm">
                <?= $this->lang->line('cetak_diagram_jadwal') ?>
              </a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php else : ?>
  <table width="100%" class="table table-hover" id="tabel_cek_data">
    <thead>
      <tr>
        <th><?= $this->lang->line('no') ?></th>
        <th><?= $this->lang->line('nama_kontingen') ?></th>
        <th><?= $this->lang->line('aksi') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; ?>
      <?php foreach ($data_kontingen as $data) : ?>
        <tr>
          <td class="text-center"><?= $i++ ?></td>
          <td class="text-nowrap"><?= $data->nama_kontingen ?></td>
          <td width="30%" class="text-center">
            <a target="_blank" href="<?= base_url('kontingen/ringkasan-data/') . $data->id_kontingen ?>" class="btn btn-primary d-inline-block me-2">
              <?= $this->lang->line('cetak_data') ?>
            </a>
            <a target="_blank" href="<?= base_url('kontingen/diagram-jadwal/') . $data->id_kontingen ?>" class="btn btn-info d-inline-block">
              <?= $this->lang->line('cetak_diagram_jadwal') ?>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<script>
  $(document).ready(function() {

    if ($('#tabel_cek_data').length != 0) {
      $('#tabel_cek_data').DataTable({
        "language": {
          "paginate": {
            "next": ">",
            "previous": "<"
          }
        },
        'autoWidth': false,
        "columnDefs": [{
          orderable: false,
          width: '10%',
          target: -1
        }],
        'paging': true,
        'searching': true,
        'ordering': true,
        'info': true
      })
    }
  });
</script>