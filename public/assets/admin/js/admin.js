$(document).ready(function () {
	/**
	 * GLOBAL SCRIPT
	 */
	(function () {
		'use strict'
		var forms = document.querySelectorAll('.needs-validation')

		Array.prototype.slice.call(forms)
			.forEach(function (form) {
				form.addEventListener('submit', function (event) {
					if (!form.checkValidity()) {
						event.preventDefault()
						event.stopPropagation()
					}else{
						if(form.dataset.loadingDialog == 'true') {
							if(form.dataset.loadingDialogTitle == undefined) {
								waitingDialog.show();
							}else{
								waitingDialog.show(form.dataset.loadingDialogTitle);
							}
						}
					}
					form.classList.add('was-validated')
				}, false)
			})
	})()
});

function preview_image(input) {
	if (input.files && input.files[0]) {
		var reader = new FileReader();
		reader.onload = function (e) {
			$('.image-preview').attr('src', e.target.result);
		};
		reader.readAsDataURL(input.files[0]);
	}
}

function confirm_submit($title = 'Are you sure ?', $element, $message, $confirmButtonText = 'OK', loadingDialog = false, loadingDialogText = null) {
	Swal.fire({
		title: "Are you Sure ?",
		text: $message,
		icon: "question",
		showCancelButton: true,
		confirmButtonText: $confirmButtonText,
	}).then((result) => {
		if (result.value) {
			$($element).parents('form').submit();
			if(loadingDialog !== false){
				if(loadingDialogText !== null)
				{
					waitingDialog.show(loadingDialogText);
				}else
				{
					waitingDialog.show('loading');
				}
			}
		}
	});
}


	// var tourObject = {
    //     steps: [
    //         {
    //             element: "#btn_atlet",
    //             title: "Form  Atlet",
    //             content: "Silahkan klik tombol dibawah ini untuk mengisi biodata atlet, anda cukup menginputkan data atlet <b>Sekali</b> saja. Setelah itu anda dapat mendaftarkannya di form tanding atau form seni",
    //             placement: "top"
    //         },
    //         {
    //             element: "#btn_tanding",
    //             title: "Form Tanding",
    //             content: "Silahkan mendaftarkan atlet yang telah diinputkan menggunakan Form tanding ",
    //             placement: "top"
    //         },
    //         {
    //             element: "#btn_seni",
    //             title: "Form Seni",
    //             content: "Silahkan mendaftarkan atlet yang telah diinputkan menggunakan Form seni ",
    //             placement: "top"
    //         },
    //         {
    //             element: "#btn_pembayaran",
    //             title: "Pembayaran",
    //             content: "Sebelum melakukan pembayaran <b>pastikan semua atlet anda telah terdaftar</b>",
    //             placement: "right",
    //             onNext : function (tour) {
    //                 window.location.href = base_path+"/kontingen/pembayaran";
    //             }
    //         },
    //         {
    //             element: "#box_pembayaran",
    //             title: "Pembayaran",
    //             content: "Silahkan periksa kembali data - data atet anda berikut ini",
    //             placement: "left"
    //         },
    //         {
    //             element: "#info_pembayaran",
    //             title: "Pembayaran",
    //             content: "Setelah memeriksa data - data atlet, silahkan melakukan pembayaran sesuai informasi pada box ini",
    //             placement: "bottom"
    //         },
    //         {
    //             element: "#btn_upload_bukti",
    //             title: "Pembayaran",
    //             content: "Setelah melakukan pembayaran, silahkan upload bukti pembayaran anda dan silahkan menunggu konfirmasi dari admin",
    //             placement: "top"
    //         }
    //     ],
    //     storage: false
    // }
    // var helpTour = new Tour(tourObject);

    // // Initialize the tour
    // helpTour.init();+
