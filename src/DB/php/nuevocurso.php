<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");

$titulo_curso = $_POST['titulo'];
$descripcion_curso = $_POST['descripcion'];
$accesibilidad = $_POST['accesibilidad'];
$keywords = $_POST['keywords'];

$sql = "INSERT INTO cursos (titulo, descripcion, accesibilidad, keywords) VALUES ('$titulo_curso', '$descripcion_curso', '$accesibilidad', '$keywords')";

if ($conn->query($sql) === TRUE) {
    header("location: ../../nuevocurso.html?success=true");
    exit;

} else {
    echo "Error al crear el curso: " . $conn->error;
}

?>