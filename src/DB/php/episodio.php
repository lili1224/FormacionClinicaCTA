<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");
$conn->set_charset("utf8mb4");

/* 1. Si viene el parámetro "id", devuelve UN episodio */
$episodeId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($episodeId) {
    $stmt = $conn->prepare(
        "SELECT id,
                nombre       AS title,
                descripcion,
                video
         FROM   episodios
         WHERE  id = ?"
    );
    $stmt->bind_param("i", $episodeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $episodio = $result->fetch_assoc();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($episodio ?: []);
    exit;
}

/* 2. Si viene "courseId", devuelve varios episodios de ese curso */
$courseId = filter_input(INPUT_GET, 'courseId', FILTER_VALIDATE_INT);
if ($courseId) {
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
    /* 3. Si no viene nada, devuelve todos */
    $result = $conn->query(
        "SELECT id,
                nombre       AS title,
                descripcion,
                video
         FROM   episodios"
    );
}

$episodios = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($episodios);
?>
