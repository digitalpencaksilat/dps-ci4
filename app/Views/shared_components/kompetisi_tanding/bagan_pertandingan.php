<style>
	/* =========================================================
       CUSTOM CSS UNTUK JQUERY BRACKET MODERN
       ========================================================= */

	/* Font dan Warna Dasar */
	.jQBracket {
		font-family: 'Poppins', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
	}

	/* Modifikasi Kotak Tim (Team Card) */
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

	/* Container label agar bisa di-custom penuh oleh render_fn */
	.jQBracket .team div.label {
		padding: 0 !important;
		height: 100%;
		width: 100%;
		background-color: transparent !important;
	}

	/* Modifikasi Kotak Skor */
	.jQBracket .team div.score {
		background-color: #f8fafc !important;
		color: #475569 !important;
		font-weight: 700 !important;
		border-left: 1px solid #e2e8f0 !important;
		border-radius: 0 6px 6px 0 !important;
		text-align: center !important;
	}

	/* Tim yang Menang (Win) */
	.jQBracket .team.win {
		border-color: #3b82f6 !important;
	}

	.jQBracket .team.win div.score {
		background-color: #547792 !important;
		color: #ffffff !important;
		border-left: none !important;
	}

	/* Tim yang Kalah (Lose) */
	.jQBracket .team.lose {
		opacity: 0.7;
		background-color: #fcfcfc !important;
	}

	/* Garis Penghubung (Connectors) */
	.jQBracket .connector {
		border-color: #cbd5e1 !important;
		border-width: 2px !important;
		border-radius: 4px;
		/* Sedikit melengkung di sudut garis */
	}

	.jQBracket .connector.highlightWinner {
		border-color: #3b82f6 !important;
	}

	.jQBracket .connector.highlightLoser {
		border-color: #94a3b8 !important;
	}

	/* =========================================================
       CSS UNTUK KONTEN DI DALAM KOTAK (RENDER_FN)
       ========================================================= */
	.atlet-card {
		display: flex;
		align-items: center;
		height: 100%;
		padding: 4px 8px;
		box-sizing: border-box;
	}

	.atlet-flag {
		width: 24px;
		min-width: 24px;
		margin-right: 10px;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.atlet-flag img {
		width: 100%;
		border-radius: 2px;
		box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
	}

	.atlet-info {
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

	/* Styling teks default BYE / TBD */
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

<div class="row h-100">
	<?php if (!isset($toggle_early_match) || isset($toggle_early_match) && $toggle_early_match !== FALSE): ?>
		<div class="d-print-none col-12 text-end mb-3">
			<!-- Tombol juga diperbarui agar lebih elegan -->
			<button class="btn btn-primary btn-sm shadow-sm rounded-pill px-3"
				id="toggleEarlyMatchButton<?= $kompetisi_tanding->id_kompetisi_tanding; ?>">
				<i class="fas fa-eye"></i> Toggle Early Match
			</button>
		</div>
	<?php endif; ?>
	<div class="col-12 overflow-scroll">
		<?php
		$peraturanClass = match ($kompetisi_tanding->peraturan_pertandingan ?? '') {
			'Tapak Suci' => 'Tapak_Suci',
			'IPSI 2012' => 'IPSI_2012',
			'PERSILAT', 'IPSI 2022' => 'PERSILAT',
			default => 'PERSILAT',
		};
		?>
		<div id="baganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding ?>" class="<?= esc($peraturanClass) ?>"></div>
	</div>
</div>

<script>
	(function() {
	// INISIALISASI BAGAN AGAR DAPAT DITAMPILKAN + BEBERAPA METHOD
	let matchData<?= $kompetisi_tanding->id_kompetisi_tanding; ?> = <?php echo $kompetisi_tanding->bagan_pertandingan; ?>;
	let idKompetisiTanding<?= $kompetisi_tanding->id_kompetisi_tanding; ?> = <?php echo $kompetisi_tanding->id_kompetisi_tanding ?>;
	let juaraTigaBersama<?= $kompetisi_tanding->id_kompetisi_tanding; ?> = <?php echo (int) ($kompetisi_tanding->juara_tiga_bersama ?? 0) ?>;

	let baganParameters<?= $kompetisi_tanding->id_kompetisi_tanding; ?> = {
		teamWidth: 260,
		/* Sedikit dilebarkan untuk memberi ruang UI modern */
		scoreWidth: 35,
		matchMargin: 60,
		/* Margin diperbesar agar bayangan (shadow) tidak terpotong */
		roundMargin: 60,
		init: matchData<?= $kompetisi_tanding->id_kompetisi_tanding; ?>,
		save: saveFn,
		disableToolbar: true,
		decorator: {
			edit: edit_fn,
			render: render_fn
		}
	};

	function initBaganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding; ?>() {
		if (!window.jQuery || !jQuery.fn.bracket) {
			window.setTimeout(initBaganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding; ?>, 50);
			return;
		}

		if (juaraTigaBersama<?= $kompetisi_tanding->id_kompetisi_tanding; ?> == 1) {
			baganParameters<?= $kompetisi_tanding->id_kompetisi_tanding; ?>.skipConsolationRound = true;
		} else {
			baganParameters<?= $kompetisi_tanding->id_kompetisi_tanding; ?>.skipConsolationRound = false;
		}

		jQuery('#baganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding ?>').bracket(baganParameters<?= $kompetisi_tanding->id_kompetisi_tanding; ?>);
		toggle_early_match<?= $kompetisi_tanding->id_kompetisi_tanding; ?>();

		jQuery('#toggleEarlyMatchButton<?= $kompetisi_tanding->id_kompetisi_tanding; ?>').on('click', function() {
			toggle_early_match<?= $kompetisi_tanding->id_kompetisi_tanding; ?>();
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initBaganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding; ?>);
	} else {
		initBaganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding; ?>();
	}

	function toggle_early_match<?= $kompetisi_tanding->id_kompetisi_tanding; ?>() {
		jQuery.each(jQuery('#baganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding ?>' + ' .bracket'), function(i_bracket, bracket) {
			jQuery.each(jQuery(bracket).find('.round').first().find('.teamContainer'), function(i, e) {
				if (jQuery(e).find('.na').length == 1) {
					jQuery(e).toggle();
				}
			});
		});
	}

	function onhover(data, hover) {
		jQuery('#matchCallback').text(data)
	}

	function saveFn(data, userData) {
		jQuery.post('<?= base_url('kompetisi-tanding/update-bagan-pertandingan/' . $kompetisi_tanding->id_kompetisi_tanding) ?>', {
				bagan_pertandingan: JSON.stringify(data)
			},
			function(data, textStatus, jqXHR) {
				if (data.status !== true) {
					Swal.fire('Error', 'Gagal edit bagan, server tidak merespon !', 'error');
				}
			},
			"json"
		);
	}

	function edit_fn(container, data, doneCb) {
		// Biarkan kosong / uncomment logika lama Anda jika diperlukan
	}

	/* FUNGSI RENDER DIPERBARUI DENGAN MODERN FLEXBOX HTML */
	function render_fn(container, data, score, state) {
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
				let stringHtml = `
                    <div class="atlet-card">
                        <div class="atlet-flag">
                            <!-- Sesuaikan path URL bendera Anda -->
                            <img src="<?= base_url('assets/images/bendera/id.png') ?>" alt="Bendera" />
                        </div>
                        <div class="atlet-info">
                            <p class="nama_atlet_bagan">${data.nama_pendaftar}</p>
                            <p class="kontingen_bagan">${data.nama_kontingen.toUpperCase()}</p>
                        </div>
                    </div>
                `;
				container.append(stringHtml);
				return;
		}
	}
	})();
</script>
