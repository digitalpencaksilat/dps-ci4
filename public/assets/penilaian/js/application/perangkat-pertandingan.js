
function confirm_submit($title = 'Are you sure ?', $element, $message, $confirmButtonText = 'OK', loadingDialog = false, loadingDialogText = null) {
    Swal.fire({
        title: "Are you sure ?",
        text: $message,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: $confirmButtonText,
    }).then((result) => {
        if (result.value) {
            $($element).parents('form').submit();
            if (loadingDialog !== false) {
                if (loadingDialogText !== null) {
                    waitingDialog.show(loadingDialogText);
                } else {
                    waitingDialog.show('loading');
                }
            }
        }
    });
}