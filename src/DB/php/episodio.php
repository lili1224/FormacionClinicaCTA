<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");


$sql = "SELECT id, nombre AS title, descripcion, video FROM episodios WHERE id = ?";
        
$result = $conn->query($sql);
$episodios = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
echo json_encode($episodios ?: []);
?>