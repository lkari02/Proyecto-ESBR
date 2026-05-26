/**
 * Lógica para el módulo de Clientes
 */

document.addEventListener('DOMContentLoaded', () => {
    const inputBusqueda = document.getElementById('busqueda-clientes');
    const filasClientes = document.querySelectorAll('.cliente-fila');

    if (inputBusqueda) {
        inputBusqueda.addEventListener('input', function(e) {
            const termino = e.target.value.toLowerCase().trim();

            filasClientes.forEach(fila => {
                // Obtenemos el texto de las columnas relevantes (Nombre, Organización, Email)
                const textoFila = fila.innerText.toLowerCase();
                
                // Si el término coincide, mostramos la fila, si no, la ocultamos
                if (textoFila.includes(termino)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });

            // Lógica para mostrar mensaje si no hay resultados
            actualizarMensajeSinResultados(termino);
        });
    }
});

/**
 * Muestra un aviso visual si la búsqueda no arroja nada
 */
function actualizarMensajeSinResultados(termino) {
    const tbody = document.getElementById('tabla-clientes-body');
    const filasVisibles = Array.from(document.querySelectorAll('.cliente-fila')).filter(f => f.style.display !== 'none');
    
    // Eliminamos mensaje previo si existe
    const mensajePrevio = document.getElementById('sin-resultados-msg');
    if (mensajePrevio) mensajePrevio.remove();

    if (filasVisibles.length === 0) {
        const tr = document.createElement('tr');
        tr.id = 'sin-resultados-msg';
        tr.innerHTML = `
            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                No se encontraron clientes que coincidan con "${termino}"
            </td>
        `;
        tbody.appendChild(tr);
    }
}

/**
 * Lógica para el módulo de Clientes - BombaParts
 */

document.addEventListener('DOMContentLoaded', () => {
    // -----------------------------------------------------
    // 1. BUSCADOR MEJORADO (Ignora acentos y mayúsculas)
    // -----------------------------------------------------
    const inputBusqueda = document.getElementById('busqueda-clientes');
    const filasClientes = document.querySelectorAll('.cliente-fila');

    // Función para quitar acentos y facilitar la búsqueda
    const quitarAcentos = (str) => {
        return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    };

    if (inputBusqueda) {
        inputBusqueda.addEventListener('input', function(e) {
            const termino = quitarAcentos(e.target.value.toLowerCase().trim());

            filasClientes.forEach(fila => {
                // Leemos el texto de la fila y le quitamos los acentos
                const textoFila = quitarAcentos(fila.innerText.toLowerCase());
                
                // Mostrar u ocultar dependiendo de si coincide
                fila.style.display = textoFila.includes(termino) ? '' : 'none';
            });

            actualizarMensajeSinResultados(termino);
        });
    }

    function actualizarMensajeSinResultados(termino) {
        const tbody = document.getElementById('tabla-clientes-body');
        if(!tbody) return;

        const filasVisibles = Array.from(filasClientes).filter(f => f.style.display !== 'none');
        
        const mensajePrevio = document.getElementById('sin-resultados-msg');
        if (mensajePrevio) mensajePrevio.remove();

        if (filasVisibles.length === 0) {
            const tr = document.createElement('tr');
            tr.id = 'sin-resultados-msg';
            tr.innerHTML = `
                <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    No se encontraron clientes que coincidan con <b>"${termino}"</b>
                </td>
            `;
            tbody.appendChild(tr);
        }
    }

    // -----------------------------------------------------
    // 2. LÓGICA DEL MODAL DE DETALLES
    // -----------------------------------------------------
    document.addEventListener('click', function(e) {
        // ABRIR MODAL
        const btnVer = e.target.closest('.btn-ver-cliente');
        if (btnVer) {
            // Leer datos del botón
            const tipo = btnVer.dataset.tipo || 'Persona';
            
            // Inyectar al HTML
            document.getElementById('md-nombre').innerText = btnVer.dataset.nombre;
            document.getElementById('md-fecha').innerText = btnVer.dataset.fecha;
            document.getElementById('md-email').innerText = btnVer.dataset.email;
            document.getElementById('md-telefono').innerText = btnVer.dataset.telefono;
            document.getElementById('md-org').innerText = btnVer.dataset.org;
            document.getElementById('md-ubicacion').innerText = btnVer.dataset.ubicacion;
            document.getElementById('md-pais').innerText = btnVer.dataset.pais;

            // Configurar el Badge del Tipo
            const badgeTipo = document.getElementById('md-tipo');
            badgeTipo.innerText = tipo;
            badgeTipo.className = tipo === 'Empresa' ? 'badge badge-approved' : 'badge badge-model';

            // Mostrar Modal
            document.getElementById('modal-cliente').classList.add('open');
        }

        // CERRAR MODAL (Al hacer clic en la X o en el fondo oscuro)
        if (e.target.closest('#btn-cerrar-cliente') || e.target.id === 'modal-cliente') {
            document.getElementById('modal-cliente').classList.remove('open');
        }
    });
});