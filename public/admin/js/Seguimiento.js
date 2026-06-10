document.addEventListener('DOMContentLoaded', () => {
    const botonesVer = document.querySelectorAll('.btn-ver-detalle');
    const modal = document.getElementById('modal-cotizacion');
    const btnCerrar = document.getElementById('modal-cerrar');

    // Elementos del DOM donde inyectaremos los datos
    const dCodigo = document.getElementById('d-codigo');
    const dEstado = document.getElementById('d-estado');
    const dFecha = document.getElementById('d-fecha');
    const dCliente = document.getElementById('d-cliente');
    const dOrganizacion = document.getElementById('d-organizacion');
    const dEmail = document.getElementById('d-email');
    const dTotal = document.getElementById('d-total');
    const tablaCuerpo = document.getElementById('tabla-productos-body');
    const modalLoading = document.getElementById('modal-loading');
    const modalBody = document.getElementById('cotizacion-detalles');

    botonesVer.forEach(boton => {
        boton.addEventListener('click', async function() {
            // 1. Llenar los datos básicos estáticos desde los data-attributes
            const data = this.dataset;
            dCodigo.textContent = data.codigo;
            dFecha.textContent = data.fecha;
            dCliente.textContent = data.cliente;
            dOrganizacion.textContent = data.org || '—';
            dEmail.textContent = data.email || '—';
            dTotal.textContent = '$' + parseFloat(data.total).toFixed(2);
            
            // Colorear el estado
            dEstado.textContent = data.estado.toUpperCase().replace('_', ' ');
            dEstado.className = 'badge ' + obtenerClaseEstado(data.estado);

            // 2. Mostrar modal en estado de carga
            modal.classList.remove('hidden');
            modal.style.display = 'flex'; // Forzar flex si tu CSS lo requiere
            modalBody.classList.add('hidden');
            modalLoading.classList.remove('hidden');
            tablaCuerpo.innerHTML = '';

            // 3. Petición AJAX para obtener las piezas
            const formData = new FormData();
            formData.append('action', 'ver_detalles');
            formData.append('id', data.id);

            try {
                // Hacemos la petición al mismo archivo PHP donde estamos
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const resultado = await response.json();

                if (resultado.status === 'success') {
                    // Dibujamos las filas de la tabla
                    resultado.data.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td style="padding: 12px; border-bottom: 1px solid var(--border);">
                                <span style="display:block; font-size:11px; color:var(--text-muted); font-family:var(--font-mono);">${item.sku}</span>
                                <span style="font-size:14px; color:var(--text-primary);">${item.nombre_pieza}</span>
                            </td>
                            <td style="text-align: center; font-family:var(--font-mono); font-size:13px; padding: 12px; border-bottom: 1px solid var(--border);">${item.cantidad}</td>
                            <td style="text-align: right; font-family:var(--font-mono); font-size:13px; padding: 12px; border-bottom: 1px solid var(--border);">$${parseFloat(item.precio_unitario).toFixed(2)}</td>
                            <td style="text-align: right; font-family:var(--font-mono); font-size:13px; font-weight: 500; padding: 12px; border-bottom: 1px solid var(--border);">$${parseFloat(item.subtotal).toFixed(2)}</td>
                        `;
                        tablaCuerpo.appendChild(tr);
                    });
                }
            } catch (error) {
                console.error("Error al obtener los detalles:", error);
                tablaCuerpo.innerHTML = `<tr><td colspan="4" style="text-align:center; color:red; padding:20px;">Error al cargar los datos.</td></tr>`;
            } finally {
                // Quitar loading y mostrar contenido
                modalLoading.classList.add('hidden');
                modalBody.classList.remove('hidden');
            }
        });
    });

    // Cerrar el modal
    btnCerrar.addEventListener('click', () => {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    });

    // Helper para pintar la insignia
    function obtenerClaseEstado(estado) {
        if (estado === 'confirmada') return 'badge-success';
        if (estado === 'finalizada') return 'badge-approved'; // Usa tu clase azul o la que prefieras
        if (estado === 'no_aprobada') return 'badge-rejected';
        return 'badge-pending';
    }
});