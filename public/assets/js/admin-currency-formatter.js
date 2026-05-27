document.addEventListener('DOMContentLoaded', function () {
    // Fungsi untuk format angka ke Rupiah dengan titik
    function formatRupiah(angka) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            var separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }

    // Terapkan ke semua input dengan class 'currency-input'
    var currencyInputs = document.querySelectorAll('.currency-input');
    currencyInputs.forEach(function(input) {
        // Format saat dimuat jika sudah ada nilai (misal dari old() validation failed)
        if (input.value) {
            input.value = formatRupiah(input.value);
        }

        // Format saat mengetik
        input.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value);
        });
    });

    // Sebelum submit form terdekat dari input, hilangkan semua titik pada currency-input agar backend menerima angka murni
    currencyInputs.forEach(function(input) {
        var form = input.closest('form');
        if (form && !form.dataset.currencyListenerAdded) {
            form.addEventListener('submit', function() {
                var formCurrencyInputs = form.querySelectorAll('.currency-input');
                formCurrencyInputs.forEach(function(ci) {
                    ci.value = ci.value.replace(/\./g, '');
                });
            });
            form.dataset.currencyListenerAdded = 'true';
        }
    });
});
