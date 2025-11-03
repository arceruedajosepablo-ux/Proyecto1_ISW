function cambiarFormatoCedula() {
    const tipoCedula = document.getElementById("tipo-cedula").value;
    const cedula = document.getElementById("cedula");

    if (tipoCedula === "nacional") {
        cedula.value = "";
        cedula.placeholder = "#-####-####";
        cedula.addEventListener("input", aplicarFormatoNacional);
    } else {
        cedula.value = "";
        cedula.placeholder = "";
        cedula.removeEventListener("input", aplicarFormatoNacional);
    }
}

function aplicarFormatoNacional(e) {
    const cedula = e.target;
    const valor = cedula.value.replace(/\D/g, ""); // Elimina caracteres no numéricos

    let formato = "";
    if (valor.length > 0) formato += valor.substring(0, 1) + "-";
    if (valor.length >= 2) formato += valor.substring(1, 5) + "-";
    if (valor.length >= 6) formato += valor.substring(5, 9);

    cedula.value = formato;
}

function validarFormulario() {
    const contrasena = document.getElementById("contrasena").value;
    const confirmarContrasena = document.getElementById("confirmar-contrasena").value;

    if (contrasena !== confirmarContrasena) {
        alert("Las contraseñas no coinciden.");
        return false;
    }

    return true;
}

//abrir y cerrar modal
const abrirModal = document.getElementById('abrirModal');
const modalFrame = document.getElementById('modalFrame');

// Mostrar el modal al hacer clic en el botón
abrirModal.addEventListener('click', (event) => {
    event.preventDefault(); // Evita que el enlace se comporte como un enlace estándar
    modalFrame.style.display = 'block'; // Mostrar el iframe
    setTimeout(() => {
        modalFrame.contentWindow.document.getElementById('modal').classList.add('show');
    }, 10); // Agregar clase después de un breve retraso para activar la animación
});


//cerar sesión dashboard
const logoutButton = document.getElementById('logoutButton');

logoutButton.addEventListener('click', () => {
    window.location.href = 'index.html';
});
