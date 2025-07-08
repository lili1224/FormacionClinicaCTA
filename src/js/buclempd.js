function getParam(name) {
  return new URLSearchParams(window.location.search).get(name);
}

async function initApp() {
  // 1. ID del episodio
  const episodeId = getParam('episodeId');
  if (!episodeId) {
    alert('Falta episodeId');
    return;
  }

  // 2. Petición al backend
  const res  = await fetch(`../DB/php/episodio.php?id=${episodeId}`);
  const data = await res.json();
  if (!data || !data.video) {
    alert('No se encontró el episodio o no tiene video');
    return;
  }

  // 3. Cargar Shaka
  shaka.polyfill.installAll();
  if (!shaka.Player.isBrowserSupported()) {
    alert('Navegador no soportado por Shaka');
    return;
  }

  const video   = document.getElementById('video');
  const player  = new shaka.Player(video);

  try {
    await player.load(data.video);   // <-- usa la URL de la BD
    console.log('Vídeo cargado correctamente');
  } catch (err) {
    console.error('Error al cargar vídeo: ', err);
    alert('No se pudo reproducir el vídeo');
  }

  // 4. (opcional) Título en la página
  document.title = data.title || 'Reproductor video';
}

document.addEventListener('DOMContentLoaded', initApp);
