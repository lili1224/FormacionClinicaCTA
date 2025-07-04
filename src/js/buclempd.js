const manifestUri = '../../Backend/MPD/output/video.mpd';  // La URL completa ya viene del servidor

    function  initApp(){
        // Instalar polyfills y reproducir con Shaka
        shaka.polyfill.installAll();
        if (shaka.Player.isBrowserSupported()) {
            initPlayer();
        } else {
            console.log("Navegador no soportado por Shaka Player");
        }
    }
    
    document.addEventListener('DOMContentLoaded', initApp);

    async function initPlayer() {
        const video = document.getElementById("video");
        const player = new shaka.Player(video);

        try {
            await player.load(manifestUri);
            console.log('El video se ha cargado correctamente!');
        }catch (error) {
            console.error('Error al cargar el video:', error);
            alert('Error al cargar el video: ' + error.message);
        }
    }