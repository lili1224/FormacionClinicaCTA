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

const PROJECT_BASE = '/FormacionClinicaCTA';               
$docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '/var/www/html') ?: '/var/www/html';



// Validación básica
if ($titulo === '' || $desc === '' || $video === '') {
    http_response_code(400);
    exit('Datos incompletos');
}

$src = realpath("/mnt/videos/$video");
if (!$src || !str_starts_with($src, '/mnt/videos/')) {
    exit('Vídeo no válido');
}

/* ─────────  DIRECTORIO DE SALIDA ───────── */

$relativeOutput = PROJECT_BASE .
                  '/src/DB/php/VideosProcesados/' .
                  pathinfo($video, PATHINFO_FILENAME);

$outDir = $relativeOutput;
@mkdir("$outDir/output", 0777, true);

/* ─────────  PROCESADO ───────── */
$timestamp = date('Ymd_His');
$logFile   =  PROJECT_BASE . "/logs/procesado_$timestamp.txt";

$cmd = escapeshellcmd("python3 ../../../Backend/procesado.py '$src' '$outDir'")
     . " > " . escapeshellarg($logFile) . " 2>&1";

exec($cmd, $out, $code);

if ($code !== 0) {
    http_response_code(500);
    exit("Error procesando el vídeo. "
       . "<a href='" . PROJECT_BASE . "/logs/" . basename($logFile) . "' target='_blank'>ver log</a>");
}

/* ─────────  LOCALIZA EL .mpd ───────── */
clearstatcache();
$mpdAbsolute = realpath("$outDir/output/video.mpd");

if (!$mpdAbsolute || !is_file($mpdAbsolute)) {
    $candidatos = glob("$outDir/output/*.mpd");
    if (!$candidatos) {
        http_response_code(500);
        exit('No se generó el MPD. Revisa el log.');
    }
    $mpdAbsolute = realpath($candidatos[0]);
}


/* ─────────  URL QUE GUARDAREMOS ─────────
   ⇒ /FormacionClinicaCTA/src/DB/php/VideosProcesados/ferrandis/output/video.mpd */
$mpdWeb = $relativeOutput . '/output/' . basename($mpdAbsolute);

$stmt = $conn->prepare(
    "INSERT INTO episodios (nombre, descripcion, video, curso)
     VALUES (?,?,?,?)"
);
$stmt->bind_param('ssss', $titulo, $desc, $mpdWeb, $curso);
$stmt->execute();

header('Location: ' . PROJECT_BASE . '/nuevoepisodio.html?success=1');
exit;

?>