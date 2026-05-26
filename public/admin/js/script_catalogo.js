// ==========================================
// VARIABLES GLOBALES
// ==========================================
let uploadedFiles = []; 
let existingImages = [];

// ==========================================
// FUNCIONES DE INTERFAZ Y MODAL
// ==========================================
function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    if (isDark) {
        html.classList.remove('dark');
        document.getElementById('iconSun').style.display = 'none';
        document.getElementById('iconMoon').style.display = 'block';
    } else {
        html.classList.add('dark');
        document.getElementById('iconSun').style.display = 'block';
        document.getElementById('iconMoon').style.display = 'none';
    }
}

function switchLangTab(lang) {
    document.querySelectorAll('.lang-tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.lang-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + lang).classList.add('active');
    document.getElementById('tab-' + lang).classList.add('active');
}

function openModal() {
    document.getElementById('piezaForm').reset();
    document.getElementById('fId').value = '';
    document.getElementById('modalTitleText').innerHTML = 'Registrar nueva pieza';
    
    // Reiniciamos imágenes
    uploadedFiles = [];
    existingImages = [];
    renderThumbnails();
    updateImageCounter();
    updatePreview();
    
    document.getElementById('mainModal').classList.add('open');
}

function closeModal() {
    document.getElementById('mainModal').classList.remove('open');
}

// ==========================================
// FUNCIONES DE BÚSQUEDA Y FILTRO
// ==========================================
function renderTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const marcaSelect = document.getElementById('marcaFilter');
    const marcaText = marcaSelect.options[marcaSelect.selectedIndex].text.toLowerCase();
    
    const rows = document.querySelectorAll('.table-wrap tbody tr');

    rows.forEach(row => {
        const sku = row.querySelector('.sku-cell').innerText.toLowerCase();
        const nombre = row.querySelector('td:nth-child(2) div').innerText.toLowerCase();
        const marcaCell = row.querySelector('.badge-brand').innerText.toLowerCase();

        const matchesSearch = sku.includes(search) || nombre.includes(search);
        const matchesMarca = (marcaText === 'todas las marcas') || (marcaCell === marcaText);

        if (matchesSearch && matchesMarca) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('marcaFilter').selectedIndex = 0;
    renderTable();
}

// ==========================================
// CRUD: EDITAR Y ELIMINAR
// ==========================================
function editPieza(data) {
    openModal();
    document.getElementById('modalTitleText').innerHTML = 'Editar pieza: ' + data.sku;
    document.getElementById('fId').value = data.id;
    document.getElementById('fSku').value = data.sku;
    document.getElementById('fMarca').value = data.marca_id;
    document.getElementById('fPrecio').value = data.precio_unitario;
    document.getElementById('fStock').value = data.stock;
    document.getElementById('fNombre_es').value = data.nombre;
    document.getElementById('fDesc_es').value = data.descripcion_tecnica;
    document.getElementById('fNombre_en').value = data.nombre_en;
    document.getElementById('fDesc_en').value = data.desc_en;
    
    // Cargar imágenes existentes (si las trae el PHP)
    existingImages = data.imagenes ? data.imagenes : [];
    uploadedFiles = [];
    
    updateImageCounter();
    renderThumbnails();
    checkFormValidity();
    updatePreview();
}

function deletePieza(id) {
    Swal.fire({
        title: '¿Eliminar pieza?',
        text: "Esta acción no se puede deshacer y quedará registrada en el historial.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#4a6080',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            fetch('Catalogo.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                Swal.fire('Eliminado', 'La pieza ha sido borrada.', 'success')
                .then(() => location.reload());
            });
        }
    });
}

// ==========================================
// VISTA PREVIA Y VALIDACIÓN
// ==========================================
function updatePreview() {
    const nombre = document.getElementById('fNombre_es').value || 'Nombre de la pieza';
    const sku = document.getElementById('fSku').value || '—';
    const stock = document.getElementById('fStock').value || '0';
    const precio = document.getElementById('fPrecio').value || '0.00';

    const prevName = document.getElementById('prevName');
    prevName.innerText = nombre;
    if(nombre === 'Nombre de la pieza') prevName.classList.add('empty');
    else prevName.classList.remove('empty');

    document.getElementById('prevSku').innerHTML = `
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        <span style="color:var(--text-muted)">SKU: ${sku}</span>
    `;

    document.getElementById('prevStock').innerHTML = `
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
        <span style="color:var(--text-muted)">Stock: ${stock} · $${precio}</span>
    `;
    
    checkFormValidity();
}

function checkFormValidity() {
    const form = document.getElementById('piezaForm');
    const btn = document.getElementById('saveBtn');
    const total = getTotalImages();
    
    const isValid = form.checkValidity() && total >= 3 && total <= 5;
    if (isValid) {
        btn.style.opacity = '1'; btn.style.cursor = 'pointer'; btn.disabled = false;
    } else {
        btn.style.opacity = '0.5'; btn.style.cursor = 'not-allowed'; btn.disabled = true;
    }
}

// ==========================================
// GESTIÓN DE IMÁGENES
// ==========================================
function getTotalImages() {
    return existingImages.length + uploadedFiles.length;
}

function handleDragOver(e) { e.preventDefault(); document.getElementById('dropZone').classList.add('drag-over'); }
function handleDragLeave(e) { e.preventDefault(); document.getElementById('dropZone').classList.remove('drag-over'); }
function handleDrop(e) { e.preventDefault(); document.getElementById('dropZone').classList.remove('drag-over'); handleFiles(e.dataTransfer.files); }

function handleFiles(files) {
    const newFiles = Array.from(files);
    if (getTotalImages() + newFiles.length > 5) {
        Swal.fire('Atención', 'Solo puedes tener un máximo de 5 imágenes.', 'warning');
        return;
    }
    uploadedFiles = [...uploadedFiles, ...newFiles];
    updateImageCounter();
    renderThumbnails();
    checkFormValidity();
}

function updateImageCounter() {
    const total = getTotalImages();
    const counter = document.getElementById('imgCounter');
    counter.innerText = `${total} / 5`;
    if (total >= 3 && total <= 5) counter.className = 'img-counter ok';
    else if (total > 0) counter.className = 'img-counter warn';
    else counter.className = 'img-counter empty';
}

function renderThumbnails() {
    const strip = document.getElementById('thumbsStrip');
    const addBtn = document.getElementById('thumbAddBtn');
    strip.innerHTML = '';
    
    let isFirst = true;

    existingImages.forEach((img, index) => {
        const thumbItem = document.createElement('div');
        thumbItem.className = 'thumb-item';
        if (isFirst) { thumbItem.classList.add('selected'); isFirst = false; }

        const imgEl = document.createElement('img');
        imgEl.src = img.ruta_imagen;

        const removeBtn = document.createElement('button');
        removeBtn.className = 'thumb-remove'; removeBtn.innerHTML = '×'; removeBtn.type = 'button';
        removeBtn.onclick = (e) => { e.stopPropagation(); removeExistingImage(index); };

        thumbItem.appendChild(imgEl); thumbItem.appendChild(removeBtn); strip.appendChild(thumbItem);
    });

    uploadedFiles.forEach((file, index) => {
        const thumbItem = document.createElement('div');
        thumbItem.className = 'thumb-item';
        if (isFirst) { thumbItem.classList.add('selected'); isFirst = false; }

        const imgEl = document.createElement('img');
        const reader = new FileReader();
        reader.onload = (e) => { imgEl.src = e.target.result; };
        reader.readAsDataURL(file);

        const removeBtn = document.createElement('button');
        removeBtn.className = 'thumb-remove'; removeBtn.innerHTML = '×'; removeBtn.type = 'button';
        removeBtn.onclick = (e) => { e.stopPropagation(); removeUploadedImage(index); };

        thumbItem.appendChild(imgEl); thumbItem.appendChild(removeBtn); strip.appendChild(thumbItem);
    });

    if (getTotalImages() < 5) strip.appendChild(addBtn);
    updateMainPreviewImage();
}

function removeExistingImage(index) {
    existingImages.splice(index, 1); 
    updateImageCounter(); renderThumbnails(); checkFormValidity();
}

function removeUploadedImage(index) {
    uploadedFiles.splice(index, 1);
    updateImageCounter(); renderThumbnails(); checkFormValidity();
}

function updateMainPreviewImage() {
    const prevThumb = document.getElementById('prevThumb');
    if (existingImages.length > 0) {
        prevThumb.innerHTML = `<img src="${existingImages[0].ruta_imagen}" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">`;
    } else if (uploadedFiles.length > 0) {
        const reader = new FileReader();
        reader.onload = (e) => { prevThumb.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">`; };
        reader.readAsDataURL(uploadedFiles[0]);
    } else {
        prevThumb.innerHTML = `<div class="no-img"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".4"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>`;
    }
}

// ==========================================
// ENVÍO DE FORMULARIO (GUARDAR)
// ==========================================
function guardarPieza(e) {
    e.preventDefault();

    if (getTotalImages() < 3) {
        Swal.fire('Imágenes incompletas', 'Debes subir al menos 3 imágenes para continuar.', 'warning');
        return;
    }

    const form = document.getElementById('piezaForm');
    const formData = new FormData(form);
    formData.append('action', 'save');
    
    // Adjuntamos las imágenes nuevas
    uploadedFiles.forEach((file, index) => {
        formData.append(`imagenes[${index}]`, file);
    });

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = 'Guardando...';

    fetch('Catalogo.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            const data = await response.json();
            if (data.status === 'success') {
                Swal.fire('¡Éxito!', 'La pieza se guardó correctamente.', 'success')
                .then(() => location.reload());
            } else {
                throw new Error(data.message || 'Error interno de validación.');
            }
        } else {
            console.error(await response.text());
            throw new Error('El servidor falló. Revisa la consola para ver el error de PHP.');
        }
    })
    .catch(error => {
        console.error(error);
        Swal.fire('Error al guardar', error.message, 'error'); 
        btn.disabled = false;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Guardar pieza';
    });
}

// ==========================================
// INICIALIZACIÓN
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('#piezaForm input, #piezaForm select, #piezaForm textarea');
    inputs.forEach(input => {
        input.addEventListener('input', updatePreview);
    });
});