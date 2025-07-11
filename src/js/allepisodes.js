async function loadAllEpisodes() {
  try {
    const res      = await fetch('../DB/php/alepisodio.php');   // endpoint completo
    const episodes = await res.json();

    const list = document.getElementById('episodes');
    list.innerHTML = '';

    episodes.forEach((ep, idx) => {
      const li = document.createElement('li');
      li.className = 'episode-item';             // usa la clase que ya está en tu CSS

      // miniatura opcional
      const thumb = ep.thumbnail || 'https://placehold.co/320x180?text=Episodio';

      li.innerHTML = `
        <a href="reproductor.html?episodeId=${ep.id}&courseId=${ep.courseId}" class="block">
          <img src="${thumb}" alt="${ep.title}" />
          <strong>${idx + 1}. ${ep.title}</strong>
          <span>${ep.courseTitle}</span>
        </a>
      `;

      list.appendChild(li);
    });
  } catch (err) {
    console.error('Error cargando episodios:', err);
  }
}

document.addEventListener('DOMContentLoaded', loadAllEpisodes);
