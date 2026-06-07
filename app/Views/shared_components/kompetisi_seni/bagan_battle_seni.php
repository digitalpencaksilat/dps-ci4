<style>
	/* =========================================================
       CUSTOM CSS UNTUK JQUERY BRACKET MODERN (SENI BATTLE)
       ========================================================= */
	.jQBracket {
		font-family: 'Poppins', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
	}

	.jQBracket .team {
		background-color: #ffffff !important;
		border: 1px solid #e2e8f0 !important;
		border-radius: 6px !important;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04) !important;
		transition: all 0.2s ease-in-out;
	}

	.jQBracket .team:hover {
		box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08) !important;
		border-color: #cbd5e1 !important;
		transform: translateY(-1px);
	}

	.jQBracket .team div.label {
		padding: 0 !important;
		height: 100%;
		width: 100%;
		background-color: transparent !important;
	}

	.jQBracket .team div.score {
		background-color: #f8fafc !important;
		color: #475569 !important;
		font-weight: 700 !important;
		border-left: 1px solid #e2e8f0 !important;
		border-radius: 0 6px 6px 0 !important;
		text-align: center !important;
	}

	.jQBracket .team.win {
		border-color: #3b82f6 !important;
	}

	.jQBracket .team.win div.score {
		background-color: #547792 !important;
		color: #ffffff !important;
		border-left: none !important;
	}

	.jQBracket .team.lose {
		opacity: 0.7;
		background-color: #fcfcfc !important;
	}

	.jQBracket .connector {
		border-color: #cbd5e1 !important;
		border-width: 2px !important;
		border-radius: 4px;
	}

	.jQBracket .connector.highlightWinner {
		border-color: #3b82f6 !important;
	}

	.jQBracket .connector.highlightLoser {
		border-color: #94a3b8 !important;
	}

	/* Konten di dalam kotak (render_fn) */
	.seni-card {
		display: flex;
		align-items: center;
		height: 100%;
		padding: 4px 8px;
		box-sizing: border-box;
	}

	.seni-flag {
		width: 24px;
		min-width: 24px;
		margin-right: 10px;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.seni-flag img {
		width: 100%;
		border-radius: 2px;
		box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
	}

	.seni-info {
		display: flex;
		flex-direction: column;
		justify-content: center;
		overflow: hidden;
		width: 100%;
	}

	.nama_atlet_bagan {
		font-size: 13px;
		font-weight: 600;
		color: #1e293b;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		line-height: 1.2;
		margin: 0;
	}

	.kontingen_bagan {
		font-size: 11px;
		font-weight: 500;
		color: #64748b;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		margin: 2px 0 0 0;
	}

	.empty-state-text {
		font-size: 13px;
		font-weight: 600;
		color: #94a3b8;
		display: flex;
		align-items: center;
		height: 100%;
		padding-left: 10px;
		font-style: italic;
	}
</style>

<?php
$idKompetisiSeni = (int) $kompetisi_seni->id_kompetisi_seni;
$peraturanClass  = match ($kompetisi_seni->peraturan_pertandingan ?? '') {
	'Tapak Suci' => 'Tapak_Suci',
	'IPSI 2012'  => 'IPSI_2012',
	'PERSILAT', 'IPSI 2022' => 'PERSILAT',
	default      => 'PERSILAT',
};
$jenisSeniClass  = 'bagan_' . preg_replace('/[^a-zA-Z0-9_]/', '_', (string) ($kompetisi_seni->jenis_seni ?? ''));
?>

<div class="row h-100">
	<?php if (! isset($toggle_early_match) || $toggle_early_match !== false) : ?>
		<div class="d-print-none col-12 text-end mb-3">
			<button class="btn btn-primary btn-sm shadow-sm rounded-pill px-3"
				id="toggleEarlyMatchButtonSeni<?= $idKompetisiSeni ?>">
				<i class="fas fa-eye"></i> Toggle Early Match
			</button>
		</div>
	<?php endif; ?>
	<div class="col-12 overflow-scroll">
		<div id="baganBattleSeni<?= $idKompetisiSeni ?>" class="<?= esc($peraturanClass) ?> <?= esc($jenisSeniClass) ?>"></div>
	</div>
</div>

<script>
	(function() {
		// INISIALISASI BAGAN AGAR DAPAT DITAMPILKAN + BEBERAPA METHOD
		let matchDataSeni<?= $idKompetisiSeni ?> = <?= $kompetisi_seni->bagan_battle_seni ?>;
		let idKompetisiSeni<?= $idKompetisiSeni ?> = <?= $idKompetisiSeni ?>;
		let juaraTigaBersamaSeni<?= $idKompetisiSeni ?> = <?= (int) ($kompetisi_seni->juara_tiga_bersama ?? 1) ?>;

		let baganParametersSeni<?= $idKompetisiSeni ?> = {
			teamWidth: 260,
			scoreWidth: 35,
			matchMargin: 60,
			roundMargin: 60,
			init: matchDataSeni<?= $idKompetisiSeni ?>,
			save: saveFnSeni<?= $idKompetisiSeni ?>,
			disableToolbar: true,
			decorator: {
				edit: editFnSeni<?= $idKompetisiSeni ?>,
				render: renderFnSeni<?= $idKompetisiSeni ?>
			}
		};

		function initBaganBattleSeni<?= $idKompetisiSeni ?>() {
			if (! window.jQuery || ! jQuery.fn.bracket) {
				window.setTimeout(initBaganBattleSeni<?= $idKompetisiSeni ?>, 50);
				return;
			}

			baganParametersSeni<?= $idKompetisiSeni ?>.skipConsolationRound = juaraTigaBersamaSeni<?= $idKompetisiSeni ?> == 1;

			jQuery('#baganBattleSeni<?= $idKompetisiSeni ?>').bracket(baganParametersSeni<?= $idKompetisiSeni ?>);
			toggleEarlyMatchSeni<?= $idKompetisiSeni ?>();

			jQuery('#toggleEarlyMatchButtonSeni<?= $idKompetisiSeni ?>').on('click', function() {
				toggleEarlyMatchSeni<?= $idKompetisiSeni ?>();
			});
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', initBaganBattleSeni<?= $idKompetisiSeni ?>);
		} else {
			initBaganBattleSeni<?= $idKompetisiSeni ?>();
		}

		function toggleEarlyMatchSeni<?= $idKompetisiSeni ?>() {
			jQuery.each(jQuery('#baganBattleSeni<?= $idKompetisiSeni ?>' + ' .bracket'), function(iBracket, bracket) {
				jQuery.each(jQuery(bracket).find('.round').first().find('.teamContainer'), function(i, e) {
					if (jQuery(e).find('.na').length == 1) {
						jQuery(e).toggle();
					}
				});
			});
		}

		function saveFnSeni<?= $idKompetisiSeni ?>(data, userData) {
			jQuery.ajax({
				url: '<?= base_url('admin/sekretariat/pool-seni/' . $idKompetisiSeni . '/update-bagan-battle') ?>',
				type: 'POST',
				dataType: 'json',
				data: {
					bagan_battle_seni: JSON.stringify(data)
				},
				success: function(response, textStatus, jqXHR) {
					const newToken = jqXHR.getResponseHeader('X-CSRF-TOKEN');
					if (newToken) {
						window.csrfHash = newToken;
					}
					if (! response || response.status !== true) {
						if (window.Swal) {
							Swal.fire('Error', 'Gagal edit bagan, server tidak merespon !', 'error');
						}
					}
				},
				error: function() {
					if (window.Swal) {
						Swal.fire('Error', 'Gagal edit bagan, server tidak merespon !', 'error');
					}
				}
			});
		}

		/* Edit function dipanggil saat label tim diklik */
		function editFnSeni<?= $idKompetisiSeni ?>(container, data, doneCb) {
			const $modal = jQuery('#modalEditBaganSeni');
			if ($modal.length === 0) {
				if (window.Swal) {
					Swal.fire('Error', 'Tidak dapat edit bagan !', 'error');
				}
				return;
			}

			$modal.find('[name="id_kelompok_peserta_seni"]').val(data.id_kelompok_peserta_seni || '');
			$modal.find('[name="id_kontingen"]').val(data.id_kontingen || '');
			$modal.find('[name="anggota_kelompok_peserta_seni"]').val(data.anggota_kelompok_peserta_seni || '');
			$modal.find('[name="nama_kontingen"]').val(data.nama_kontingen || '');

			const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
			modalInstance.show();

			jQuery('#buttonSaveEditBaganSeni').off('click').on('click', function() {
				doneCb({
					id_kelompok_peserta_seni: $modal.find('[name="id_kelompok_peserta_seni"]').val(),
					id_kontingen: $modal.find('[name="id_kontingen"]').val(),
					anggota_kelompok_peserta_seni: $modal.find('[name="anggota_kelompok_peserta_seni"]').val(),
					nama_kontingen: $modal.find('[name="nama_kontingen"]').val(),
				});
				modalInstance.hide();
			});
		}

		function renderFnSeni<?= $idKompetisiSeni ?>(container, data, score, state) {
			switch (state) {
				case 'empty-bye':
					container.append('<div class="empty-state-text">BYE</div>');
					return;
				case 'empty-tbd':
					container.append('<div class="empty-state-text">TBD</div>');
					return;

				case 'entry-no-score':
				case 'entry-default-win':
				case 'entry-complete':
					let flagUrl = data.url_bendera || '<?= base_url('assets/images/bendera/id.png') ?>';
					let namaAtlet = data.anggota_kelompok_peserta_seni || '-';
					let namaKontingen = (data.nama_kontingen || '-').toUpperCase();
					let stringHtml = `
						<div class="seni-card">
							<div class="seni-flag">
								<img src="${flagUrl}" alt="Bendera" onerror="this.src='<?= base_url('assets/images/bendera/id.png') ?>'" />
							</div>
							<div class="seni-info">
								<p class="nama_atlet_bagan">${namaAtlet}</p>
								<p class="kontingen_bagan">${namaKontingen}</p>
							</div>
						</div>
					`;
					container.append(stringHtml);
					return;
			}
		}
	})();
</script>
