<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");
$conn->set_charset("utf8mb4");

$sql =
        "SELECT episodios.id,
                episodios.nombre       AS title,
                episodios.descripcion,
                episodios.video,
                episodios.curso         AS courseId,
                cursos.titulo AS courseTitle,
                episodios.imagen       AS thumbnail
         FROM   episodios
         JOIN cursos ON episodios.curso = cursos.id
         ORDER  BY episodios.id DESC"
    ;


/* ================== EJECUCIÓN ================== */
$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error SQL: ' . $conn->error]);
    exit;
}

$episodios = $result->fetch_all(MYSQLI_ASSOC);

/* ================== SALIDA JSON ================== */
header('Content-Type: application/json');
echo json_encode($episodios);
?>
