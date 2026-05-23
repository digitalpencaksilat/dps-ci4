<button type="button" class="btn btn-outline-primary m-0 w-100" data-bs-toggle="modal"
	data-bs-target="#modalGantiPassword">
	Ganti password
</button>
<div class="modal fade" id="modalGantiPassword" tabindex="-1" role="dialog" aria-labelledby="modalGantiPassword"
	aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Ganti Password</h5>
				<button type="button" class="btn btn-link m-0" data-bs-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<?php $this->load->view('shared_components/kontingen/form_ganti_password');?>
			</div>
		</div>
	</div>
</div>