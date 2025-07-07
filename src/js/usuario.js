document.addEventListener('DOMContentLoaded', () => {
  const usuario = localStorage.getItem('usuario');
  const isAdmin = localStorage.getItem('isAdmin');
  const btnLogin = document.getElementById('btn-login');
  const btnLogout = document.getElementById('btn-logout');
  const userNameEl = document.getElementById('user-name');

  if (!usuario) {
    window.location.href = 'iniciarsesion.html';
    return;
  }

  // Cambiar nombre en la cabecera
  if (btnLogin) {
    btnLogin.textContent = usuario.toUpperCase();
    btnLogin.href = isAdmin === '1' ? 'usuarioadmin.html' : 'usuario.html';
  }

  // Mostrar nombre en grande en la página
  if (userNameEl) {
    userNameEl.textContent = usuario.toUpperCase();
  }

  // Lógica del botón cerrar sesión
  if (btnLogout) {
    btnLogout.addEventListener('click', () => {
      localStorage.clear();
      window.location.href = 'iniciarsesion.html';
    });
  }
});
