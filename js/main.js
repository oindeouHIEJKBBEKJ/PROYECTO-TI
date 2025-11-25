// Funciones generales del sistema
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Manejo de formularios
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Confirmación para acciones importantes
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres eliminar este elemento?')) {
                e.preventDefault();
            }
        });
    });
});

// Funciones para el semáforo de ofertas
function updateOfferStatus(offerId, status) {
    // Lógica para actualizar el estado de una oferta
    console.log(`Actualizando oferta ${offerId} a estado: ${status}`);
}

// Funciones para validaciones específicas
function validateMatricula(matricula) {
    const matriculaRegex = /^[A-Z0-9]{8,12}$/;
    return matriculaRegex.test(matricula);
}

function validateCURP(curp) {
    const curpRegex = /^[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9A-Z]{2}$/;
    return curpRegex.test(curp);
}