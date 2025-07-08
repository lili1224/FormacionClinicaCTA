<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");


/* Leemos la id de curso, si viene */
$courseId = filter_input(INPUT_GET, 'courseId', FILTER_VALIDATE_INT);

/* Preparamos la consulta */
if ($courseId) {
    /* — Episodios solo del curso indicado — */
    $stmt = $conn->prepare(
        "SELECT id,
                nombre       AS title,
                descripcion,
                video
         FROM   episodios
         WHERE  curso = ?"
    );
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    /* — Sin parámetro → todos los episodios (fallback) — */
    $result = $conn->query(
        "SELECT id,
                nombre       AS title,
                descripcion,
                video
         FROM   episodios"
    );
}

/* Ejecutamos y formateamos la respuesta */
$episodios = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($episodios);
?>