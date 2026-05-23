<button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalINsertKontingen">
  Tambah Kontingen
</button>

<div class="modal fade" id="modalINsertKontingen" tabindex="-1" data-bs-backdrop="static" role="dialog" aria-labelledby="modalTitleInsertKontingen" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-fullscreen" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalTitleInsertKontingen">Tambah Kontingen</h5>
					<button type="button" class="btn btn-default h4 shadow-none m-0 py-1" data-bs-dismiss="modal" aria-label="Close">&times;</button>
			</div>
			<div class="modal-body">
				<?php $this->load->view('shared_components/kontingen/form_insert');?>
			</div>
		</div>
	</div>
</div>
