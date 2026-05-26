document.addEventListener("DOMContentLoaded", () => {
    // 1. BASE DE DATOS SIMULADA (Tus piezas reales de SQL con prefijos BOM y REF)
    const dummyProducts = [
        {
            id: 12,
            sku: "REF-DE-BR62",
            title: "Disco de equilibrio",
            desc: "Material: Fabricado en Bronce SAE 62 'C', con anillo fabricado en tubo mecánico y tornillos de latón.",
            category: "Refacciones",
            images: [
                "Disco de equilibrio.jpeg",

            ]
        },
        {
            id: 13,
            sku: "REF-IMP-HG4",
            title: "Impulsor",
            desc: "Material: Fabricado en hierro gris.<br><br>Uso: Impulsor cerrado con 4 aspas que genera fuerza centrífuga que impulsa el fluido hacia el exterior de la carcaza, transformando energía mecánica en presión hidráulica.",
            category: "Refacciones",
            images: [
                "Impulsor.jpeg",

            ]
        },
        {
            id: 14,
            sku: "REF-EJE-4140",
            title: "Eje",
            desc: "Material: Fabricado en AISI 4140T.<br><br>Uso: Es el eslabón mecánico de potencia de una bomba. Recibe la potencia del motor y la transmite al impulsor.",
            category: "Refacciones",
            images: [
                "Eje SWP-150.jpg",
            ]
        },
        {
            id: 15,
            sku: "REF-RET-VT",
            title: "Retén",
            desc: "Material: Vitón.<br><br>Uso: Sellado de alta calidad para contener lubricantes, aceites, grasas, y proteger componentes internos de los equipos.",
            category: "Refacciones",
            images: [
                "Reten.jpeg",
            ]
        },
        {
            id: 16,
            sku: "BOM-SWP150-CH",
            title: "SWP 150 - Bomba centrífuga horizontal",
            desc: "Material: Fabricado en hierro gris, sellado con estoperos, retenes para el sellado de aceite, eje construido en AISI 4140T.",
            category: "Equipos",
            images: [
                "SWP-150_2.jpg",
            ]
        }
    ];

    // 2. SELECCIÓN DE ELEMENTOS DEL DOM
    const catalogGrid = document.getElementById("catalog-grid");
    const searchBar = document.getElementById("search-bar");
    const filterBtns = document.querySelectorAll(".filter-btn");

    const modal = document.getElementById("product-modal");
    const closeModalBtn = document.querySelector(".modal-close");
    const modalImg = document.getElementById("modal-img");
    const modalThumbnails = document.getElementById("modal-thumbnails");
    const modalCategory = document.getElementById("modal-category");
    const modalTitle = document.getElementById("modal-title");
    const modalDesc = document.getElementById("modal-desc");
    const modalCompatibility = document.getElementById("modal-compatibility");
    const modalQuoteBtn = document.getElementById("modal-quote-btn");

    // 3. ESTADOS VARIABLES DE FILTRADO
    let currentFilter = "ALL";
    let currentSearchTerm = "";

    // 4. FUNCIÓN CENTRAL DE FILTRADO (Buscador + Filtros por iniciales del SKU)
    function applyFilters() {
        let filteredProducts = dummyProducts;

        // Filtrar por prefijo de SKU (BOM o REF)
        if (currentFilter !== "ALL") {
            filteredProducts = filteredProducts.filter(p => p.sku.startsWith(currentFilter));
        }

        // Filtrar por coincidencia de texto en la barra de búsqueda
        if (currentSearchTerm.trim() !== "") {
            const term = currentSearchTerm.toLowerCase();
            filteredProducts = filteredProducts.filter(p => 
                p.title.toLowerCase().includes(term) || 
                p.sku.toLowerCase().includes(term) || 
                p.desc.toLowerCase().includes(term)
            );
        }

        // Renderizar los productos resultantes
        renderCatalog(filteredProducts);
    }

    // 5. RENDERIZADO DE LAS TARJETAS DENTRO DEL GRID MODERNIZADO
    function renderCatalog(productsToRender) {
        if (!catalogGrid) return;
        
        catalogGrid.innerHTML = ""; 
        catalogGrid.className = "catalog-grid-modern"; 

        if (productsToRender.length === 0) {
            catalogGrid.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #64748b;">
                    <span class="material-icons-outlined" style="font-size: 3rem; margin-bottom: 1rem;">search_off</span>
                    <p style="font-family: 'Montserrat', sans-serif; font-size: 1.1rem;">No se encontraron piezas o bombas con esos criterios.</p>
                </div>`;
            return;
        }

        productsToRender.forEach(product => {
            const card = document.createElement("article");
            card.className = "product-card";
            card.innerHTML = `
                <div class="product-image-wrapper">
                    <img src="${product.images[0]}" alt="${product.title}">
                    <div class="gallery-indicator" title="Ver galería de imágenes">
                        <span class="material-icons-outlined">photo_camera</span>
                        <span>1/${product.images.length}</span>
                    </div>
                </div>
                <div class="product-info">
                    <span class="product-sku">SKU: ${product.sku}
                    <h3 class="product-title">${product.title}</h3>
                    <p class="product-desc" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                        ${product.desc.replace(/<br>/g, ' ')}
                    </p>
                    <button class="btn-details" data-id="${product.id}" aria-label="Ver detalles de ${product.title}">
                        <span class="material-icons-outlined">visibility</span>
                        <span>Ver detalles</span>
                    </button>
                </div>
            `;
            catalogGrid.appendChild(card);
        });
    }

    // 6. DELEGACIÓN DE EVENTOS SEGURO PARA EL BOTÓN "VER DETALLES"
    if (catalogGrid) {
        catalogGrid.addEventListener("click", (e) => {
            const btn = e.target.closest(".btn-details");
            if (btn) {
                const productId = parseInt(btn.getAttribute("data-id"));
                openModal(productId);
            }
        });
    }

    // 7. GESTIÓN DE INFORMACIÓN Y GALERÍA DENTRO DEL MODAL
    function openModal(id) {
        if (!modal) return;

        const product = dummyProducts.find(p => p.id === id);
        if (!product) return;

        if (modalCategory) modalCategory.textContent = `${product.category} | SKU: ${product.sku}`;
        if (modalTitle) modalTitle.textContent = product.title;
        if (modalDesc) modalDesc.innerHTML = product.desc;
        if (modalImg) modalImg.src = product.images[0];
        
        if (modalThumbnails) {
            modalThumbnails.innerHTML = "";
            product.images.forEach((imgSrc, index) => {
                const thumb = document.createElement("img");
                thumb.src = imgSrc;
                thumb.style.width = "60px";
                thumb.style.height = "60px";
                thumb.style.objectFit = "cover";
                thumb.style.border = index === 0 ? "2px solid #0284c7" : "1px solid #ccc";
                thumb.style.borderRadius = "4px";
                thumb.style.cursor = "pointer";
                thumb.style.transition = "border 0.2s ease";

                thumb.addEventListener("click", () => {
                    if (modalImg) modalImg.src = imgSrc;
                    Array.from(modalThumbnails.children).forEach(child => child.style.border = "1px solid #ccc");
                    thumb.style.border = "2px solid #0284c7";
                });

                modalThumbnails.appendChild(thumb);
            });
        }

        modal.style.display = "flex";
    }

    // Cierre del Modal
    if (closeModalBtn) {
        closeModalBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

    // Envío alternativo para cotizaciones (SweetAlert2)
    if (modalQuoteBtn) {
        modalQuoteBtn.addEventListener("click", () => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Añadir a Cotización',
                    text: '¿Cómo prefieres que te enviemos la información?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#25D366',
                    cancelButtonColor: '#0284c7',
                    confirmButtonText: '<i class="material-icons-outlined" style="vertical-align:middle;">whatsapp</i> WhatsApp',
                    cancelButtonText: '<i class="material-icons-outlined" style="vertical-align:middle;">email</i> Correo Electrónico',
                    showDenyButton: true,
                    denyButtonText: 'Ambos',
                    denyButtonColor: '#475569'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire('¡Añadido!', 'Te contactaremos vía WhatsApp.', 'success');
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        Swal.fire('¡Añadido!', 'Te enviaremos la cotización por Correo.', 'success');
                    } else if (result.isDenied) {
                        Swal.fire('¡Añadido!', 'Registramos ambas preferencias de contacto.', 'success');
                    }
                });
            } else {
                alert("Pieza añadida a la cotización.");
            }
        });
    }

    // 8. ESCUCHADORES DE EVENTOS DE ENTRADA EN TIEMPO REAL
    if (searchBar) {
        searchBar.addEventListener("input", (e) => {
            currentSearchTerm = e.target.value;
            applyFilters();
        });
    }

    filterBtns.forEach(btn => {
        btn.addEventListener("click", (e) => {
            filterBtns.forEach(b => b.classList.remove("active"));
            e.currentTarget.classList.add("active");

            currentFilter = e.currentTarget.getAttribute("data-filter");
            applyFilters();
        });
    });

    // 9. INICIALIZACIÓN INMEDIATA AL CARGAR LA PÁGINA
    applyFilters(); 
});