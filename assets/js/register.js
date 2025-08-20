// assets/js/register.js

document.getElementById('registroForm').addEventListener('submit', function(event) {
    event.preventDefault();

    document.querySelectorAll('.error').forEach(function(errorSpan) {
        errorSpan.style.display = 'none';
    });

    const nome = document.getElementById('nome').value.trim();
    const cognome = document.getElementById('cognome').value.trim();
    const email = document.getElementById('email').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const conferma_password = document.getElementById('conferma_password').value.trim();
    let isValid = true;

    if (nome === '' || cognome === '' || username === '' || password === '') {
        isValid = false;
        alert("Tutti i campi obbligatori devono essere compilati.");
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        isValid = false;
        document.getElementById('emailError').style.display = 'block';
    }

    if (password.length < 8) {
        isValid = false;
        document.getElementById('passwordError').style.display = 'block';
    }

    if( password !== conferma_password) {
        isValid = false;
        document.getElementById('confermaPasswordError').style.display = 'block';
    }

    if (isValid) {
        this.submit();
    }
});