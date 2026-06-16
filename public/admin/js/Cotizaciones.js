// ==========================================
// 2. BUSCADOR Y CÁLCULOS
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById('buscador-cotizaciones');
    if(buscador) {
        buscador.addEventListener('input', function(e) {
            const termino = e.target.value.toLowerCase();
            document.querySelectorAll('.table-wrap tbody tr').forEach(fila => {
                const sku = fila.querySelector('td:nth-child(1)').innerText.toLowerCase();
                const cliente = fila.querySelector('td:nth-child(2)').innerText.toLowerCase();
                const org = fila.querySelector('td:nth-child(3)').innerText.toLowerCase();
                fila.style.display = (sku.includes(termino) || cliente.includes(termino) || org.includes(termino)) ? '' : 'none';
            });
        });
    }

    const tablaProductos = document.getElementById('tabla-productos-body');
    if(tablaProductos) {
        tablaProductos.addEventListener('input', function(e) {
            if (e.target.classList.contains('input-precio')) recalcularTotalVisual();
        });
    }
});

function recalcularTotalVisual() {
    let granTotal = 0;
    document.querySelectorAll('#tabla-productos-body tr').forEach(fila => {
        const cantidad = parseFloat(fila.dataset.cantidad) || 1; 
        const inputPrecio = fila.querySelector('.input-precio');
        if (inputPrecio) {
            const subtotal = cantidad * (parseFloat(inputPrecio.value) || 0);
            const tdSubtotal = fila.querySelector('.celda-subtotal');
            if(tdSubtotal) tdSubtotal.innerText = '$' + subtotal.toFixed(2);
            granTotal += subtotal;
        }
    });
    document.getElementById('d-total').innerText = '$' + granTotal.toFixed(2);
}

        // ==========================================
// 3. LÓGICA DEL MODAL DE DETALLES
// ==========================================
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-ver-detalle');
    if (btn) {
        // 1. Cargar datos básicos en el modal (desde los atributos data- del botón)
        const cotizacionId = btn.dataset.id;
        const codigo = btn.dataset.codigo || '-';
        const estado = (btn.dataset.estado || 'pendiente').toLowerCase();
        
        document.getElementById('d-codigo').innerText = codigo;
        document.getElementById('d-fecha').innerText = btn.dataset.fecha || '-';
        document.getElementById('d-cliente').innerText = btn.dataset.cliente || '-';
        document.getElementById('d-organizacion').innerText = btn.dataset.org || '-';
        document.getElementById('d-email').innerText = btn.dataset.email || '-';
        document.getElementById('d-total').innerText = '$' + (parseFloat(btn.dataset.total) || 0).toFixed(2);
        
        // --- NUEVA LÓGICA DE SEPARACIÓN EN JAVASCRIPT ---
        const rawNotas = btn.dataset.raw || '';
        let preferenciaReal = 'Ambos';
        let notasFinales = rawNotas;

        // Verificamos si el texto tiene el formato esperado
        if (rawNotas.toLowerCase().includes('preferencia:') && rawNotas.toLowerCase().includes('mensaje:')) {
            // Partimos el texto usando "Mensaje:" o "mensaje:" como cuchillo
            const partes = rawNotas.split(/mensaje:/i); 
            
            // Limpiamos la primera parte
            preferenciaReal = partes[0].replace(/preferencia:/i, '').trim();
            
            // Limpiamos la segunda parte
            notasFinales = partes[1].trim();
        }

        // Inyectamos los resultados en el modal
        document.getElementById('d-pref-contacto').innerText = preferenciaReal || 'Ambos';
        
        const divNotas = document.getElementById('d-notas');
        if (divNotas) {
            divNotas.innerText = notasFinales || 'Sin notas adicionales';
        }
        // ------------------------------------------------

        const badge = document.getElementById('d-estado');

        const inputVigencia = document.getElementById('input-vigencia');
        if(inputVigencia) inputVigencia.value = btn.dataset.vigencia || 30;

        // Mostrar/Ocultar elementos según Estado
        const btnConfirmVigencia = document.getElementById('btn-confirm-vigencia');
        const accionesIniciales = document.getElementById('acciones-iniciales'); 
        const accionesPost = document.getElementById('acciones-post'); 
        const footerModal = document.getElementById('detalles-acciones');
        
        if(footerModal) footerModal.classList.remove('hidden');
        document.getElementById('contact-channels').style.display = 'none';

        if (estado === 'pendiente') {
            if(inputVigencia) inputVigencia.disabled = false;
            if(btnConfirmVigencia) btnConfirmVigencia.style.display = 'inline-block';
            if(accionesIniciales) accionesIniciales.style.display = 'flex';
            if(accionesPost) accionesPost.style.display = 'none';
        } else {
            if(inputVigencia) inputVigencia.disabled = true;
            if(btnConfirmVigencia) btnConfirmVigencia.style.display = 'none';
            if(accionesIniciales) accionesIniciales.style.display = 'none'; 
            if(accionesPost) accionesPost.style.display = 'flex';           
        }

        // 2. Mostrar mensaje de carga en la tabla de productos
        const tbody = document.getElementById('tabla-productos-body');
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 20px; color: var(--text-muted);">Cargando productos...</td></tr>';

        // 3. Abrir el modal visualmente
        document.getElementById('modal-cotizacion').classList.add('open');
        document.getElementById('cotizacion-detalles').classList.remove('hidden');

        // 4. PETICIÓN AJAX PARA TRAER LOS PRODUCTOS
        const formData = new FormData();
        formData.append('action', 'ver_detalles');
        formData.append('id', cotizacionId);

        // Apunta al archivo PHP que devuelve los detalles (el que armamos antes)
        fetch('Cotizaciones.php', { 
            method: 'POST', 
            body: formData 
        })
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                const productos = response.data;
                
                if (productos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 20px;">No hay productos registrados en esta cotización.</td></tr>';
                    return;
                }
                
                // Dibujar las filas de la tabla
                let html = '';
                productos.forEach(p => {
                    const cantidad = parseFloat(p.cantidad) || 0;
                    const precioUnit = parseFloat(p.precio_unitario) || 0;
                    const subtotal = parseFloat(p.subtotal) || (cantidad * precioUnit);
                    
                    html += `
                    <tr data-sku="${p.sku}" data-cantidad="${cantidad}">
                        <td>
                            <div style="font-weight: 500; color: var(--text-primary);">${p.nombre_pieza}</div>
                            <div style="font-size: 12px; color: var(--text-muted); font-family: 'IBM Plex Mono', monospace;">SKU: ${p.sku}</div>
                        </td>
                        <td style="text-align: center; font-family: 'IBM Plex Mono', monospace;">${cantidad}</td>
                        <td style="text-align: right;">
                            ${estado === 'pendiente' 
                                ? `<input type="number" class="input-precio editable-input" style="width: 80px; text-align: right; font-family: 'IBM Plex Mono', monospace;" value="${precioUnit.toFixed(2)}">` 
                                : `<span style="font-family: 'IBM Plex Mono', monospace;">$${precioUnit.toFixed(2)}</span>`}
                        </td>
                        <td style="text-align: right; font-weight: 500; font-family: 'IBM Plex Mono', monospace;" class="celda-subtotal">
                            $${subtotal.toFixed(2)}
                        </td>
                    </tr>`;
                });
                tbody.innerHTML = html;
                
            } else {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color: var(--danger);">Error al procesar los productos.</td></tr>';
            }
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color: var(--danger);">Error de conexión al cargar productos.</td></tr>';
        });
    }

    if (e.target.closest('#modal-cerrar') || e.target.id === 'btn-cerrar-modal') {
        document.getElementById('modal-cotizacion').classList.remove('open');
    }
});
// ==========================================
// 4. ACCIONES AJAX (VIGENCIA Y APROBAR/RECHAZAR)
// ==========================================
function actualizarVigencia() {
    const codigo = document.getElementById('d-codigo').innerText;
    const vigencia = document.getElementById('input-vigencia').value;

    const formData = new FormData();
    formData.append('action', 'actualizar_vigencia');
    formData.append('codigo', codigo);
    formData.append('vigencia', vigencia);

    fetch('/Proyecto/app/controllers/procesar_cotizacion.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            Swal.fire({title:'Guardado', text:'Días de vigencia actualizados.', icon:'success', toast:true, position:'top-end', showConfirmButton:false, timer:3000});
        } else {
            Swal.fire('Error', 'No se pudo guardar la vigencia.', 'error');
        }
    }).catch(err => Swal.fire('Error', 'Problema de conexión.', 'error'));
}

function procesarCotizacion(nuevoEstado) {
    const codigoCotizacion = document.getElementById('d-codigo').innerText.trim();
    const vigencia = document.getElementById('input-vigencia').value;
    
    if(!codigoCotizacion || codigoCotizacion === '-') return;

    const productosModificados = [];
    document.querySelectorAll('#tabla-productos-body tr').forEach(fila => {
        const inputPrecio = fila.querySelector('.input-precio');
        if(inputPrecio) productosModificados.push({ sku: fila.dataset.sku, precio_unitario: inputPrecio.value });
    });

    const formData = new FormData();
    formData.append('action', 'procesar_cotizacion');
    formData.append('id', codigoCotizacion); 
    formData.append('estado', nuevoEstado);
    formData.append('vigencia_dias', vigencia);
    formData.append('productos', JSON.stringify(productosModificados));

    document.getElementById('btn-aprobar').disabled = true;
    document.getElementById('btn-rechazar').disabled = true;

    fetch('/Proyecto/app/controllers/procesar_cotizacion.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            
            // 1. Actualizar modal activo
            const badge = document.getElementById('d-estado');
            badge.innerText = nuevoEstado.toUpperCase();
            badge.className = nuevoEstado === 'aprobada' ? 'badge badge-approved' : 'badge badge-rejected';

            document.getElementById('input-vigencia').disabled = true;
            document.getElementById('btn-confirm-vigencia').style.display = 'none';
            document.getElementById('acciones-iniciales').style.display = 'none';
            document.getElementById('acciones-post').style.display = 'flex';

            // 2. ACTUALIZACIÓN EN TIEMPO REAL (Desaparecer fila de la tabla actual)
            const urlParams = new URLSearchParams(window.location.search);
            const pestañaActual = urlParams.get('estado') || 'pendiente';

            if (pestañaActual === 'pendiente') {
                const botonesEnTabla = document.querySelectorAll(`.btn-ver-detalle[data-codigo="${codigoCotizacion}"]`);
                botonesEnTabla.forEach(btnDOM => {
                    const filaDesktop = btnDOM.closest('tr');
                    if(filaDesktop) filaDesktop.style.display = 'none'; // Desaparece la fila
                    const tarjetaMobile = btnDOM.closest('.mobile-card');
                    if(tarjetaMobile) tarjetaMobile.style.display = 'none'; // Desaparece tarjeta
                });
            }

            Swal.fire({
                title: '¡Actualizado!',
                text: `La cotización ha sido marcada como ${nuevoEstado}.`,
                icon: 'success',
                showConfirmButton: true,
                confirmButtonText: 'OK'
            });
            
        } else {
            Swal.fire('Error', data.message, 'error');
            document.getElementById('btn-aprobar').disabled = false;
            document.getElementById('btn-rechazar').disabled = false;
        }
    }).catch(err => Swal.fire('Error', 'Falla de conexión.', 'error'));
}

// === FUNCIONES NUEVAS: PDF Y ENVÍO ===

// Disparar PDF
document.addEventListener('click', function(e) {
    // Si dan clic al botón PDF o al de Enviar a Cliente
    if (e.target.closest('#btn-pdf') || e.target.closest('.btn-enviar-cliente')) { 
        e.preventDefault(); // Evitamos que haga la acción por defecto inmediatamente
        const codigo = document.getElementById('d-codigo').innerText.trim();

        // 1. Recolectamos los precios de la tabla, justo como lo haces en procesarCotizacion()
        const productosModificados = [];
        document.querySelectorAll('#tabla-productos-body tr').forEach(fila => {
            const inputPrecio = fila.querySelector('.input-precio');
            if(inputPrecio) {
                productosModificados.push({ 
                    sku: fila.dataset.sku, 
                    precio_unitario: inputPrecio.value 
                });
            }
        });

        console.log("Datos a enviar a PHP:", productosModificados);

        // 2. Mandamos a guardar a la Base de Datos
        const formData = new FormData();
        formData.append('action', 'actualizar_precios'); // Necesitas agregar esta acción en tu PHP
        formData.append('id', codigo);
        formData.append('productos', JSON.stringify(productosModificados));

        fetch('/Proyecto/app/controllers/procesar_cotizacion.php', { 
            method: 'POST', 
            body: formData 
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // 3. AHORA SÍ, con la BD actualizada, generamos el PDF
                window.open('/Proyecto/app/services/generar_pdf_cotizacion.php?codigo=' + encodeURIComponent(codigo), '_blank');
            } else {
                Swal.fire('Error', 'No se pudieron actualizar los precios antes de generar el PDF.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Falla de conexión al intentar guardar los precios.', 'error');
        });
    }
});

function toggleCanalesContacto() {
    const cliente = document.getElementById('d-cliente').innerText;
    const preferencia = document.getElementById('d-pref-contacto').innerText;
    
    document.getElementById('envio-nombre-cliente').innerText = cliente;
    
    const textoPreferencia = document.getElementById('envio-preferencia-texto');
    if(textoPreferencia) {
        textoPreferencia.innerHTML = `Preferencia: <strong style="color: var(--accent);">${preferencia}</strong>`;
    }

    const modal = document.getElementById('modal-enviar');
    modal.classList.remove('hidden');
    modal.style.display = 'flex'; // IMPORTANTE: Debe ser flex
}

function cerrarModalEnviar() {
    const modal = document.getElementById('modal-enviar');
    modal.classList.add('hidden');
    modal.style.display = 'none';
}

function abrirWhatsApp() {
    const cliente = document.getElementById('d-cliente').innerText;
    const total = document.getElementById('d-total').innerText;
    const codigo = document.getElementById('d-codigo').innerText.trim();
    
    // Si lograste traer el teléfono del cliente desde la base de datos (recomendado para que sea automático):
    // const telefono = document.getElementById('d-telefono').innerText.replace(/\D/g, ''); 
    
    // Obtenemos la ruta base de tu proyecto automáticamente
    // Obtenemos el origen (ej. http://localhost) y armamos la ruta correcta hacia la vista pública
    const origin = window.location.origin;
    const urlCotizacion = `${origin}/Proyecto/public/admin/ver_cotizacion.php?codigo=${encodeURIComponent(codigo)}`;

    const mensaje = `Hola ${cliente}, tu cotización ${codigo} por un total de ${total} ha sido generada.\n\nPuedes consultar y descargar tu documento oficial en el siguiente enlace:\n${urlCotizacion}\n\nQuedamos a tu disposición.`;
    
    // URL PARA ABRIR CON CONTACTO ESPECÍFICO (Si tienes la variable 'telefono'):
    // window.open(`https://api.whatsapp.com/send?phone=${telefono}&text=${encodeURIComponent(mensaje)}`, '_blank');
    
    // URL ACTUAL (Abre WhatsApp pero te hace buscar el contacto manualmente):
    window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(mensaje)}`, '_blank');
}

function abrirEmail() {
    const email = document.getElementById('d-email').innerText;
    const cliente = document.getElementById('d-cliente').innerText;
    const total = document.getElementById('d-total').innerText;
    const codigo = document.getElementById('d-codigo').innerText.trim();
    
    // Obtenemos la ruta base de tu proyecto automáticamente

    const origin = window.location.origin;
    const urlCotizacion = `${origin}/Proyecto/public/admin/ver_cotizacion.php?codigo=${encodeURIComponent(codigo)}`;

    const subject = `Cotización Equipos de Bombeo - ${codigo}`;
    const body = `Estimado(a) ${cliente},\n\nLe compartimos el enlace para consultar los detalles de su cotización aprobada por un monto total de ${total}.\n\nPuede ver y descargar su documento oficial aquí:\n${urlCotizacion}\n\nSaludos cordiales.`;
    
    // URL para forzar la apertura de Gmail Web en la ventana de redacción
    const urlGmail = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(email)}&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    
    window.open(urlGmail, '_blank');
}