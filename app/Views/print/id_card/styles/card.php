<style>
    .kartu-peserta {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        width: 94mm;
        height: 129mm;
        position: relative;
        overflow: hidden;
        margin: 0 auto;
    }
    .kartu-peserta .kartu-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
        pointer-events: none;
        image-rendering: -webkit-optimize-contrast;
    }
    .kartu-peserta > *:not(.kartu-bg) {
        position: relative;
        z-index: 1;
    }
    .kartu-peserta .kategori-lomba {
        font-weight: bold;
        text-align: center;
    }
    .tabel-pertandingan {
        border-collapse: collapse;
    }
    .tabel-pertandingan thead tr {
        color: #fff;
        text-align: center;
        background-color: #313131;
        background: linear-gradient(rgb(37, 37, 37) 50%, #313131 50%);
    }
    .tabel-pertandingan thead th {
        padding: 2mm 1mm;
        font-size: 10px;
    }
    .tabel-pertandingan tr {
        text-align: center;
        background-color: #fff;
        background: linear-gradient(#fff 50%, rgb(243, 243, 243) 50%);
    }
    .tabel-pertandingan td {
        padding: 2mm 1mm;
        font-size: 10px;
    }
    .tabel-pertandingan td.merah {
        color: #fff;
        background-color: #ff0000;
        background: linear-gradient(#ff0000 30%, #db0101 30%);
    }
    .tabel-pertandingan td.biru {
        color: #fff;
        background-color: #0000ff;
        background: linear-gradient(#0000ff 30%, #0000db 30%);
    }
</style>
