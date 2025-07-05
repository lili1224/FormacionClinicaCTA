<?php 
declare(strict_types=1);
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

$cmd = escapeshellcmd("python3 /opt/backend/procesado.py '$src' '$outDir'");
exec($cmd, $out, $code);
if ($code !== 0) { /* maneja error */ }

$mpd = "/media/".basename($outDir)."/output/video.mpd";

$stmt = $conn->prepare(
   "INSERT INTO episodios (nombre, descripcion, video, curso)
    VALUES (?,?,?,?)"
);
$stmt->bind_param('ssss',$titulo,$desc,$mpd,$curso);
$stmt->execute();

header('Location: /nuevoepisodio.html?success=1');