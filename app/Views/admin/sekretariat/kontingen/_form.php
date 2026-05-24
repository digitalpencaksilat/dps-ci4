<?php $isEdit = ($mode ?? 'create') === 'edit'; ?>
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Nama Kontingen</label>
        <input type="text" name="nama_kontingen" class="form-control rounded-4" value="<?= old('nama_kontingen', $kontingen->nama_kontingen ?? '') ?>" required>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Singkatan</label>
        <input type="text" name="singkatan_nama_kontingen" class="form-control rounded-4" value="<?= old('singkatan_nama_kontingen', $kontingen->singkatan_nama_kontingen ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Jenis Kontingen</label>
        <?php $jenis = old('jenis_kontingen', $kontingen->jenis_kontingen ?? 'dalam_negeri'); ?>
        <select name="jenis_kontingen" class="form-select rounded-4" required>
            <option value="dalam_negeri" <?= $jenis === 'dalam_negeri' ? 'selected' : '' ?>>Dalam Negeri</option>
            <option value="luar_negeri" <?= $jenis === 'luar_negeri' ? 'selected' : '' ?>>Luar Negeri</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Perguruan</label>
        <?php $perguruan = old('perguruan', $kontingen->perguruan ?? 'ipsi'); ?>
        <select name="perguruan" class="form-select rounded-4" required>
            <?php foreach (['ipsi' => 'IPSI', 'ts' => 'Tapak Suci', 'psht' => 'PSHT', 'pamur' => 'Pamur'] as $value => $label) : ?>
                <option value="<?= esc($value) ?>" <?= $perguruan === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="email_kontingen" class="form-control rounded-4" value="<?= old('email_kontingen', $kontingen->email_kontingen ?? '') ?>" required>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Username</label>
        <input type="text" name="username" class="form-control rounded-4" value="<?= old('username', $kontingen->username ?? '') ?>">
    </div>
    <?php if (! $isEdit) : ?>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Password Awal</label>
            <input type="password" name="password" class="form-control rounded-4" minlength="6" required>
        </div>
    <?php endif; ?>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Telepon Kontingen</label>
        <input type="text" name="nomor_telepon_kontingen" class="form-control rounded-4" value="<?= old('nomor_telepon_kontingen', $kontingen->nomor_telepon_kontingen ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Penanggung Jawab</label>
        <input type="text" name="nama_penanggungjawab" class="form-control rounded-4" value="<?= old('nama_penanggungjawab', $kontingen->nama_penanggungjawab ?? '') ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Jabatan</label>
        <input type="text" name="jabatan_penanggungjawab" class="form-control rounded-4" value="<?= old('jabatan_penanggungjawab', $kontingen->jabatan_penanggungjawab ?? '') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Telepon PJ</label>
        <input type="text" name="nomor_telepon_penanggungjawab" class="form-control rounded-4" value="<?= old('nomor_telepon_penanggungjawab', $kontingen->nomor_telepon_penanggungjawab ?? '') ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Negara</label>
        <input type="text" name="negara" class="form-control rounded-4" value="<?= old('negara', $kontingen->negara ?? 'indonesia') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Provinsi</label>
        <input type="text" name="provinsi" class="form-control rounded-4" value="<?= old('provinsi', $kontingen->provinsi ?? '') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Kabupaten/Kota</label>
        <input type="text" name="kabupaten_kota" class="form-control rounded-4" value="<?= old('kabupaten_kota', $kontingen->kabupaten_kota ?? '') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Kecamatan</label>
        <input type="text" name="kecamatan" class="form-control rounded-4" value="<?= old('kecamatan', $kontingen->kecamatan ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Kelurahan</label>
        <input type="text" name="kelurahan" class="form-control rounded-4" value="<?= old('kelurahan', $kontingen->kelurahan ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Alamat Kontingen</label>
        <textarea name="alamat_kontingen" class="form-control rounded-4" rows="2"><?= old('alamat_kontingen', $kontingen->alamat_kontingen ?? '') ?></textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Alamat Lengkap</label>
        <textarea name="alamat_lengkap" class="form-control rounded-4" rows="2"><?= old('alamat_lengkap', $kontingen->alamat_lengkap ?? '') ?></textarea>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Keterangan</label>
        <textarea name="keterangan" class="form-control rounded-4" rows="2"><?= old('keterangan', $kontingen->keterangan ?? '') ?></textarea>
    </div>
</div>
