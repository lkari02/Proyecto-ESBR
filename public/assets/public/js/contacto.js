document.addEventListener('DOMContentLoaded', () => {
    const contactForm = document.getElementById('contact-form');

    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault(); // Evita que la página se recargue

            const formData = new FormData(contactForm);
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerText;

            // Estado de carga
            submitBtn.innerText = "Enviando...";
            submitBtn.disabled = true;

            try {
                const response = await fetch('enviar_correo.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    alert("¡Mensaje enviado con éxito!");
                    contactForm.reset(); // Limpia el formulario
                } else {
                    alert("Error: " + result.message);
                }
            } catch (error) {
                console.error("Error en el envío:", error);
                alert("Hubo un problema con el servidor. Inténtalo más tarde.");
            } finally {
                submitBtn.innerText = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    }
});