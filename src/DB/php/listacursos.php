<?php
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli("db", "root", "root", "usuariosDB");
if ($conn->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$sql = "SELECT id, titulo FROM cursos ORDER BY titulo";
$result = $conn->query($sql);

$cursos = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cursos[] = $row;     // ['id'=>…, 'titulo'=>…]
    }
}

echo json_encode($cursos, JSON_UNESCAPED_UNICODE);
