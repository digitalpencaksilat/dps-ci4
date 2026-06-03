<!-- Modal Add Arena -->
<div class="modal fade" id="modalInsertGelanggang" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('admin/gelanggang/create') ?>" method="post" novalidate="novalidate" id="formInsertGelanggang" class="needs-validation">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Gelanggang Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="gelanggang_nomor_gelanggang" class="form-label">Nomor Gelanggang</label>
                        <input type="number" name="nomor_gelanggang" id="gelanggang_nomor_gelanggang" 
                               class="form-control <?= session('errors.nomor_gelanggang') ? 'is-invalid' : '' ?>" 
                               value="<?= old('nomor_gelanggang') ?>" required min="0" />
                        <?php if (session('errors.nomor_gelanggang')): ?>
                            <div class="invalid-feedback d-block">
                                <?= session('errors.nomor_gelanggang') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="gelanggang_nama_gelanggang" class="form-label">Nama Gelanggang</label>
                        <input type="text" name="nama_gelanggang" id="gelanggang_nama_gelanggang" 
                               class="form-control <?= session('errors.nama_gelanggang') ? 'is-invalid' : '' ?>" 
                               value="<?= old('nama_gelanggang') ?>" required />
                        <?php if (session('errors.nama_gelanggang')): ?>
                            <div class="invalid-feedback d-block">
                                <?= session('errors.nama_gelanggang') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="gelanggang_keterangan" class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" id="gelanggang_keterangan" 
                               class="form-control <?= session('errors.keterangan') ? 'is-invalid' : '' ?>" 
                               value="<?= old('keterangan') ?>" />
                        <?php if (session('errors.keterangan')): ?>
                            <div class="invalid-feedback d-block">
                                <?= session('errors.keterangan') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>
