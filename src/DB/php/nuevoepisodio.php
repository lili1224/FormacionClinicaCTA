<?php 
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli('db','root','root','usuariosDB');
$conn->set_charset('utf8mb4');

$titulo = trim($_POST['titulo'] ?? '');
$desc   = trim($_POST['descripcion'] ?? '');
$curso  = trim($_POST['curso'] ?? '');
$video  = basename($_POST['video'] ?? '');

// Validación básica
if ($titulo === '' || $desc === '' || $video === '') {
    http_response_code(400);
    exit('Datos incompletos');
}

$src = realpath("/mnt/videos/$video");
if (!$src || !str_starts_with($src, '/mnt/videos/')) {
    exit('Vídeo no válido');
}

$outDir = "VideosProcesados/".pathinfo($video, PATHINFO_FILENAME);
@mkdir($outDir, 0777, true);

$timestamp = date('Ymd_His');
$logFile = "/var/www/html/logs/procesado_$timestamp.txt";

$cmd = escapeshellcmd("python3 /opt/backend/procesado.py '$src' '$outDir'")
      . " > " . escapeshellarg($logFile) . " 2>&1";

exec($cmd, $out, $code);
$log = implode("\n", $out);

if ($code !== 0) {
    http_response_code(500);
    exit("Error procesando el vídeo. Detalles: <a href='/logs/" . basename($logFile) . "' target='_blank'>ver log</a>");
}

// 3) Comprueba la existencia del MPD con ruta absoluta real
$mpdAbsolute = realpath("$outDir/output/video.mpd");
// vacía la caché de stat para que PHP no use datos viejos
clearstatcache();

if ($mpdAbsolute === false || !is_file($mpdAbsolute)) {
    // ▸ Plan B: busca cualquier .mpd dentro de output/
    $candidatos = glob($outDir . "/output/*.mpd");
    if ($candidatos) {
        $mpdAbsolute = realpath($candidatos[0]);     // primer MPD hallado
    } else {
        error_log("MPD no encontrado en $outDir/output/");
        http_response_code(500);
        exit("No se generó el MPD. Revisa el log.");
    }
}

// 4) Ruta “web” (sin /var/www/html) para guardar en la BD
$mpdWeb = str_replace('/var/www/html', '', $mpdAbsolute);

// …inserta $mpdWeb en MySQL…
$stmt = $conn->prepare(
   "INSERT INTO episodios (nombre, descripcion, video, curso)
    VALUES (?,?,?,?)"
);
$stmt->bind_param('ssss', $titulo, $desc, $mpdWeb, $curso);
$stmt->execute();

header('Location: /nuevoepisodio.html?success=1');