<div class="dropstart">
    <button type="button" id="dropdown<?= $kontingen->id_kontingen ?>" class="btn btn-default m-0 font-weight-normal shadow-none" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-ellipsis-v"></i>
    </button>
    <ul class="dropdown-menu shadow-lg">
        <li class="dropdown-item">
            <a class="btn btn-default shadow-none m-0 w-100 text-start" href="<?= base_url('kontingen/' . $kontingen->id_kontingen) ?>">Detail</a>
        </li>
        <li class="dropdown-item">
			<a class="btn btn-default shadow-none m-0 w-100 text-start"  href="<?= wa_me(convert_to_indonesian_phone_number($kontingen->nomor_telepon_penanggungjawab))?>" target="_blank">
				Hubungi Via Whatsapp
			</a>
        </li>
        <li class="dropdown-item">
            <form action="<?= base_url('kontingen/delete/' . $kontingen->id_kontingen) ?>" method="post">
                <button type="button" class="btn btn-default shadow-none m-0 w-100 text-start" onclick="confirm_submit('<?= lang('apakah_anda_yakin')?>', this, 'Kontingen <?= $kontingen->nama_kontingen ?>  akan dihapus !')">Hapus</button>
            </form>
        </li>
    </ul>
</div>
