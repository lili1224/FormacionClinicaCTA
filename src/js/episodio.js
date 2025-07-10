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
      fetch(`../DB/php/cursos.php?id=${courseId}`),
      fetch(`../DB/php/episodio.php?courseId=${courseId}`)
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

      const thumbnail = ep.thumbnail || 'https://placehold.co/640x360?text=Episodio';

      li.innerHTML = `
        <img src="${thumbnail}" alt="${ep.title}" class="rounded-md mb-2 h-40 object-cover w-full" />
        <a href="reproductor.html?episodeId=${ep.id}&courseId=${courseId}">
          <strong>${i + 1}. ${ep.title}</strong>
        </a>
        <p>${ep.descripcion}</p>
      `;

      container.appendChild(li);
    });


  } catch (err) {
    console.error('Error cargando curso o episodios:', err);
  }
}

document.addEventListener('DOMContentLoaded', loadCourseAndEpisodes);
