document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. Menú Móvil ---
    const menuBtn = document.getElementById('menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuBtn && navMenu) {
        const icon = menuBtn.querySelector('.material-icons-outlined');
        menuBtn.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            if (navMenu.classList.contains('active')) {
                icon.textContent = 'close';
            } else {
                icon.textContent = 'menu';
            }
        });
    }

    // --- 2. Selector de Idioma ---
    const langPicker = document.getElementById("language-picker");
    if (langPicker) {
        langPicker.addEventListener("change", (e) => {
            if (typeof applyLanguage === 'function') {
                const selectedLang = e.target.value;
                localStorage.setItem("userLanguage", selectedLang);
                applyLanguage(selectedLang);
            }
        });
    }

    // --- 3. Datos Dinámicos (MOCK API) ---
    const MOCK_API_DATA = {
        about: {
            categories: [
                {
                    title: "Bombas horizontales centrifugas multipasos.",
                    i18nKey: "cat_bombas_horizontales",
                    products: [
                        { name: 'SWP 150', img: 'assets/img/SWP-150_3-Photoroom.png', i18nKey: "prod_swp"}, 
                        { name: 'DNC', img: 'assets/img/DNC-Photoroom.png', i18nKey: "prod_dnc" },
                        { name: 'SWK 130', img: 'assets/img/SWK-130_2-Photoroom.png', i18nKey: "prod_swk" }
                    ]
                },
                {
                    title: "Bomba de tazones, sumergible para pozo.",
                    i18nKey: "cat_bombas_tazones",
                    products: [
                        { name: 'Vertical 3', img: 'assets/img/Vertical_3-Photoroom.png', i18nKey: "prod_vertical" },
                        { name: 'Tazon 1', img: 'assets/img/Pieza-Photoroom.png', i18nKey: "prod_tazon1" },
                        { name: 'Tazon 2', img: 'assets/img/Pieza2-Photoroom.png', i18nKey: "prod_tazon2" }
                    ]
                },
                {
                    title: "Refacciones para bombas industriales de agua, en materiales, hierro, gris, acero inoxidable, bronce estándar, bronce SAE 62.",
                    i18nKey: "cat_refacciones",
                    products: [
                        { name: 'DISCO DE EQUILIBRIO', img: 'assets/img/Disco_de-equilibrio.png', i18nKey: "prod_disco" },
                        { name: 'IMPULSOR', img: 'assets/img/Impulsor_3-Photoroom.png', i18nKey: "prod_impulsor" },
                        { name: 'RETEN', img: 'assets/img/Reten.jpg', i18nKey: "prod_reten" }
                    ]
                }
            ]
        },
        home: {
            aboutUsGallery: [] // Previene errores si estás en la página de inicio
        }
    };

    // --- 4. Funciones de Renderizado ---
    
    // Función para la página "Nosotros" (Catálogo agrupado)
    const renderAboutProducts = () => {
        const mainContainer = document.getElementById('product-grid');
        if (!mainContainer) return;

        mainContainer.innerHTML = ''; // Limpiamos contenido previo
        mainContainer.className = 'categories-container'; // Aplicamos clase CSS

        MOCK_API_DATA.about.categories.forEach(category => {
            const section = document.createElement('div');
            section.className = 'category-section';

            // 2. Agregamos el título de la categoría (MODIFICADO)
        const title = document.createElement('h3');
        title.className = 'category-title';
        title.setAttribute('data-i18n', category.i18nKey); // <-- Le pone la etiqueta para traducir
        title.textContent = category.title;
        section.appendChild(title);

        // 3. Creamos el grid para las tarjetas
        const grid = document.createElement('div');
        grid.className = 'product-grid'; 

        // 4. Llenamos el grid con las tarjetas blancas
        category.products.forEach(product => {
            const productCard = document.createElement('div');
            productCard.className = 'product-item';

            // AQUÍ ESTÁ LA CLAVE: Revisa que el h3 tenga el data-i18n
            productCard.innerHTML = `
                <div class="product-img-wrapper">
                    <img src="${product.img}" alt="${product.name}">
                </div>
                <h3 class="product-title" data-i18n="${product.i18nKey}">${product.name}</h3>
            `;

            grid.appendChild(productCard);
        });

            section.appendChild(grid);
            mainContainer.appendChild(section);
        });

            // Forzar la traducción de los nuevos elementos inyectados
        if (typeof applyLanguage === 'function') {
            const currentLang = localStorage.getItem("userLanguage") || "es";
            applyLanguage(currentLang);
        }
    };

    // Función para la página de Inicio (Si es que la usas)
    const loadHomePageData = () => {
        const gallery = document.getElementById('about-gallery');
        if (gallery && MOCK_API_DATA.home && MOCK_API_DATA.home.aboutUsGallery.length > 0) {
            gallery.innerHTML = '';
            MOCK_API_DATA.home.aboutUsGallery.forEach(imageUrl => {
                gallery.innerHTML += `
                    <div class="gallery-item">
                        <img src="${imageUrl}" alt="Refacción BombaParts">
                    </div>`;
            });
        }
    };

    // --- 5. Inicialización (Ejecutar las funciones según la página) ---
    
    // Si estamos en el index.html
    if (document.getElementById('hero-section') || document.getElementById('about-gallery')) {
        loadHomePageData();
    } 
    
    // Si estamos en nosotros.html (buscamos si existe el grid de productos)
    if (document.getElementById('product-grid')) {
        renderAboutProducts();
    }
});