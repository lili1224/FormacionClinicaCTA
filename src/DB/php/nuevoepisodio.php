<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");

$titulo_episodio = $_POST['titulo'];
$descripcion_episodio = $_POST['descripcion'];
$video = $_POST['video'];
$curso = $_POST['curso'];

$sql = "INSERT INTO episodios (nombre, descripcion, video, curso) VALUES ('$titulo_episodio', '$descripcion_episodio', '$video', '$curso')";

if ($conn->query($sql) === TRUE) {
    header("location: ../../nuevoepisodio.html?success=true");
    exit;
} else {
    echo "Error al crear el curso: " . $conn->error;
}

?>