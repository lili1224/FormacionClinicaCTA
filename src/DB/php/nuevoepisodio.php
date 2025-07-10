<?php 
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ───── CONFIGURACIONES DE RUTA ─────
define('FS_ROOT', realpath(dirname(__DIR__, 3))); // /var/www/html/FormacionClinicaCTA
define('WEB_ROOT', '');

// ───── CONEXIÓN ─────
$conn = new mysqli('db','root','root','usuariosDB');
$conn->set_charset('utf8mb4');

// ───── DATOS DEL FORMULARIO ─────
$titulo = trim($_POST['titulo'] ?? '');
$desc   = trim($_POST['descripcion'] ?? '');
$curso  = trim($_POST['curso'] ?? '');
$video  = basename($_POST['video'] ?? '');

// ───── VALIDACIÓN ─────
if ($titulo === '' || $desc === '' || $video === '') {
    http_response_code(400);
    exit('Datos incompletos');
}

$src = realpath(__DIR__ . '/../../VideosOriginales/mnt/videos/' . $video);
if (!$src || !str_contains($src, '/VideosOriginales/mnt/videos/')) {
    exit('Vídeo no válido');
}



// ───── DIRECTORIO DE SALIDA ─────
$videoName = pathinfo($video, PATHINFO_FILENAME);
$outDir = "/var/www/html/media/$videoName";
@mkdir($outDir, 0777, true);

// ───── PROCESADO PYTHON ─────
$timestamp = date('Ymd_His');
$logFile = "/var/www/html/logs/procesado_$timestamp.txt";
$logWeb  = WEB_ROOT . '/logs/procesado_' . $timestamp . '.txt';

$cmd = escapeshellcmd("python3 /opt/backend/procesado.py '$src' '$outDir'")
     . " > " . escapeshellarg($logFile) . " 2>&1";

exec($cmd, $out, $code);

if ($code !== 0) {
    http_response_code(500);

    // ▼----------------- DEPURACIÓN RÁPIDA ----------------▼
    echo "<pre>";
    echo "Código devuelto por Python: $code\n";
    echo "Log generado en: $logFile\n";
    echo "</pre>";
    // ▲----------------- /DEPURACIÓN ----------------▲

    exit("Error procesando el vídeo. <a href='$logWeb' target='_blank'>Ver log</a>");
}

// ───── LOCALIZAR MPD ─────
clearstatcache();
$mpdAbsolute = realpath("$outDir/output/video.mpd");

if (!$mpdAbsolute || !is_file($mpdAbsolute)) {
    $candidatos = glob("$outDir/output/*.mpd");
    if (!$candidatos) {
        http_response_code(500);
        exit("No se generó el MPD. <a href='$logWeb' target='_blank'>Revisar log</a>");
    }
    $mpdAbsolute = realpath($candidatos[0]);
}

// ───── GUARDAR RUTA RELATIVA PARA WEB ─────
$mpdWeb = WEB_ROOT . '/media/' . $videoName . '/output/' . basename($mpdAbsolute);

// ───── GUARDAR EN BASE DE DATOS ─────
$stmt = $conn->prepare(
    "INSERT INTO episodios (nombre, descripcion, video, curso)
     VALUES (?,?,?,?)"
);
$stmt->bind_param('ssss', $titulo, $desc, $mpdWeb, $curso);
$stmt->execute();

// ───── REDIRECCIÓN ─────
header('Location: ' . WEB_ROOT . '/nuevoepisodio.html?success=1');
exit;
?>
