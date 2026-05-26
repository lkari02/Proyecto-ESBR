    /* ─── TOGGLE PANEL DE DETALLE ─── */
    let openId = null;

    function toggleDetail(id, rowEl) {
        const panel = document.getElementById('detail-' + id);
        if (openId === id) {
            panel.classList.remove('open');
            openId = null;
            return;
        }
        if (openId !== null) {
            document.getElementById('detail-' + openId).classList.remove('open');
        }
        panel.classList.add('open');
        openId = id;
        setTimeout(() => rowEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 50);
    }

    function closeDetail(id, e) {
        e.stopPropagation();
        document.getElementById('detail-' + id).classList.remove('open');
        openId = null;
    }

    /* ─── FILTROS DE CATEGORÍA ─── */
    const pills = document.querySelectorAll('.cat-pill');
    pills.forEach(pill => {
        pill.addEventListener('click', () => {
            pills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            applyFilters();
        });
    });

    /* ─── BÚSQUEDA ─── */
    document.getElementById('cat-search').addEventListener('input', applyFilters);

    function applyFilters() {
        const query  = document.getElementById('cat-search').value.toLowerCase().trim();
        const filter = document.querySelector('.cat-pill.active').dataset.filter;
        const rows   = document.querySelectorAll('.cat-row');
        let visible  = 0;

        rows.forEach(row => {
            const cat    = row.dataset.cat;
            const text   = row.innerText.toLowerCase();
            const matchC = filter === 'todos' || cat === filter;
            const matchQ = !query || text.includes(query);
            const show   = matchC && matchQ;

            row.style.display = show ? '' : 'none';
            /* Cerrar panel si la fila se oculta */
            const panel = document.getElementById('detail-' + row.dataset.id);
            if (!show && panel) panel.classList.remove('open');
            if (show) visible++;
        });

        document.getElementById('cat-empty').style.display = visible === 0 ? 'block' : 'none';
    }

document.addEventListener('DOMContentLoaded', () => {
    // 1. Capturamos los elementos del DOM
    const searchInput = document.getElementById('cat-search');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const products = document.querySelectorAll('.cat-row');
    
    // 2. Función principal que evalúa qué mostrar y qué ocultar
    function applyFilters() {
        // Obtenemos lo que el usuario escribió (en minúsculas)
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        
        // Verificamos qué botón de categoría está activo
        const activeFilterBtn = document.querySelector('.filter-btn.active');
        const filterCategory = activeFilterBtn ? activeFilterBtn.getAttribute('data-filter') : 'ALL';

        // Recorremos cada producto de la lista
        products.forEach(product => {
            // Extraemos el texto del SKU y del Título del producto actual
            const sku = product.querySelector('.cat-row-sku').innerText.toLowerCase();
            const title = product.querySelector('.cat-row-title').innerText.toLowerCase();
            
            // ¿Coincide con la búsqueda de texto?
            const matchesSearch = sku.includes(searchTerm) || title.includes(searchTerm);
            
            // ¿Coincide con la categoría de los botones?
            let matchesCategory = false;
            
            if (filterCategory === 'ALL') {
                matchesCategory = true;
            } else if (filterCategory === 'BOM' && sku.includes('bom-')) {
                // Si la categoría es BOM, validamos que el SKU tenga 'bom-'
                matchesCategory = true;
            } else if (filterCategory === 'REF' && sku.includes('ref-')) {
                // Si la categoría es REF, validamos que el SKU tenga 'ref-'
                matchesCategory = true;
            }

            // Si pasa ambas validaciones, mostramos la fila. Si no, la ocultamos.
            if (matchesSearch && matchesCategory) {
                product.style.display = 'flex';
            } else {
                product.style.display = 'none';
            }
        });
    }

    // 3. Activamos el evento para cuando el usuario escribe en el buscador
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    // 4. Activamos los eventos para cuando el usuario hace clic en los botones
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Quitamos la clase 'active' de todos los botones
            filterBtns.forEach(b => b.classList.remove('active'));
            // Se la ponemos solo al botón que recibió el clic
            btn.classList.add('active');
            // Volvemos a ejecutar el filtro
            applyFilters();
        });
    });
});