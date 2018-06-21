$(document).ready(function() {
    var btnSubmit = document.getElementsByTagName('button');
    btnSubmit[1].addEventListener("click", Enviar);
});

function Enviar(e) {
    var inpDNI = document.getElementsByClassName('DNI');
    var correcto = validarDNI(inpDNI[0].value);
    var contenedor = document.getElementById('validacion');
    if(correcto == true){
        contenedor.textContent = "";
        e.submit();
    }else {
        e.preventDefault();
        var divFallo = document.createElement("div");
        var texto = document.createTextNode("DNI mal escrito");
        divFallo.setAttribute("id", "falloDNI");
        divFallo.setAttribute("class", "alert alert-danger");
        divFallo.appendChild(texto);
        contenedor.appendChild(divFallo);
    }
}

function validarDNI(dni) {
    var expresion_regular_dni = /^[XYZ]?\d{5,8}[A-Z]$/;

    dni = dni.toUpperCase();

    if(expresion_regular_dni.test(dni) === true){
        return true;
    }else{
        return false;
    }
}