document.addEventListener('DOMContentLoaded', () => {
    
    // --- 2. Menú Móvil & Idioma ---
    const setupMobileMenu = () => {
        const navToggle = document.getElementById('nav-toggle');
        const navMenu = document.getElementById('nav-menu');
        if (navToggle && navMenu) {
            navToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
                navToggle.classList.toggle('active');
            });
        }
    };

    const setupLanguagePicker = () => {
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
    };

    // --- 3. Datos (Optimizado con Clases Profesionales) ---
    const MOCK_API_DATA = {
        about: {
            heroImageUrl: 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=1920',
            products: [
                { name: 'Bombas Centrífugas', img: 'uploads/imagen1.jpeg', desc: 'Ideales para mover grandes volúmenes de agua a altas presiones.' },
                { name: 'Bombas Sumergibles', img: 'uploads/imagen2.jpeg', desc: 'Diseñadas para operar bajo el agua, perfectas para pozos.' },
                { name: 'Fuelles de Engranajes', img: 'uploads/imagen3.jpg', desc: 'Precisión y durabilidad para aplicaciones industriales.' },
                { name: 'Bombas de Diafragma', img: 'uploads/68cf8e55f2280-Eje.jpg', desc: 'Versátiles para manejar una amplia variedad de fluidos.' },
                { name: 'Bombas Peristálticas', img: 'uploads/68cf8e8a7c3d2-rodamiento.jpg', desc: 'Para dosificación precisa sin contaminación.' },
                { name: 'Bombas de Pistón', img: 'admin/uploads/68cf8e18dd340-SelloMecanico.jpg', desc: 'Alta presión para limpieza y aplicaciones industriales.' }
            ]
        }
    };

    // --- 4. Carga de Datos Dinámicos ---

    const loadHomePageData = () => {
        const gallery = document.getElementById('about-gallery');
        if (gallery) {
            gallery.innerHTML = '';
            MOCK_API_DATA.home.aboutUsGallery.forEach(imageUrl => {
                // Usamos la clase gallery-item para asegurar que los 4 sean iguales
                gallery.innerHTML += `
                    <div class="gallery-item">
                        <img src="${imageUrl}" alt="Refacción BombaParts">
                    </div>`;
            });
        }
    };

    const loadAboutPageData = () => {
        const productGrid = document.getElementById('product-grid');
        if (productGrid) {
            productGrid.innerHTML = '';
            MOCK_API_DATA.about.products.forEach(product => {
                // Usamos la clase feature-card para que coincida con el estilo de Misión/Visión
                productGrid.innerHTML += `
                    <article class="feature-card">
                        <div class="gallery-item" style="border:none; box-shadow:none; aspect-ratio:16/9; height:auto;">
                            <img src="${product.img}" alt="${product.name}" style="padding:0; object-fit:cover;">
                        </div>
                        <h3 style="margin-top:1rem;">${product.name}</h3>
                        <p style="font-size:0.9rem; color:var(--gray-600);">${product.desc}</p>
                    </article>
                `;
            });
        }
    };

    if (document.getElementById('hero-section')) {
        loadHomePageData();
    } else if (document.getElementById('product-grid')) {
        loadAboutPageData();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const menuBtn = document.getElementById('menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuBtn && navMenu) {
        const icon = menuBtn.querySelector('.material-icons-outlined');
        
        menuBtn.addEventListener('click', () => {
            // Activa o desactiva la clase que muestra el menú en CSS
            navMenu.classList.toggle('active');
            
            // Cambia el ícono entre Hamburguesa (menu) y Cerrar (close)
            if (navMenu.classList.contains('active')) {
                icon.textContent = 'close';
            } else {
                icon.textContent = 'menu';
            }
        });
    }
});