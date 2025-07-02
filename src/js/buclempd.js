    const manifestUri = "../../backend/MPD/output/10.mpd"  

    // Mostrar el video
    const video = document.getElementById("video");

    // Instalar polyfills y reproducir con Shaka
    shaka.polyfill.installAll();

    if (!shaka.Player.isBrowserSupported()) {
      alert("Navegador no soportado por Shaka Player");
      return;
    }

    const player = new shaka.Player(video);

    try {
      await player.load(manifestUri);
      console.log('El video se ha cargado correctamente!');
    } catch (error) {
      console.error('Error al cargar el video:', error);
      alert('Error al cargar el video: ' + error.message);
    }


  window.onload = cargarListaVideos;