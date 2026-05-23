
<div class="card">
	<div class="card-header pb-0 p-3">
		<h6 class="mb-0"><?= $kontingen->nama_kontingen ?></h6>
	</div>
	<div class="card-body p-3">
		<ul class="list-group">
			<li class="list-group-item border-0 ps-0 pt-0 text-sm">
				<strong class="text-dark">Email :</strong> &nbsp; <?= $kontingen->email_kontingen ?>
			</li>
			<li class="list-group-item border-0 ps-0 pt-0 text-sm">
				<strong class="text-dark">Asal :</strong> &nbsp; <?= ucwords(str_replace('_', ' ', $kontingen->jenis_kontingen)) ?>
			</li>
			<?php if($kontingen->jenis_kontingen == 'dalam_negeri'):?>
				<li class="list-group-item border-0 ps-0 pt-0 te`xt-sm">
					<strong class="text-dark">Provinsi :</strong> &nbsp; <?= $kontingen->provinsi ?>
				</li>
				<li class="list-group-item border-0 ps-0 pt-0 text-sm">
					<strong class="text-dark">Kabupaten Kota :</strong> &nbsp; <?= $kontingen->kabupaten_kota ?>
				</li>
				<li class="list-group-item border-0 ps-0 pt-0 text-sm">
					<strong class="text-dark">Kecamatan :</strong> &nbsp; <?= $kontingen->kecamatan ?>
				</li>
				<li class="list-group-item border-0 ps-0 pt-0 text-sm">
					<strong class="text-dark">Kelurahan :</strong> &nbsp; <?= $kontingen->kelurahan ?>
				</li>
			<?php else:?>
				<li class="list-group-item border-0 ps-0 pt-0 text-sm">
					<strong class="text-dark">Negara :</strong> &nbsp; <?= $kontingen->negara ?>
				</li>
			<?php endif;?>
			<li class="list-group-item border-0 ps-0 pt-0 text-sm">
				<strong class="text-dark">Alamat Lengkap :</strong> &nbsp; <?= $kontingen->alamat_lengkap ?>
			</li>
			<li class="list-group-item border-0 ps-0 pt-0 text-sm">
				<strong class="text-dark">Penanggungjawab :</strong> &nbsp; <?= $kontingen->nama_penanggungjawab ?>
			</li>
			<li class="list-group-item border-0 ps-0 pt-0 text-sm">
				<strong class="text-dark">Nomor Telepon :</strong> &nbsp; <?= $kontingen->nomor_telepon_penanggungjawab ?>
			</li>
			<li class="list-group-item border-0 ps-0 pt-0 text-sm">
				<strong class="text-dark">Atlet Tanding :</strong> &nbsp; <?= $kontingen->jumlah_peserta_tanding ?> Atlet
			</li>
			<li class="list-group-item border-0 ps-0 pt-0 text-sm">
				<strong class="text-dark">Atlet Tunggal :</strong> &nbsp; <?= $kontingen->jumlah_kelompok_peserta_seni_tunggal ?> Atlet
			</li>
			<li class="list-group-item border-0 ps-0 pt-0 text-sm">
				<strong class="text-dark">Atlet Ganda :</strong> &nbsp; <?= $kontingen->jumlah_kelompok_peserta_seni_ganda ?> Pasang
			</li>
			<li class="list-group-item border-0 ps-0 pt-0 text-sm">
				<strong class="text-dark">Atlet Beregu :</strong> &nbsp; <?= $kontingen->jumlah_kelompok_peserta_seni_beregu ?> Regu
			</li>
			<li class="list-group-item border-0 ps-0 pt-0 text-sm">
				<strong class="text-dark">Catatan Pembayaran :</strong> &nbsp;
				<?= ($kontingen->peserta_tanding_belum_lunas > 0) ? '<span class="badge badge-danger">' . $kontingen->peserta_tanding_belum_lunas . ' atlet tanding belum lunas</span> <br>' :  ''; ?>
				<?= ($kontingen->tunggal_belum_lunas > 0) ? '<span class="badge badge-danger">' . $kontingen->tunggal_belum_lunas . ' atlet tunggal belum lunas</span> <br>' :  ''; ?>
				<?= ($kontingen->ganda_belum_lunas > 0) ? '<span class="badge badge-danger">' . $kontingen->ganda_belum_lunas . ' atlet ganda belum lunas</span> <br>' :  ''; ?>
				<?= ($kontingen->beregu_belum_lunas > 0) ? '<span class="badge badge-danger">' . $kontingen->beregu_belum_lunas . ' atlet regu belum lunas</span> <br>' :  ''; ?>
				<?= ($kontingen->solo_kreatif_belum_lunas > 0) ? '<span class="badge badge-danger">' . $kontingen->solo_kreatif_belum_lunas . ' atlet solo kreatif belum lunas</span> <br>' :  ''; ?>
			</li>
		</ul>
	</div>
</div>
