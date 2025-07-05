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

$outDir = "/var/www/html/media/".pathinfo($video, PATHINFO_FILENAME);
@mkdir($outDir, 0777, true);

/* ---------- DEBUG SOLO MIENTRAS PROBAMOS ---------- */
$cmd = escapeshellcmd(
    "python3 /opt/backend/procesado.py "
  . escapeshellarg($src) . ' ' . escapeshellarg($outDir)
) . ' 2>&1';                      // mezclamos stderr + stdout

exec($cmd, $out, $code);

echo '<h3>DEBUG: comando ejecutado</h3><pre>'.
     htmlspecialchars($cmd) .
     '</pre><h3>Salida completa</h3><pre>'.
     htmlspecialchars(implode("\n", $out)) .
     "\n\nExit-code: $code</pre>";
exit;  // <-- detenemos aquí para leer
/* --------------------------------------------------- */

$cmd = escapeshellcmd(
    "python3 ../../Backend/procesado.py "
  . escapeshellarg($src) . ' ' . escapeshellarg($outDir)
) . ' 2>&1';                     // ← redirige stderr a stdout

exec($cmd, $out, $code);
$log = implode("\n", $out);

if ($code !== 0) {
    error_log("Procesado falló ($code):\n$log");
    http_response_code(500);
    exit('Error procesando el vídeo. Revisa el log del servidor.');
}

$mpd = "$outDir/output/video.mpd";
if (!is_file($mpd)) {
    error_log("MPD no generado en $mpd");
    http_response_code(500);
    exit('No se generó el MPD. Revisa el log.');
}

$stmt = $conn->prepare(
   "INSERT INTO episodios (nombre, descripcion, video, curso)
    VALUES (?,?,?,?)"
);
$stmt->bind_param('ssss',$titulo,$desc,$mpd,$curso);
$stmt->execute();

header('Location: /nuevoepisodio.html?success=1');