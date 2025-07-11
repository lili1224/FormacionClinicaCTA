document.addEventListener('DOMContentLoaded', async () => {
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
    btnLogin.href = isAdmin === '1' ? 'usuarioadmin.html' : 'usuario.html';
  }

  if (btnLogout) {
    btnLogout.style.display = 'inline-block';
    btnLogout.addEventListener('click', () => {
      localStorage.clear();
      window.location.href = 'iniciarsesion.html';
    });
  }

  // Cargar nombres de cursos aleatorios y asignarlos
  try {
    const res = await fetch('DB/php/cursos.php');
    const cursos = await res.json();

    // Mezcla aleatoria y selecciona 3 cursos
    const cursosAleatorios = cursos.sort(() => Math.random() - 0.5).slice(0, 3);

    // Asignar títulos y enlaces a las cajas
    const cajas = document.querySelectorAll('.image-box');
    cajas.forEach((box, index) => {
      const overlay = box.querySelector('.overlay-text');
      const curso = cursosAleatorios[index];

      if (curso && overlay) {
        overlay.textContent = curso.title; // CAMBIA el texto
        box.style.cursor = 'pointer';
        box.addEventListener('click', () => {
          window.location.href = `episodio.html?courseId=${curso.id}`;
        });
      }
    });
  } catch (err) {
    console.error('Error cargando cursos:', err);
  }
});
