function getParam(name) {
  return new URLSearchParams(window.location.search).get(name);
}

async function loadCourseAndEpisodes() {
  const courseId = getParam('courseId');
  if (!courseId) {
    alert('Falta el parámetro courseId');
    return;
  }

  try {
    const [courseRes, episodesRes] = await Promise.all([
      fetch(`../DB/php/curso.php?id=${courseId}`),
      fetch(`../DB/php/episodios.php?courseId=${courseId}`)
    ]);

    const course    = await courseRes.json();
    const episodes  = await episodesRes.json();

    // título y descripción del curso
    document.getElementById('courseTitle').textContent       = course.title;
    document.getElementById('courseDescription').textContent = course.description;

    // lista de episodios
    const container = document.getElementById('episodes');
    container.innerHTML = '';

    episodes.forEach((ep, i) => {
      const li = document.createElement('li');
      li.className = 'episode-item';

      /*  👇 Enlace directo al reproductor
          Pasamos episodeId (y opcionalmente courseId para un botón “volver”) */
      li.innerHTML = `
        <a href="reproductor.html?episodeId=${ep.id}&courseId=${courseId}">
          <strong>${i + 1}. ${ep.title}</strong>
        </a>
        <span>(${ep.video})</span>
      `;

      container.appendChild(li);
    });
  } catch (err) {
    console.error('Error cargando curso o episodios:', err);
  }
}

document.addEventListener('DOMContentLoaded', loadCourseAndEpisodes);
