/**
 * bracketPrintEnhancer.js
 * Tambahan khusus CETAK: label babak di atas tiap kolom bagan + auto-shrink landscape.
 * TIDAK dipakai di halaman interaktif — hanya di-load oleh cetak_tanding.php & cetak_seni_battle.php.
 *
 * Catatan struktur jquery.bracket (hasil inspeksi DOM):
 *   .jQBracket (position:relative) > .bracket (float) > .round (float:left) > .match > .teamContainer
 *   - Jumlah .match di kolom pertama = jumlah pertandingan babak awal (mis. 8 → 1/8 Final).
 *   - .teamContainer diposisikan absolute oleh plugin, jadi JANGAN menyisipkan elemen
 *     di dalam .round (merusak penataan). Label dipasang absolute relatif ke .jQBracket.
 */

(function (window, document) {
    'use strict';

    // Lebar konten acuan kertas A4 landscape (≈277mm usable − padding card) dalam px @96dpi.
    var PRINT_CONTENT_WIDTH = 950;
    // Tinggi area yang direservasi di atas bagan untuk menaruh label babak.
    var LABEL_AREA_HEIGHT = 30;

    /**
     * Nama babak berdasarkan jumlah match di kolom.
     * Konsisten dengan get_babak() di customBracket.js.
     */
    function getBabak(matches) {
        if (matches <= 1) return 'Final';
        if (matches === 2) return 'Semi Final';
        return '1/' + matches + ' Final';
    }

    /**
     * Pasang label babak di atas tiap kolom .round.
     * Label dipasang absolute relatif ke .jQBracket agar tidak mengganggu
     * penataan absolute milik plugin (kolom tetap presisi/sejajar).
     */
    function labelRounds(scopeEl) {
        var bracketRoots = scopeEl.querySelectorAll('.jQBracket');

        [].forEach.call(bracketRoots, function (jqb) {
            if (jqb.getAttribute('data-labelled') === '1') return;

            var rounds = jqb.querySelectorAll('.bracket > .round');
            if (rounds.length === 0) {
                rounds = jqb.querySelectorAll('.round');
            }
            if (rounds.length === 0) return;

            // Jumlah pertandingan babak awal = jumlah .match di kolom pertama.
            var firstMatches = rounds[0].querySelectorAll('.match').length || 1;

            // Reservasi ruang label di atas bagan (geser bagan ke bawah).
            jqb.style.paddingTop = LABEL_AREA_HEIGHT + 'px';
            jqb.setAttribute('data-labelled', '1');

            [].forEach.call(rounds, function (round, idx) {
                // Jumlah match kolom ini dihitung dari babak awal (bukan dihitung ulang
                // dari DOM, karena kolom terakhir bisa memuat match perebutan juara 3).
                var matchesInCol = Math.max(1, Math.round(firstMatches / Math.pow(2, idx)));
                var label = getBabak(matchesInCol);

                var wrapper = document.createElement('div');
                wrapper.className = 'bracket-round-label-wrap';
                wrapper.style.cssText = [
                    'position:absolute',
                    'top:2px',
                    'left:' + round.offsetLeft + 'px',
                    'width:' + round.offsetWidth + 'px',
                    'text-align:center',
                    'pointer-events:none'
                ].join(';');

                var pill = document.createElement('span');
                pill.className = 'bracket-round-label';
                pill.textContent = label;
                pill.style.cssText = [
                    'display:inline-block',
                    'font-family:"Oswald","Poppins",Arial,sans-serif',
                    'font-size:10pt',
                    'font-weight:600',
                    'color:#ffffff',
                    'background-color:#c60000',
                    'border-radius:4px',
                    'padding:2px 12px',
                    'white-space:nowrap',
                    'letter-spacing:0.3px',
                    '-webkit-print-color-adjust:exact',
                    'print-color-adjust:exact'
                ].join(';');

                wrapper.appendChild(pill);
                jqb.appendChild(wrapper);
            });
        });
    }

    /**
     * Skalakan .jQBracket agar muat dalam availableWidthPx (hanya mengecilkan).
     * Collapse box pada container LANGSUNG bracket (bukan .bagan) supaya card
     * pembungkus ikut menyusut rapi dan tidak ada konten yang terpotong.
     */
    function fitToWidth(scopeEl, availableWidthPx) {
        var jqb = scopeEl.querySelector('.jQBracket');
        if (!jqb) return;

        // Reset dulu agar pengukuran natural akurat bila dipanggil ulang.
        jqb.style.transform = '';
        var holder = jqb.parentNode;

        var naturalWidth = jqb.scrollWidth || jqb.offsetWidth;
        var naturalHeight = jqb.scrollHeight || jqb.offsetHeight;
        if (naturalWidth <= 0) return;

        var scale = Math.min(1, availableWidthPx / naturalWidth);
        if (scale >= 1) return; // sudah muat

        jqb.style.transformOrigin = 'top left';
        jqb.style.transform = 'scale(' + scale + ')';

        // Collapse layout box milik bracket agar card membungkus tepat.
        if (holder && holder.style) {
            holder.style.width = Math.ceil(naturalWidth * scale) + 'px';
            holder.style.height = Math.ceil(naturalHeight * scale) + 'px';
            holder.style.overflow = 'hidden';
        }
    }

    /**
     * Titik masuk utama: label semua babak lalu shrink semua bagan agar muat landscape.
     * Dipanggil setelah bracket selesai di-render, sebelum window.print().
     */
    function enhanceForPrint() {
        var bagans = document.querySelectorAll('.bagan');
        [].forEach.call(bagans, function (bagan) {
            labelRounds(bagan);          // label dulu (menambah padding-top → tinggi natural benar)
            fitToWidth(bagan, PRINT_CONTENT_WIDTH);
        });
    }

    window.BracketPrintEnhancer = {
        getBabak: getBabak,
        labelRounds: labelRounds,
        fitToWidth: fitToWidth,
        enhanceForPrint: enhanceForPrint
    };

}(window, document));
