document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. DEFINICIÓN DE VARIABLES ---
    const quoteList = document.getElementById('quote-list');
    const totalArticlesSpan = document.getElementById('total-articles');
    const form = document.getElementById('quote-contact-form');
    const tipoClienteSelect = document.getElementById('tipo-cliente');
    const orgWrapper = document.getElementById('organizacion-wrapper');
    const orgInput = document.getElementById('nombre-organizacion');
    const modalConfirm = document.getElementById('modal-confirm');
    const btnConfirmYes = document.getElementById('btn-confirm-yes');
    const toastContainer = document.getElementById('toast-container'); // NUEVO

    // --- 2. SISTEMA DE NOTIFICACIONES (TOASTS) ---
    const showToast = (message, type = 'success') => {
        if (!toastContainer) return;
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        const icon = type === 'success' ? 'check_circle' : 'error';
        
        toast.innerHTML = `
            <span class="material-icons-outlined toast-icon">${icon}</span>
            <span>${message}</span>
        `;
        toastContainer.appendChild(toast);

        // Desaparecer después de 4 segundos
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 400); // Esperar a que termine la animación
        }, 4000);
    };

    // --- 3. CARGAR PRODUCTOS DEL CARRITO ---
    let cart = JSON.parse(localStorage.getItem('BombaPartsQuote') || '[]');

    const renderCart = () => {
        if (!quoteList) return;
        quoteList.innerHTML = '';
        let totalItems = 0;

        if (cart.length === 0) {
            quoteList.innerHTML = `
                <div style="text-align:center; padding: 3rem 1rem; background: #fff; border-radius: 8px; border: 1px dashed #cbd5e1;">
                    <span class="material-icons-outlined" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;">shopping_cart</span>
                    <p style="color: #64748b; margin: 0;">Tu lista de cotización está vacía.</p>
                </div>`;
            if(totalArticlesSpan) totalArticlesSpan.textContent = '0';
            return;
        }

        cart.forEach((item, index) => {
            totalItems += item.quantity;
            // Nueva tarjeta estilizada
            const itemHTML = `
                <div class="cart-item">
                    <img src="${item.img}" alt="${item.name}" onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=BombaParts'">
                    <div style="flex-grow:1;">
                        <p style="font-size:0.75rem; color:#64748b; margin:0 0 0.2rem 0; font-weight:600;">SKU: ${item.sku} | ${item.marca}</p>
                        <h3 style="margin:0; font-size:1.05rem; color:var(--blue-dark); font-family:var(--font-display);">${item.name}</h3>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.5rem; margin: 0 1rem;">
                        <button type="button" class="btn-qty-minus" data-index="${index}" style="width:28px; height:28px; border-radius:4px; border:1px solid #cbd5e1; background:#fff; cursor:pointer;">-</button>
                        <span style="font-weight:bold; min-width:20px; text-align:center; color:var(--blue-dark);">${item.quantity}</span>
                        <button type="button" class="btn-qty-plus" data-index="${index}" style="width:28px; height:28px; border-radius:4px; border:1px solid #cbd5e1; background:#fff; cursor:pointer;">+</button>
                    </div>
                    <button type="button" class="btn-remove" data-index="${index}" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:0.5rem; transition:0.2s;">
                        <span class="material-icons-outlined">delete</span>
                    </button>
                </div>
            `;
            quoteList.insertAdjacentHTML('beforeend', itemHTML);
        });

        if(totalArticlesSpan) totalArticlesSpan.textContent = totalItems;
        attachCartEvents();
    };

    // --- 4. EVENTOS DE CANTIDAD Y ELIMINAR ---
    const attachCartEvents = () => {
        document.querySelectorAll('.btn-qty-plus').forEach(btn => {
            btn.addEventListener('click', (e) => { cart[e.target.dataset.index].quantity += 1; updateCart(); });
        });
        document.querySelectorAll('.btn-qty-minus').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const idx = e.target.dataset.index;
                if (cart[idx].quantity > 1) cart[idx].quantity -= 1;
                else cart.splice(idx, 1);
                updateCart();
            });
        });
        document.querySelectorAll('.btn-remove').forEach(btn => {
            btn.addEventListener('click', (e) => { cart.splice(e.currentTarget.dataset.index, 1); updateCart(); });
        });
    };

    const updateCart = () => { localStorage.setItem('BombaPartsQuote', JSON.stringify(cart)); renderCart(); };

    // --- 5. LÓGICA DEL FORMULARIO ---
    if (tipoClienteSelect && orgWrapper && orgInput) {
        tipoClienteSelect.addEventListener('change', (e) => {
            const val = e.target.value;
            if (val === 'Empresa' || val === 'Distribuidor' || val === 'Institucion publica') {
                orgWrapper.style.display = 'block';
                orgInput.required = true;
            } else {
                orgWrapper.style.display = 'none';
                orgInput.required = false;
                orgInput.value = ''; 
            }
        });
    }

            // --- 6. ABRIR MODAL DE CONFIRMACIÓN (Al dar clic en Solicitar Cotización) ---
    if (form) {
        form.addEventListener('submit', (e) => {
            // Evitamos que la página se recargue
            e.preventDefault(); 
            
            // Verificamos que haya piezas
            if (cart.length === 0) {
                showToast("Agrega piezas al carrito primero.", "error");
                return;
            }
            
            // Si el checkbox está marcado y todo está bien, mostramos el modal
            if (modalConfirm) {
                modalConfirm.style.display = 'flex';
            }
        });
    }

    // --- CERRAR MODAL SI LE DAN A "REVISAR DE NUEVO" ---
    const btnConfirmNo = document.getElementById('btn-confirm-no');
    if (btnConfirmNo) {
        btnConfirmNo.addEventListener('click', () => {
            if (modalConfirm) modalConfirm.style.display = 'none';
        });
    }

    // --- 7. ENVÍO REAL A LA BASE DE DATOS ---
    if (btnConfirmYes) {
        btnConfirmYes.addEventListener('click', async () => {
            if (modalConfirm) modalConfirm.style.display = 'none';
            btnConfirmYes.textContent = "Procesando...";
            btnConfirmYes.disabled = true;

            const formData = new FormData(form);
            const dataPayload = {
                cliente: {
                    nombre: formData.get('name') || '', email: formData.get('email') || '', telefono: formData.get('phone') || '',
                    pais: formData.get('pais') || '', ubicacion: formData.get('ubicacion') || '', tipo_cliente: formData.get('tipo_cliente') || 'Empresa',
                    organizacion: formData.get('nombre_organizacion') || '', mensaje: formData.get('message') || '', preferencia: formData.get('preferencia_contacto') || 'No especificado'
                },
                carrito: cart
            };

            try {
                const response = await fetch('/Proyecto/app/api/api_guardar_cotizacion.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(dataPayload)
                });
                const result = await response.json();

                // (Esto va justo debajo de: const result = await response.json(); )
                    if (result.success) {
                    showToast("¡La cotización se guardó correctamente!", "success");

                    // 1. Llenamos los datos del Cliente en el Modal
                    document.getElementById('detail-folio').textContent = result.codigo;
                    document.getElementById('detail-name').textContent = formData.get('name') || '';
                    document.getElementById('detail-email').textContent = formData.get('email') || '';
                    document.getElementById('detail-phone').textContent = formData.get('phone') || '';
                    document.getElementById('detail-location').textContent = formData.get('ubicacion') || '';
                    document.getElementById('detail-country').textContent = formData.get('pais') || '';
                    document.getElementById('detail-client-type').textContent = formData.get('tipo_cliente') || '';
                    
                    const org = formData.get('nombre_organizacion');
                    if (org) {
                        document.getElementById('detail-org-wrapper').style.display = 'block';
                        document.getElementById('detail-org').textContent = org;
                    } else {
                        document.getElementById('detail-org-wrapper').style.display = 'none';
                    }

                    document.getElementById('detail-pref').textContent = formData.get('preferencia_contacto') || '';
                    document.getElementById('detail-message').textContent = formData.get('message') || 'Sin comentarios adicionales.';

                    // 2. Llenamos los Productos en el Modal
                    const productListContainer = document.getElementById('detail-products-list');
                    productListContainer.innerHTML = '';
                    
                    // ... (Debajo de: productListContainer.innerHTML = ''; )
                    
                    cart.forEach(item => {
                        productListContainer.innerHTML += `
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.8rem; background: #ffffff; padding: 0.8rem; border-radius: 6px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                <img src="${item.img}" alt="${item.name}" onerror="this.src='https://placehold.co/100x100?text=BombaParts'" style="width: 50px; height: 50px; object-fit: contain; padding: 2px;">
                                <div style="flex-grow: 1;">
                                    <h4 style="margin: 0 0 0.2rem 0; font-size: 0.95rem; color: #1e293b; font-weight: 600;">${item.name}</h4>
                                    <p style="margin: 0; font-size: 0.8rem; color: #64748b;">SKU: ${item.sku} | Marca: ${item.marca}</p>
                                </div>
                                <div style="text-align: right; background: #f8fafc; padding: 0.5rem 0.8rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                                    <span style="font-size: 0.75rem; color: #64748b; display: block; text-transform: uppercase;">Cant.</span>
                                    <span style="display: block; font-weight: bold; color: var(--blue-main, #007bff); font-size: 1.1rem;">${item.quantity}</span>
                                </div>
                            </div>
                        `;
                    });

                    // 3. Configuramos el enlace del PDF
                    const btnDetailsPdf = document.getElementById('btn-details-pdf');
                    if (btnDetailsPdf) {
                        btnDetailsPdf.href = `/Proyecto/app/services/generar_cotizacion_pdf.php?folio=${result.codigo}`;
                    }

                    // 4. Limpiamos el carrito en la memoria RAM y mostramos el modal
                    localStorage.removeItem('BombaPartsQuote');
                    document.getElementById('modal-details').style.display = 'flex';

                } else {
                    showToast(`Error de Base de Datos: ${result.error}`, "error");
                    btnConfirmYes.textContent = "Sí, Enviar"; 
                    btnConfirmYes.disabled = false;
                }
            } catch (error) {
                console.error("Error en la petición:", error);
                showToast("Hubo un error de comunicación con el servidor.", "error");
                btnConfirmYes.textContent = "Sí, Enviar"; btnConfirmYes.disabled = false;
            }
        });
    }

    renderCart();
});

// --- LÓGICA DE BOTONES DEL MODAL DE DETALLES ---
    const closeDetailsModal = () => {
        document.getElementById('modal-details').style.display = 'none';
        window.location.href = "catalogo.html"; // Regresa al catálogo al cerrar
    };

    const btnDetailsOk = document.getElementById('btn-details-ok');
    const closeDetailsX = document.getElementById('close-details');

    if (btnDetailsOk) btnDetailsOk.addEventListener('click', closeDetailsModal);
    if (closeDetailsX) closeDetailsX.addEventListener('click', closeDetailsModal);

    // Añade esta línea al final de tus funciones de renderizado en cotizacion.js:
if (typeof setLanguage === 'function') {
    setLanguage(localStorage.getItem('selectedLanguage') || 'es');
}