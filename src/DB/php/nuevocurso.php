<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");

$titulo_curso = $_POST['titulo'];
$descripcion_curso = $_POST['descripcion'];
$accesibilidad = $_POST['accesibilidad'];
$keywords = $_POST['keywords'];

// Directorio absoluto donde se guardarán las imágenes
$uploadDir = __DIR__ . '/../../uploads/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    die('No se pudo crear la carpeta de subida.');
}

// 1. Obtener la extensión
$extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);

// 2. Generar nombre único
$nombreImagen = uniqid('curso_', true) . '.' . $extension;

// 3. Construir rutas
$rutaDestino = $uploadDir . $nombreImagen;
$rutaBD      = 'uploads/' . $nombreImagen;   // ← nombre correcto

// 4. Mover la imagen
if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
    die('Error al mover la imagen al directorio de destino.');
}

// 5. INSERT (corrigiendo nombre y comilla final)
$sql = "INSERT INTO cursos (titulo, descripcion, accesibilidad, keywords, imagen)
        VALUES ('$titulo_curso', '$descripcion_curso', '$accesibilidad', '$keywords', '$rutaBD')";

if ($conn->query($sql) === TRUE) {
    header("location: ../../nuevocurso.html?success=true");
    exit;
} else {
    echo "Error al crear el curso: " . $conn->error;
}