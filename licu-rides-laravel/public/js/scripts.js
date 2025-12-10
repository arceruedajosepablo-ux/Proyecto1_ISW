// Funciones para manejar el formato de cédula costarricense
// Cambia el formato según sea nacional o extranjera
function cambiarFormatoCedula() {
    const tipoCedula = document.getElementById("tipo-cedula").value;
    const cedula = document.getElementById("cedula");

    if (tipoCedula === "nacional") {
        cedula.value = "";
        cedula.placeholder = "#-####-####";  // Formato típico tico
        cedula.addEventListener("input", aplicarFormatoNacional);
    } else {
        cedula.value = "";
        cedula.placeholder = "";
        cedula.removeEventListener("input", aplicarFormatoNacional);
    }
}

// Aplica el formato automático a la cédula mientras se escribe
function aplicarFormatoNacional(e) {
    const cedula = e.target;
    const valor = cedula.value.replace(/\D/g, ""); // Quita todo lo que no sean números

    // Armar el formato #-####-#### paso a paso
    let formato = "";
    if (valor.length > 0) formato += valor.substring(0, 1) + "-";
    if (valor.length >= 2) formato += valor.substring(1, 5) + "-";
    if (valor.length >= 6) formato += valor.substring(5, 9);

    cedula.value = formato;
}

// Validar que las contraseñas coincidan antes de enviar el formulario
function validarFormulario() {
    const contrasena = document.getElementById("contrasena").value;
    const confirmarContrasena = document.getElementById("confirmar-contrasena").value;

    if (contrasena !== confirmarContrasena) {
        alert("Las contraseñas no coinciden, revisá bien.");
        return false;
    }

    return true;
}

// Funciones para manejar modales (ventanas emergentes)
const abrirModal = document.getElementById('abrirModal');
const modalFrame = document.getElementById('modalFrame');

// Mostrar el modal cuando se hace clic en el botón
abrirModal.addEventListener('click', (event) => {
    event.preventDefault(); // Evitar que el enlace haga su acción normal
    modalFrame.style.display = 'block'; // Mostrar el iframe del modal
    setTimeout(() => {
        modalFrame.contentWindow.document.getElementById('modal').classList.add('show');
    }, 10); // Pequeña pausa para que la animación se vea bien
});

// Función para cerrar sesión desde el dashboard
const logoutButton = document.getElementById('logoutButton');

logoutButton.addEventListener('click', () => {
    window.location.href = 'index.html';  // Regresar a la página principal
});
