<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");


$sql = "SELECT id,
               titulo       AS title,
               descripcion  AS description,
               imagen       AS thumbnail
        FROM   cursos
        ORDER  BY id DESC";
        
$result = $conn->query($sql);
$cursos = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
echo json_encode($cursos);
?>