// assets/js/login.js
document.addEventListener('DOMContentLoaded', function () {
  const loginForm = document.getElementById('loginForm');
  const urlParams = new URLSearchParams(window.location.search);

  // Mostrar errores que vengan del servidor (?error=...)
  if (urlParams.has('error')) {
    const error = urlParams.get('error');
    const loginErrorSpan = document.getElementById('loginError');
    if (error === 'utente_non_trovato') {
      loginErrorSpan.textContent = 'Utente non trovato.';
      loginErrorSpan.style.display = 'block';
    } else if (error === 'password_errata') {
      loginErrorSpan.textContent = 'Password errata.';
      loginErrorSpan.style.display = 'block';
    }
  }

  loginForm.addEventListener('submit', function (event) {
    event.preventDefault();

    // Oculta errores previos
    document.querySelectorAll('.error').forEach((el) => (el.style.display = 'none'));

    const usernameOrEmail = document.getElementById('username_or_email').value.trim();
    const password = document.getElementById('password').value.trim();
    let isValid = true;

    if (usernameOrEmail === '') {
      const e = document.getElementById('loginError');
      if (e) { e.textContent = 'Inserisci il tuo username o e-mail.'; e.style.display = 'block'; }
      isValid = false;
    }
    if (password === '') {
      const e = document.getElementById('passwordError');
      if (e) { e.textContent = 'Inserisci la tua password.'; e.style.display = 'block'; }
      isValid = false;
    }

    if (isValid) this.submit(); // envía al servidor (pages/login.php)
  });
});