document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('certificate-canvas');
    const propertySidebar = document.getElementById('property-sidebar');
    const zoomInBtn = document.getElementById('zoom-in');
    const zoomOutBtn = document.getElementById('zoom-out');
    const zoomLevelText = document.getElementById('zoom-level');
    
    let currentZoom = 0.6; // Initial zoom to fit screen
    let selectedElement = null;
    const PX_TO_CM = 37.795; // Standard CSS conversion: 1cm = 37.795px (96 DPI)

    // Configuration for elements
    const elements = ['nomor', 'nama', 'kategori', 'kontingen', 'sekolah', 'qrcode'];
    
    function init() {
        updateCanvasScale();
        
        elements.forEach(id => {
            const el = document.getElementById(`editor-${id}`);
            if (el) {
                el.addEventListener('mousedown', startDragging);
                el.addEventListener('click', (e) => selectElement(el, e));
            }
        });

        // Click outside to deselect
        canvas.addEventListener('click', (e) => {
            if (e.target === canvas) {
                deselectAll();
            }
        });

        // Sync initial values from form to canvas
        syncFromFormToCanvas();
    }

    function updateCanvasScale() {
        canvas.style.transform = `scale(${currentZoom})`;
        zoomLevelText.innerText = Math.round(currentZoom * 100) + '%';
    }

    zoomInBtn.addEventListener('click', () => {
        currentZoom = Math.min(1.5, currentZoom + 0.1);
        updateCanvasScale();
    });

    zoomOutBtn.addEventListener('click', () => {
        currentZoom = Math.max(0.2, currentZoom - 0.1);
        updateCanvasScale();
    });

    function selectElement(el, e) {
        e.stopPropagation();
        deselectAll();
        selectedElement = el;
        el.classList.add('active');
        showProperties(el.dataset.id);
    }

    function deselectAll() {
        elements.forEach(id => {
            const el = document.getElementById(`editor-${id}`);
            if (el) el.classList.remove('active');
        });
        selectedElement = null;
        propertySidebar.innerHTML = '<div class="text-center text-muted mt-5">Pilih elemen untuk mengatur properti</div>';
    }

    function startDragging(e) {
        if (!selectedElement) selectElement(this, e);
        
        e.preventDefault();
        const el = this;
        const rect = el.getBoundingClientRect();
        const canvasRect = canvas.getBoundingClientRect();
        const isCentered = el.classList.contains('is-centered');
        const textAlign = el.querySelector('.content').style.textAlign;
        
        // Offset mouse from element corner, account for zoom
        let startX = e.clientX;
        let startY = e.clientY;
        
        // Parse current inset/top/left/right
        const computedStyle = window.getComputedStyle(el);
        let currentTop = parseFloat(computedStyle.top) || 0;
        let currentLeft = parseFloat(computedStyle.left) || 0;
        let currentRight = parseFloat(computedStyle.right) || 0;

        function onMouseMove(moveEvent) {
            const dy = (moveEvent.clientY - startY) / currentZoom;
            const newTop = currentTop + dy;
            el.style.top = newTop + 'px';

            if (textAlign === 'right') {
                const dx = (moveEvent.clientX - startX) / currentZoom;
                const newRight = currentRight - dx;
                el.style.left = 'auto';
                el.style.right = newRight + 'px';
            } else {
                const dx = (moveEvent.clientX - startX) / currentZoom;
                const newLeft = currentLeft + dx;
                el.style.right = 'auto';
                el.style.left = newLeft + 'px';
            }
            
            updateFormFromCanvas(el.dataset.id);
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }

    function showProperties(id) {
        const title = id.charAt(0).toUpperCase() + id.slice(1);
        const fontSizeInput = document.querySelector(`[name="${id}_font_size"]`);
        const textAlignInput = document.querySelector(`[name="${id}_text_align"]`);
        const displayInput = document.querySelector(`[name="${id}_display"]`);
        
        const fontSizeValue = fontSizeInput ? fontSizeInput.value : '20px';
        const textAlignValue = textAlignInput ? textAlignInput.value : 'center';
        const displayValue = displayInput ? displayInput.value : 'block';

        propertySidebar.innerHTML = `
            <div class="sidebar-header">
                <h6 class="mb-0">Edit: ${title}</h6>
            </div>
            <div class="sidebar-content">
                <div class="property-group">
                    <label>Ukuran (px)</label>
                    <input type="range" class="form-range" id="prop-font-size" min="8" max="250" value="${parseInt(fontSizeValue)}">
                    <div class="text-end small text-muted">${fontSizeValue}</div>
                </div>

                <div class="property-group">
                    <label>Perataan Teks</label>
                    <div class="control-row">
                        <button class="btn-alignment ${textAlignValue === 'left' ? 'active' : ''}" data-align="left" onclick="setProperty('${id}', 'text_align', 'left', this)">Left</button>
                        <button class="btn-alignment ${textAlignValue === 'center' ? 'active' : ''}" data-align="center" onclick="setProperty('${id}', 'text_align', 'center', this)">Center</button>
                        <button class="btn-alignment ${textAlignValue === 'right' ? 'active' : ''}" data-align="right" onclick="setProperty('${id}', 'text_align', 'right', this)">Right</button>
                    </div>
                </div>

                <div class="property-group">
                    <label>Tampilkan</label>
                    <select class="form-select form-select-sm" id="prop-display" onchange="setProperty('${id}', 'display', this.value)">
                        <option value="block" ${displayValue === 'block' ? 'selected' : ''}>Tampilkan</option>
                        <option value="none" ${displayValue === 'none' ? 'selected' : ''}>Sembunyikan</option>
                    </select>
                </div>

                <div class="property-group mt-4">
                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle me-1"></i> Geser elemen langsung di canvas untuk memindahkan posisi.
                    </div>
                </div>
            </div>
        `;

        document.getElementById('prop-font-size').addEventListener('input', function() {
            const val = this.value + 'px';
            setProperty(id, 'font_size', val);
            this.nextElementSibling.innerText = val;
        });
    }

    // Global helper for sidebar interactions
    window.setProperty = function(id, key, value, btn = null) {
        const el = document.getElementById(`editor-${id}`);
        const input = document.querySelector(`[name="${id}_${key}"]`);
        
        if (input) input.value = value;
        
        if (key === 'font_size') el.querySelector('.content').style.fontSize = value;
        if (key === 'text_align') {
            el.querySelector('.content').style.textAlign = value;
            if (value === 'center') {
                el.classList.add('is-centered');
                el.style.right = 'auto';
                if (!el.style.left || el.style.left === '0px') el.style.left = '50%';
            } else if (value === 'right') {
                el.classList.remove('is-centered');
                el.style.left = 'auto';
                // If it was centered, move it to right edge as starting point
                if (el.style.right === '0px' || !el.style.right) el.style.right = '20px'; 
            } else {
                el.classList.remove('is-centered');
                el.style.right = 'auto';
                if (el.style.left === '0px' || !el.style.left) el.style.left = '20px';
            }
            updateFormFromCanvas(id); 
        }
        if (key === 'display') {
            if (value === 'none') {
                el.classList.add('is-hidden');
            } else {
                el.classList.remove('is-hidden');
            }
        }

        if (btn) {
            const parent = btn.parentElement;
            parent.querySelectorAll('.btn-alignment').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
    };

    function updateFormFromCanvas(id) {
        const el = document.getElementById(`editor-${id}`);
        const input = document.querySelector(`[name="${id}_position"]`);
        if (!input) return;

        const canvasHeight = canvas.offsetHeight;
        const canvasWidth = canvas.offsetWidth;

        const textAlign = el.querySelector('.content').style.textAlign;
        const topPx = parseFloat(el.style.top) || 0;
        const topPct = ((topPx / canvasHeight) * 100).toFixed(4);

        if (textAlign === 'center') {
            const leftPx = parseFloat(el.style.left) || (canvasWidth / 2);
            const leftPct = ((leftPx / canvasWidth) * 100).toFixed(4);
            input.value = `${topPct}% auto auto ${leftPct}%`;
        } else if (textAlign === 'right') {
            const rightPx = parseFloat(el.style.right) || 0;
            const rightPct = ((rightPx / canvasWidth) * 100).toFixed(4);
            input.value = `${topPct}% ${rightPct}% 0 0`;
        } else {
            const leftPx = parseFloat(el.style.left) || 0;
            const leftPct = ((leftPx / canvasWidth) * 100).toFixed(4);
            input.value = `${topPct}% 0 0 ${leftPct}%`;
        }
    }

    function syncFromFormToCanvas() {
        const canvasHeight = canvas.offsetHeight;
        const canvasWidth = canvas.offsetWidth;

        elements.forEach(id => {
            const input = document.querySelector(`[name="${id}_position"]`);
            const el = document.getElementById(`editor-${id}`);
            if (input && el) {
                const parts = input.value.split(' ');
                if (parts.length >= 4) {
                    const topStr = parts[0] || '0';
                    const rightStr = parts[1] || '0';
                    const leftStr = parts[3] || '0';

                    let topPx = 0, rightPx = 0, leftPx = 0;

                    if (topStr.includes('%')) {
                        topPx = (parseFloat(topStr) / 100) * canvasHeight;
                    } else {
                        topPx = parseFloat(topStr) * PX_TO_CM;
                    }

                    if (rightStr.includes('%')) {
                        rightPx = (parseFloat(rightStr) / 100) * canvasWidth;
                    } else {
                        rightPx = parseFloat(rightStr) * PX_TO_CM;
                    }

                    if (leftStr.includes('%')) {
                        leftPx = (parseFloat(leftStr) / 100) * canvasWidth;
                    } else {
                        leftPx = parseFloat(leftStr) * PX_TO_CM;
                    }
                    
                    el.style.top = topPx + 'px';

                    // Sync fonts and display
                    const fsInput = document.querySelector(`[name="${id}_font_size"]`);
                    const taInput = document.querySelector(`[name="${id}_text_align"]`);
                    const dispInput = document.querySelector(`[name="${id}_display"]`);

                    if(fsInput) el.querySelector('.content').style.fontSize = fsInput.value;
                    if(taInput) {
                        el.querySelector('.content').style.textAlign = taInput.value;
                        if (taInput.value === 'center') {
                            el.classList.add('is-centered');
                            el.style.right = 'auto';
                            if (leftStr === '0' && rightStr === '0') {
                                el.style.left = '50%';
                            } else {
                                el.style.left = leftPx + 'px';
                            }
                        } else if (taInput.value === 'right') {
                            el.classList.remove('is-centered');
                            el.style.left = 'auto';
                            el.style.right = rightPx + 'px';
                        } else {
                            el.classList.remove('is-centered');
                            el.style.right = 'auto';
                            el.style.left = leftPx + 'px';
                        }
                    }
                    if(dispInput) {
                        if (dispInput.value === 'none') {
                            el.classList.add('is-hidden');
                        } else {
                            el.classList.remove('is-hidden');
                        }
                    }
                }
            }
        });
    }

    init();
});
