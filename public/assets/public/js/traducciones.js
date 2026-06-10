// ==========================================
// 1. DICCIONARIOS DE TRADUCCIÓN
// ==========================================
const translations = {
  en: {

  // NAVEGACIÓN Y FOOTER (Global)
    "nav_home": "Home",
    "nav_about": "About Us",
    "nav_catalog": "Catalog",
    "nav_contact": "Contact",
    "nav_faq": "FAQ",
    "nav_privacidad": "Privacy Policy",
    
    "footer_about_desc": "Your reliable partner for quality water pump parts.",
    "footer_nav_title": "Navigation",
    "footer_contact_title": "Contact",
    "footer_address": "Ecatepec de Morelos, State of Mexico",
    "footer_copyright": "© 2026 Equipos de Bombeo. All rights reserved.",

    // Inicio
    "page_title": "Pumping Equipment, Service and Spare Parts",
    "hero_title": "QUALITY FOR THE INDUSTRY",
    "hero_desc": "Your reliable partner for pumping solutions. Explore our catalog and request a quote today.",
    "btn_explore": "Explore Catalog",
    "btn_about": "Who We Are",
    "stat_years": "Years of experience",
    "stat_parts": "Available spare parts",
    "stat_quality": "Custom Manufacturing",
    "feat_1_title": "Extensive Catalog",
    "feat_1_desc": "Find the exact part you need for any type of water pump.",
    "feat_2_title": "Easy Quoting",
    "feat_2_desc": "Intuitive process to select products and request your quote.",
    "feat_3_title": "Custom Manufacturing",
    "feat_3_desc": "We manufacture parts according to your blueprints, specifications, and choice of material, adapting to your equipment's exact needs.",
    "about_title": "Who We Are",
    "about_desc": "At Pumping Equipment, Service and Spare Parts, we are dedicated to manufacturing and supplying spare parts adapted to the specifications, blueprints, and materials each client requires, providing exact solutions for their equipment...",
    "value_experience": "Proven experience",
    "value_service": "Specialized technical service",
    "value_catalog": "Extensive and updated catalog",
    "value_delivery": "On-time deliveries",

    // Nosotros
    "about_page_title": "Our History- Pumping Equipment",
    "about_eyebrow": "History and Trust", 
    "about_hero_title": "Meet Pumping Equipment",
    "about_hero_desc": "We were created to satisfy and organize pumping solutions in decision-making, with top quality equipment and worldwide excellence.",
    "mvv_eyebrow": "Fundamentals",
    "mvv_title": "Mission, Vision, and Values",
    "mission_title": "Our Mission",
    "mission_desc": "To manufacture and provide exact spare parts according to each client's blueprints, materials, and needs, ensuring precision machining and efficient response times.",
    "vision_title": "Our Vision",
    "vision_desc": "To consolidate ourselves as the trusted workshop and supplier in the industrial sector, recognized for our adaptability, manufacturing precision, and custom technical solutions.",
    "values_title": "Our Values",
    "values_desc": "Precision, Honesty, Customer Commitment, Flexibility, and Responsibility. These pillars guide the way we work and adapt to each project.",
    "prod_eyebrow": "Specialization",
    "prod_types_title": "Product Types",
    "prod_types_desc": "Explore our wide range of solutions tailored to every technical need.",

    // Contacto
    "contact_page_title": "Contact",
    "contact_eyebrow": "Personalized Attention",
    "contact_hero_title": "Contact Us",
    "contact_hero_desc": "Send us a message and we will get back to you as soon as possible.",
    "form_label_name": "Your Name",
    "form_label_email": "Your Email",
    "form_label_subject": "Subject",
    "form_label_message": "Your Message",
    "form_btn_send": "Send Message",
    "info_title": "Contact Information",
    "info_address": "Ecatepec de Morelos, State of Mexico",

    // Catálogo
    "cat_page_title": "Spare Parts Catalog",
    "cat_eyebrow": "Industrial Precision",
    "cat_header_title": "Our Parts Catalog",
    "cat_search_placeholder": "Search technical part...",
    "cat_btn_quote": "Quote",
    "cat_filter_all": "All",
    "cat_filter_bom": "Water Pumps",
    "cat_filter_ref": "Spare Parts",

    // COTIZACIÓN

    "quote_page_title": "Quote Request",
    "quote_return_btn": "<span class=\"material-icons-outlined\">arrow_back</span> Back to Catalog",
    "quote_summary_title": "1. Spare Parts Summary",
    "quote_total_articles": "Total Articles:",
    "quote_form_title": "2. Contact and Shipping Information",
    
    // Formulario de Cotización
    "quote_label_name": "Full Name *",
    "quote_ph_name": "Your name",
    "quote_label_email": "Email Address *",
    "quote_ph_email": "email@company.com",
    "quote_label_phone": "Phone / WhatsApp *",
    "quote_label_client_type": "Client Type *",
    "quote_opt_select": "-- Select --",
    "quote_opt_person": "Individual (End Customer)",
    "quote_opt_company": "Company / Distributor",
    "quote_label_org": "Company or Institution Name *",
    "quote_ph_org": "Business Name",
    "quote_label_country": "Country *",
    "quote_label_city": "City / State *",
    "quote_ph_city": "E.g., Tlaxcala",
    "quote_label_message": "Additional Message (Optional)",
    "quote_ph_message": "Additional technical specifications...",
    "quote_label_contact_pref": "Preferred contact method:",
    "quote_radio_email": "Email",
    "quote_radio_whatsapp": "WhatsApp",
    "quote_privacy_notice": "I have read and accept the <a href='aviso-de-privacidad.html' target='_blank' style='color: var(--blue-main); text-decoration: underline; font-weight: bold;'>Privacy Policy</a>.",
    "quote_btn_submit": "Request Quote",

    // Modal de Confirmación
    "quote_modal_confirm_title": "Confirm Request",
    "quote_modal_confirm_desc": "Are you sure you want to send this quote request with the entered parts and details?",
    "quote_btn_review": "Review again",
    "quote_btn_yes_send": "Yes, Send",

    // Modal de Detalles Premium
    "quote_success_title": "Request Successfully Registered",
    "quote_detail_client_title": "Client Details",
    "quote_detail_folio": "Tracking Folio:",
    "quote_detail_name": "Full Name:",
    "quote_detail_email": "Email:",
    "quote_detail_phone": "Phone:",
    "quote_detail_location": "Location:",
    "quote_detail_type": "Type:",
    "quote_detail_org": "Company / Institution:",
    "quote_detail_pref": "Contact Preference:",
    "quote_detail_notes": "Notes / Requirements:",
    "quote_detail_products_title": "Quoted Parts",
    "quote_success_footer_text": "Our team will send the formal quote to your preferred contact.",
    "quote_btn_close": "Close",
    "quote_btn_download_pdf": "<span class=\"material-icons-outlined\">download</span> Download PDF",

    // PREGUNTAS FRECUENTES (FAQ)
    "faq_page_title": "Frequently Asked Questions",
    "faq_eyebrow": "Help Center",
    "faq_header_title": "Frequently Asked Questions",
    
    "faq_q1": "How can I request a quote?",
    "faq_a1": "You can request a quote by navigating to our 'Catalog', selecting the parts you need, and adding them to your quote cart. Then, fill out the form and our team will contact you.",
    "faq_q2": "What types of parts do you offer?",
    "faq_a2": "Our specialty is **custom manufacturing**. Although we have a limited variety of standard parts in stock, our core business is creating spare parts based on the exact specifications you provide.",
    "faq_q3": "Do you offer international shipping?",
    "faq_a3": "Yes, we ship internationally. Shipping costs and times may vary depending on the destination. Please contact us for specific details regarding your location.",
    "faq_q4": "How can I contact technical support?",
    "faq_a4": "You can contact us through the 'Contact' form, via email at info@bombaparts.com, or by using the WhatsApp button in the bottom corner of the screen.",
    "faq_q5": "Can I return a part if it is not compatible?",
    "faq_a5": "Due to the technical nature of our products, we do not accept returns, exchanges or refunds, except in the case of manufacturing defects or errors attributable to the company.",

    // =====================================
    // AVISO DE PRIVACIDAD
    // =====================================
    "privacy_page_title": "Privacy Notice",
    "privacy_eyebrow": "Legal & Transparency",
    "privacy_title": "Privacy and Security Notice",
    "privacy_p1": "At <strong>Pumping Equipment, Service and Spare Parts</strong>, we are committed to the protection and proper handling of your personal data.",
    "privacy_h_data": "1. Data we collect",
    "privacy_p_data": "Through our contact and quote request forms, we collect information such as: full name, email, phone/WhatsApp number, location (city/country), and the name of your company or institution.",
    "privacy_h_purpose": "2. Purpose of data processing",
    "privacy_p_purpose": "The collected information is used exclusively to: process and send spare part quotes, coordinate custom manufacturing, provide technical follow-up, manage the shipping of parts, and maintain communication regarding your requirements.",
    "privacy_h_security": "3. Security Policies",
    "privacy_p_security": "We implement administrative and technical security measures to protect your personal data against damage, loss, alteration, or unauthorized access. Your information will not be sold, rented, or shared with third parties for advertising purposes outside our company. Location information will only be shared with shipping providers when necessary for the delivery of your parts.",
    "privacy_h_arco": "4. ARCO Rights",
    "privacy_p_arco": "You have the right to Access, Rectify, Cancel, or Oppose the processing of your personal data. To exercise these rights, you can send a formal request to our email: <strong>ventas@equiposbombeo.com.mx</strong>.",
    "privacy_h_updates": "5. Updates",
    "privacy_p_updates": "We reserve the right to make changes to this privacy notice to comply with legislative updates or internal policies. Any modifications will be available on this website.",
    "privacy_last_update": "<em>Last update: May 2026.</em>",
  },
  
  es: {
    // ------------------------------------
  // NAVEGACIÓN Y FOOTER (Global)
  // ------------------------------------
    "nav_home": "Inicio",
    "nav_about": "Nosotros",
    "nav_catalog": "Catálogo",
    "nav_contact": "Contacto",
    "nav_faq": "Preguntas Frecuentes",
    "nav_privacidad": "Aviso de Privacidad",
    
    "footer_about_desc": "Su socio confiable para piezas de bomba de agua de calidad.",
    "footer_nav_title": "Navegación",
    "footer_contact_title": "Contacto",
    "footer_address": "Ecatepec de Morelos, Estado de México",
    "footer_copyright": "© 2026 Equipos de Bombeo. Todos los derechos reservados.",

    // Inicio
    "page_title": "Equipos de Bombeo, Servicio y Refacciones",
    "hero_title": "Calidad a la Industria",
    "hero_desc": "Su socio confiable para soluciones de bombeo. Explore nuestro catálogo y solicite una cotización hoy mismo.",
    "btn_explore": "Explorar Catálogo",
    "btn_about": "Quiénes Somos",
    "stat_years": "Años de experiencia",
    "stat_parts": "Refacciones disponibles",
    "stat_quality": "Fabricación a medida",
    "feat_1_title": "Amplio Catálogo",
    "feat_1_desc": "Encuentre la pieza exacta que necesita para cualquier tipo de bomba de agua.",
    "feat_2_title": "Cotización Sencilla",
    "feat_2_desc": "Proceso intuitivo para seleccionar productos y solicitar su cotización.",
    "feat_3_title": "Fabricación a Medida",
    "feat_3_desc": "Fabricamos piezas según sus planos, especificaciones y elección de material, adaptándonos a las exigencias exactas de su equipo.",
    "about_title": "Quiénes Somos",
    "about_desc": "En Equipos de Bombeo, Servicio y Refacciones, nos dedicamos a fabricar y suministrar refacciones adaptadas a las especificaciones, planos y materiales que cada cliente requiere, brindando soluciones exactas para sus equipos...",
    "value_experience": "Experiencia comprobada",
    "value_service": "Servicio técnico especializado",
    "value_catalog": "Catálogo amplio y actualizado",
    "value_delivery": "Entregas a tiempo",

    // Nosotros
    "about_page_title": "Nuestra Historia",
    "about_eyebrow": "Trayectoria y Confianza", 
    "about_hero_title": "Conozca Equipos de Bombeo:",
    "about_hero_desc": "Fuimos creados con el fin de satisfacer y organizar soluciones de bombeo en la toma de decisiones, con equipos de primera calidad y excelencia mundial.",
    "mvv_eyebrow": "Fundamentos",
    "mvv_title": "Misión, Visión y Valores",
    "mission_title": "Nuestra Misión",
    "mission_desc": "Fabricar y proveer refacciones exactas según los planos, materiales y necesidades de cada cliente, garantizando un maquinado de precisión y tiempos de respuesta eficientes.",
    "vision_title": "Nuestra Visión",
    "vision_desc": "Consolidarnos como el taller y proveedor de confianza en el sector industrial, reconocidos por nuestra capacidad de adaptación, precisión en la fabricación y soluciones técnicas a medida.",
    "values_title": "Nuestros Valores",
    "values_desc": "Precisión, Honestidad, Compromiso con el Cliente, Flexibilidad y Responsabilidad. Estos pilares guían nuestra forma de trabajar y de adaptarnos a cada proyecto.",
    "prod_eyebrow": "Especialización",
    "prod_types_title": "Tipos de Productos",
    "prod_types_desc": "Explora nuestra amplia gama de soluciones adaptadas a cada necesidad técnica.",

    // Contacto
    "contact_page_title": "Contacto",
    "contact_eyebrow": "Atención Personalizada",
    "contact_hero_title": "Contáctanos",
    "contact_hero_desc": "Envíanos un mensaje y nos pondremos en contacto contigo lo antes posible.",
    "form_label_name": "Tu Nombre",
    "form_label_email": "Tu Correo Electrónico",
    "form_label_subject": "Asunto",
    "form_label_message": "Tu Mensaje",
    "form_btn_send": "Enviar Mensaje",
    "info_title": "Información de Contacto",
    "info_address": "Ecatepec de Morelos, Estado de Mexico",

    // Catálogo
    "cat_page_title": "Catálogo de Refacciones",
    "cat_eyebrow": "Precisión Industrial",
    "cat_header_title": "Nuestro Catálogo de Piezas",
    "cat_search_placeholder": "Buscar pieza técnica...",
    "cat_btn_quote": "Cotización",
    "cat_filter_all": "Todos",
    "cat_filter_bom": "Bombas de Agua",
    "cat_filter_ref": "Refacciones",

    // COTIZACIÓN
    // =====================================
    "quote_page_title": "Solicitud de Cotización",
    "quote_return_btn": "<span class=\"material-icons-outlined\">arrow_back</span> Volver al Catálogo",
    "quote_summary_title": "1. Resumen de Refacciones",
    "quote_total_articles": "Total de Artículos:",
    "quote_form_title": "2. Datos de Contacto y Envío",
    
    // Formulario de Cotización
    "quote_label_name": "Nombre Completo *",
    "quote_ph_name": "Su nombre",
    "quote_label_email": "Correo Electrónico *",
    "quote_ph_email": "correo@empresa.com",
    "quote_label_phone": "Teléfono / WhatsApp *",
    "quote_label_client_type": "Tipo de cliente *",
    "quote_opt_select": "-- Seleccione --",
    "quote_opt_person": "Persona Física (Cliente Final)",
    "quote_opt_company": "Empresa / Distribuidor",
    "quote_label_org": "Nombre de la Empresa o Institución *",
    "quote_ph_org": "Razón Social",
    "quote_label_country": "País *",
    "quote_label_city": "Ciudad / Estado *",
    "quote_ph_city": "Ej: Tlaxcala",
    "quote_label_message": "Mensaje Adicional (Opcional)",
    "quote_ph_message": "Especificaciones técnicas adicionales...",
    "quote_label_contact_pref": "Vía de contacto preferida:",
    "quote_radio_email": "Correo",
    "quote_radio_whatsapp": "WhatsApp",
    "quote_privacy_notice": "He leído y acepto el <a href='aviso-de-privacidad.html' target='_blank' style='color: var(--blue-main); text-decoration: underline; font-weight: bold;'>Aviso de Privacidad</a>.",
    "quote_btn_submit": "Solicitar Cotización",

    // Modal de Confirmación
    "quote_modal_confirm_title": "Confirmar Solicitud",
    "quote_modal_confirm_desc": "¿Estás seguro de que deseas enviar esta solicitud de cotización con las refacciones y datos ingresados?",
    "quote_btn_review": "Revisar de nuevo",
    "quote_btn_yes_send": "Sí, Enviar",

    // Modal de Detalles Premium
    "quote_success_title": "Solicitud Registrada con Éxito",
    "quote_detail_client_title": "Datos del Cliente",
    "quote_detail_folio": "Folio de Seguimiento:",
    "quote_detail_name": "Nombre Completo:",
    "quote_detail_email": "Correo:",
    "quote_detail_phone": "Teléfono:",
    "quote_detail_location": "Ubicación:",
    "quote_detail_type": "Tipo:",
    "quote_detail_org": "Empresa / Institución:",
    "quote_detail_pref": "Preferencia Contacto:",
    "quote_detail_notes": "Notas / Requerimientos:",
    "quote_detail_products_title": "Refacciones Cotizadas",
    "quote_success_footer_text": "Nuestro equipo enviará la cotización formal a su contacto de preferencia.",
    "quote_btn_close": "Cerrar",
    "quote_btn_download_pdf": "<span class=\"material-icons-outlined\">download</span> Descargar PDF",

    // =====================================
    // PREGUNTAS FRECUENTES (FAQ)
    // =====================================
    "faq_page_title": "Preguntas Frecuentes",
    "faq_eyebrow": "Centro de Ayuda",
    "faq_header_title": "Preguntas Frecuentes",
    
    "faq_q1": "¿Cómo puedo solicitar una cotización?",
    "faq_a1": "Puede solicitar una cotización navegando a nuestro 'Catálogo', seleccionando las piezas que necesita y añadiéndolas a su carrito de cotización. Luego, complete el formulario y nuestro equipo se pondrá en contacto con usted.",
    "faq_q2": "¿Qué tipos de piezas ofrecen?",
    "faq_a2": "Nuestra especialidad es la **fabricación a la medida**. Aunque contamos con una variedad limitada de piezas estándar en stock, nuestro núcleo es crear refacciones basadas en las especificaciones exactas que usted nos proporcione.",
    "faq_q3": "¿Realizan envíos internacionales?",
    "faq_a3": "Sí, realizamos envíos a nivel internacional. Los costos y tiempos de envío pueden variar según el destino. Por favor, contáctenos para obtener detalles específicos sobre su ubicación.",
    "faq_q4": "¿Cómo puedo contactar al soporte técnico?",
    "faq_a4": "Puede contactarnos a través del formulario de 'Contacto', vía email a info@bombaparts.com, o utilizando el botón de WhatsApp en la esquina inferior de la pantalla.",
    "faq_q5": "¿Puedo devolver una pieza si no es compatible?",
    "faq_a5": "Debido a la naturaleza técnica de nuestros productos, ni se aceptan devoluciones, cambios no reembolsos, salvo en caso de defectos de fabricación o errores atribuibles a la empresa.",

    // =====================================
    // AVISO DE PRIVACIDAD
    // =====================================
    "privacy_page_title": "Aviso de Privacidad",
    "privacy_eyebrow": "Legal y Transparencia",
    "privacy_title": "Aviso de Privacidad y Seguridad",
    "privacy_p1": "En <strong>Equipos de Bombeo, Servicio y Refacciones</strong>, estamos comprometidos con la protección y el manejo adecuado de sus datos personales.",
    "privacy_h_data": "1. Datos que recopilamos",
    "privacy_p_data": "A través de nuestros formularios de contacto y solicitud de cotización, recopilamos información como: nombre completo, correo electrónico, número de teléfono/WhatsApp, ubicación (ciudad/país) y nombre de su empresa o institución.",
    "privacy_h_purpose": "2. Finalidad del tratamiento de datos",
    "privacy_p_purpose": "La información recopilada se utiliza exclusivamente para: procesar y enviar cotizaciones de refacciones, coordinar la fabricación a medida, brindar seguimiento técnico, gestionar el envío de piezas y mantener comunicación sobre sus requerimientos.",
    "privacy_h_security": "3. Políticas de Seguridad",
    "privacy_p_security": "Implementamos medidas de seguridad administrativas y técnicas para proteger sus datos personales contra daño, pérdida, alteración o acceso no autorizado. Su información no será vendida, alquilada ni compartida con terceros para fines publicitarios ajenos a nuestra empresa. Únicamente se compartirá información de ubicación con proveedores de paquetería cuando sea necesario para la entrega de sus refacciones.",
    "privacy_h_arco": "4. Derechos ARCO",
    "privacy_p_arco": "Usted tiene derecho a Acceder, Rectificar, Cancelar u Oponerse al tratamiento de sus datos personales. Para ejercer estos derechos, puede enviar una solicitud formal a nuestro correo electrónico: <strong>ventas@equiposbombeo.com.mx</strong>.",
    "privacy_h_updates": "5. Actualizaciones",
    "privacy_p_updates": "Nos reservamos el derecho de efectuar modificaciones a este aviso de privacidad para la atención de novedades legislativas o políticas internas. Cualquier modificación estará disponible en esta misma página web.",
    "privacy_last_update": "<em>Última actualización: Mayo de 2026.</em>",
  }
};

// ==========================================
// 2. LÓGICA DE TRADUCCIÓN Y DOM
// ==========================================

/**
 * Aplica el idioma seleccionado a toda la página
 * @param {string} lang - 'es' o 'en'
 */
function setLanguage(lang) {
    // 1. Guardar preferencia en el navegador para que persista entre páginas
    localStorage.setItem('selectedLanguage', lang);
    
    // 2. Cambiar atributo lang del HTML para accesibilidad y SEO
    document.documentElement.lang = lang;
    
    // 3. Sincronizar el selector visual si existe en la página actual
    const picker = document.getElementById('language-picker');
    if (picker && picker.value !== lang) {
        picker.value = lang;
    }

    // 4. Buscar todos los elementos que tengan el atributo data-i18n
    const elements = document.querySelectorAll('[data-i18n]');
    
    elements.forEach(el => {
        const key = el.getAttribute('data-i18n');
        
        // Si la clave existe en el diccionario seleccionado
        if (translations[lang] && translations[lang][key]) {
            const translatedText = translations[lang][key];

            // Manejo dinámico dependiendo de qué tipo de etiqueta es:
            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                // Si es un input o textarea de búsqueda/formulario, cambiamos el placeholder
                el.placeholder = translatedText;
            } else if (el.tagName === 'TITLE') {
                // Si es el título de la pestaña del navegador
                document.title = translatedText;
            } else {
                // Para el resto (p, h1, h2, span, a, li, etc.) usamos innerHTML
                // Esto es crucial para que etiquetas HTML como <em> no se rompan
                el.innerHTML = translatedText;
            }
        }
    });
}

// ==========================================
// 3. INICIALIZACIÓN AL CARGAR LA PÁGINA
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    // 1. Revisar si ya hay un idioma guardado en la memoria del navegador
    let savedLang = localStorage.getItem('selectedLanguage');
    
    // 2. Si NO hay un idioma guardado (es su primera visita)
    if (!savedLang) {
        // Detectamos el idioma del dispositivo/navegador (ej. 'es-MX', 'en-US')
        // Usamos slice(0, 2) para quedarnos solo con las dos primeras letras ('es', 'en')
        const browserLang = navigator.language.slice(0, 2).toLowerCase();
        
        // Si el navegador está en inglés, asignamos 'en', de lo contrario 'es'
        if (browserLang === 'en') {
            savedLang = 'en';
        } else {
            savedLang = 'es'; // Español como respaldo para cualquier otro idioma
        }
    }
    
    // 3. Aplicar la traducción inicial
    setLanguage(savedLang);

    // 4. Agregar el escuchador de eventos al selector para que cambie en vivo
    const picker = document.getElementById('language-picker');
    if (picker) {
        picker.addEventListener('change', (event) => {
            setLanguage(event.target.value);
        });
    }
});