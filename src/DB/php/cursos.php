<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");


$sql = "SELECT
            id,
            titulo       AS title,       
            descripcion  AS description,
            NULL         AS thumbnail   
        FROM cursos";
        
$result = $conn->query($sql);
$cursos = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
echo json_encode($cursos);
?>