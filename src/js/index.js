document.addEventListener('DOMContentLoaded', () => {
  const usuario = localStorage.getItem('usuario');
  const isAdmin = localStorage.getItem('isAdmin');
  const btnLogin = document.getElementById('btn-login');
  const btnLogout = document.getElementById('btn-logout');

  if (!usuario) {
    window.location.href = 'iniciarsesion.html';
    return;
  }

  if (btnLogin) {
    btnLogin.textContent = usuario.toUpperCase();
    if (isAdmin === '1') {
      btnLogin.href = 'usuarioadmin.html';
    } else {
      btnLogin.href = 'usuario.html';
    }
  }

  if (btnLogout) {
    btnLogout.style.display = 'inline-block';
    btnLogout.addEventListener('click', () => {
      localStorage.clear();
      window.location.href = 'iniciarsesion.html';
    });
  }
});
