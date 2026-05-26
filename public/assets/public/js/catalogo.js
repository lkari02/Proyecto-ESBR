document.addEventListener('DOMContentLoaded', () => {
    // --- VARIABLES GLOBALES ---
    let allProducts = [];
    let currentLang = document.documentElement.lang === 'en' ? 'en' : 'es';
    let currentCategoryFilter = 'all'; // NUEVA VARIABLE

    // --- ELEMENTOS DEL DOM ---
    const catalogList = document.getElementById('catalog-list');
    const searchBar = document.getElementById('search-bar');
    const emptyState = document.getElementById('empty-state');

    // --- VISOR DE IMÁGENES EN PANTALLA COMPLETA ---
    const zoomOverlay = document.createElement('div');
    zoomOverlay.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(0,0,0,0.95); z-index:100000; align-items:center; justify-content:center; cursor:zoom-out;';
    const zoomImg = document.createElement('img');
    zoomImg.style.cssText = 'max-width:95vw; max-height:95vh; object-fit:contain; border-radius:4px;';
    zoomOverlay.appendChild(zoomImg);
    document.body.appendChild(zoomOverlay);

    zoomOverlay.addEventListener('click', () => {
        zoomOverlay.style.display = 'none';
    });

    // --- LÓGICA DE TEXTOS SEGURA ---
    const getLocalizedText = (producto, campo) => {
        const langData = producto.traducciones?.[currentLang];
        const valTraduccion = campo === 'nombre' ? langData?.nombre : langData?.desc;
        const valBase = campo === 'nombre' ? producto.name : producto.desc;

        if (valTraduccion && !valTraduccion.startsWith('Sin ') && !valTraduccion.startsWith('No ')) {
            return valTraduccion;
        }
        return valBase;
    };

    // --- OBSERVADOR DE IDIOMA EN TIEMPO REAL ---
    const langObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'lang') {
                const newLang = document.documentElement.lang === 'en' ? 'en' : 'es';
                if (newLang !== currentLang) {
                    currentLang = newLang; 
                    
                    // Cerramos cualquier panel abierto al cambiar de idioma para evitar inconsistencias
                    const openPanel = document.querySelector('.cat-detail-panel');
                    if(openPanel) openPanel.remove();
                    
                    renderList(allProducts); 

                    // Actualizar el botón del PDF
                    const btnPdf = document.getElementById('btn-download-pdf');
                    const textBtnPdf = document.getElementById('text-btn-pdf');
                    if(btnPdf && textBtnPdf) {
                        btnPdf.href = `/Proyecto/app/services/catalogo_pdf_publico.php?lang=${currentLang}`;
                        textBtnPdf.textContent = currentLang === 'en' ? 'Download Catalog' : 'Descargar Catálogo';
                    }
                }
            }
        });
    });
    langObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });

    // --- 1. CARGA DE PRODUCTOS ---
    const fetchProducts = async (term = '') => {
        try {
            const response = await fetch(`/Proyecto/app/api/api_get_productos.php?q=${encodeURIComponent(term)}`);
            if (!response.ok) throw new Error('Error en el servidor');
            allProducts = await response.json();
            
            // En lugar de renderList directo, pasamos por el filtro local
            applyFiltersAndRender(); 
        } catch (error) {
            console.error('Error al cargar:', error);
            if(catalogList) catalogList.innerHTML = '<p style="color:red; padding:2rem; text-align:center;">Error al cargar las piezas. Verifica la conexión.</p>';
        }
    };

    // --- NUEVO: FUNCIÓN PARA FILTRAR POR SKU ---
    const applyFiltersAndRender = () => {
        let filtered = allProducts;
        
        if (currentCategoryFilter !== 'all') {
            filtered = allProducts.filter(p => {
                // Verificamos que tenga SKU y empiece con el prefijo seleccionado (BOM o REF)
                return p.sku && p.sku.toUpperCase().startsWith(currentCategoryFilter);
            });
        }
        
        renderList(filtered);
    };

    // --- 2. RENDERIZAR LISTA (FILAS) ---
    const renderList = (products) => {
        if(!catalogList) return;
        catalogList.innerHTML = '';

        if (products.length === 0) {
            emptyState.style.display = 'block';
            emptyState.querySelector('p').textContent = currentLang === 'en' ? 'No parts found.' : 'No se encontraron piezas.';
            return;
        } else {
            if(emptyState) emptyState.style.display = 'none';
        }

        const basePath = '/Proyecto/public/admin/uploads/piezas/';

        products.forEach(p => {
            const nombreFinal = getLocalizedText(p, 'nombre');
            const descFinal = getLocalizedText(p, 'desc');
            const fileName = p.img ? p.img.split('/').pop() : '';
            const imgSrc = fileName ? (basePath + fileName) : 'https://placehold.co/400x300/f1f5f9/a0aab5?text=Sin+Imagen';
            const imgCount = p.allImages ? p.allImages.length : (fileName ? 1 : 0);
            
            const stockText = currentLang === 'en' ? 'In Stock' : 'En Stock';
            const btnTextCard = currentLang === 'en' ? 'View Details' : 'Ver Detalles'; // <-- Traducción del botón

            catalogList.innerHTML += `
                <div class="cat-row" data-id="${p.id}">
                    <div class="cat-row-img-wrap">
                        <img src="${imgSrc}" alt="${nombreFinal}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='https://placehold.co/400x300/f1f5f9/a0aab5?text=Sin+Imagen';">
                        ${imgCount > 1 ? `<span class="cat-img-count">+${imgCount}</span>` : ''}
                    </div>
                    
                    <div class="cat-row-info">
                        <span class="cat-row-sku">SKU: ${p.sku || 'N/A'}</span>
                        <h3 class="cat-row-title">${nombreFinal}</h3>
                        <p class="cat-row-meta">${descFinal.substring(0, 70)}...</p>
                        <div><span class="cat-row-badge">${p.marca || (currentLang === 'en' ? 'Generic' : 'Genérica')}</span></div>
                    </div>
                    
                    <div class="cat-row-action-zone">
                        <button class="cat-row-action" aria-label="${btnTextCard}">
                            ${btnTextCard} <span class="material-icons-outlined">chevron_right</span>
                        </button>
                    </div>
                </div>
            `;
        });
    };

    // --- 3. EVENTOS: ABRIR PANEL DE DETALLES INLINE ---
    if(catalogList) {
        catalogList.addEventListener('click', (e) => {
            const row = e.target.closest('.cat-row');
            if (row) {
                const product = allProducts.find(item => item.id == row.dataset.id);
                if (product) toggleDetailPanel(row, product);
            }
        });
    }

    // --- 4. LÓGICA DEL PANEL DESPLEGABLE ---
    const toggleDetailPanel = (row, product) => {
        // Cerrar paneles abiertos previamente
        const existingPanel = document.querySelector('.cat-detail-panel');
        if (existingPanel) {
            const isSameRow = existingPanel.previousElementSibling === row;
            existingPanel.remove();
            if (isSameRow) return; // Si se clicó la misma fila, actúa como un toggle (solo cierra)
        }

        const nombreFinal = getLocalizedText(product, 'nombre');
        const descFinal = getLocalizedText(product, 'desc');
        const basePath = '/Proyecto/public/admin/uploads/piezas/';
        
        let images = [];
        if (product.allImages && product.allImages.length > 0) {
            images = product.allImages.map(imgString => basePath + imgString.split('/').pop());
        } else if (product.img) {
            images = [basePath + product.img.split('/').pop()];
        } else {
            images = ['https://placehold.co/400x300/f1f5f9/a0aab5?text=Sin+Imagen'];
        }

        const compatText = product.compatibilidad && product.compatibilidad.length > 0 
            ? product.compatibilidad.map(c => `<span class="cat-compat-tag">${c}</span>`).join('') 
            : `<span class="cat-compat-tag">${currentLang === 'en' ? 'Universal' : 'Universal'}</span>`;

        // Generar el HTML del panel
        const panelHTML = `
            <div class="cat-detail-panel open" id="active-detail-panel">
                <div class="cat-detail-inner">
                    <div class="cat-detail-img-col">
                        <div class="cat-detail-main-img">
                            <img id="panel-main-img" src="${images[0]}" alt="${nombreFinal}" style="max-width:100%; max-height:100%; object-fit:contain; cursor:zoom-in;">
                        </div>
                        <div class="cat-detail-thumbs">
                            ${images.map((src, i) => `
                                <div class="cat-detail-thumb ${i===0?'active':''}" data-src="${src}">
                                    <img src="${src}" style="width:100%; height:100%; object-fit:cover; border-radius:4px;">
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="cat-detail-info-col">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <div>
                                <p class="cat-detail-eyebrow">${product.marca || 'BombaParts'}</p>
                                <h2 class="cat-detail-title">${nombreFinal}</h2>
                            </div>
                            <button class="cat-btn-close-panel" id="btn-close-panel">
                                <span class="material-icons-outlined">close</span>
                                ${currentLang === 'en' ? 'Close' : 'Cerrar'}
                            </button>
                        </div>
                        
                        <p class="cat-detail-desc">${descFinal}</p>
                        
                        <div class="cat-spec-grid">
                            <div class="cat-spec">
                                <span class="cat-spec-label">SKU</span>
                                <span class="cat-spec-val">${product.sku || 'N/A'}</span>
                            </div>
                            <div class="cat-spec">
                                <span class="cat-spec-label">${currentLang === 'en' ? 'Brand' : 'Marca'}</span>
                                <span class="cat-spec-val">${product.marca || 'N/A'}</span>
                            </div>
                        </div>
                        
                        <hr class="cat-detail-divider">
                        
                        <div class="cat-detail-compat-label">${currentLang === 'en' ? 'Compatible with:' : 'Compatible con:'}</div>
                        <div class="cat-compat-tags">
                            ${compatText}
                        </div>
                        
                        <div class="cat-detail-bottom">
                            <div class="cat-detail-stock">
                                <span class="material-icons-outlined" style="color:var(--cat-green); font-size:18px;">check_circle</span>
                                <span><strong>${currentLang === 'en' ? 'Available' : 'Disponible'}</strong> ${currentLang === 'en' ? 'to quote' : 'para cotizar'}</span>
                            </div>
                            <button class="cat-btn-quote" id="panel-quote-btn">
                                <span class="material-icons-outlined">add_shopping_cart</span>
                                ${currentLang === 'en' ? 'Add to Quote' : 'Añadir a Cotización'}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Inyectar el panel justo debajo de la fila clickeada
        row.insertAdjacentHTML('afterend', panelHTML);

        // --- BINDING DE EVENTOS DEL PANEL ---
        const panel = document.getElementById('active-detail-panel');
        
        // GalerÍa de imágenes
        const mainImg = document.getElementById('panel-main-img');
        const thumbs = panel.querySelectorAll('.cat-detail-thumb');
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', () => {
                mainImg.src = thumb.dataset.src;
                thumbs.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });

        // Zoom
        mainImg.addEventListener('click', () => {
            zoomImg.src = mainImg.src;
            zoomOverlay.style.display = 'flex';
        });

        // Cerrar panel
        document.getElementById('btn-close-panel').addEventListener('click', () => {
            panel.remove();
        });

        // Añadir a cotización
        document.getElementById('panel-quote-btn').addEventListener('click', () => {
            addToQuote(product);
        });
    };

    // --- 5. LÓGICA DE AÑADIR A COTIZACIÓN ---
    const addToQuote = (product) => {
        const nombreFinal = getLocalizedText(product, 'nombre');
        let cart = JSON.parse(localStorage.getItem('BombaPartsQuote') || '[]');
        const idx = cart.findIndex(item => item.id == product.id);

        const basePath = '/Proyecto/public/admin/uploads/piezas/';
        const fileName = product.img ? product.img.split('/').pop() : '';
        
        if (idx > -1) {
            cart[idx].quantity += 1;
        } else {
            cart.push({ 
                id: product.id, 
                sku: product.sku, 
                name: nombreFinal, 
                img: fileName ? (basePath + fileName) : '', 
                marca: product.marca, 
                precio: product.precio, 
                quantity: 1 
            });
        }
        
        localStorage.setItem('BombaPartsQuote', JSON.stringify(cart));
        
        // Cerrar panel tras añadir
        const openPanel = document.querySelector('.cat-detail-panel');
        if(openPanel) openPanel.remove();
        
        Swal.fire({
            icon: 'success',
            title: currentLang === 'en' ? 'Success!' : '¡Éxito!',
            text: currentLang === 'en' ? 'The part was added to the quote.' : 'La pieza se añadió a la cotización correctamente.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#185FA5' // Alineado con var(--cat-blue)
        });
    };
         
    // --- NUEVO: EVENTOS DE LOS BOTONES DE FILTRO ---
    const filterButtons = document.querySelectorAll('.cat-pill');
    if (filterButtons.length > 0) {
        filterButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                // 1. Gestionar las clases visuales
                filterButtons.forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                
                // 2. Actualizar la variable global del filtro
                currentCategoryFilter = e.target.dataset.filter;
                
                // 3. Cerrar cualquier panel de detalles abierto para evitar inconsistencias visuales
                const openPanel = document.querySelector('.cat-detail-panel');
                if(openPanel) openPanel.remove();
                
                // 4. Aplicar el filtro y renderizar
                applyFiltersAndRender();
            });
        });
    }
    // --- 6. INICIAR ---
    if(searchBar) searchBar.addEventListener('input', (e) => fetchProducts(e.target.value));
    fetchProducts();
});