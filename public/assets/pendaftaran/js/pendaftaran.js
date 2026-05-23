
$(document).ready(function () {
    $theme = $('body').data('bs-theme');
    if ($theme == 'dark') {
        $('#navbarPendaftaran').addClass('navbar-dark bg-dark');
    } else {
        $('#navbarPendaftaran').addClass('navbar-light bg-white');
    }
});