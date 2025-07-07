<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");
if ($conn->connect_errno) {
    die("Error de conexión: " . $conn->connect_error);
}

$usuario = $_POST['usuario'];
$contraseña = $_POST['contrasena'];

// Buscar el usuario
$stmt = $conn->prepare("SELECT password, is_admin FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
        alert('Usuario o contraseña incorrectos');
        window.location.href='/iniciarsesion.html';
    </script>";
    exit;
}

$row = $result->fetch_assoc();
$password_hash = $row['password'];
$is_admin = $row['is_admin'];

if (password_verify($contraseña, $password_hash)) {
    echo "<script>
        // Guardamos en localStorage
        localStorage.setItem('usuario', '$usuario');
        localStorage.setItem('isAdmin', '$is_admin');
        window.location.href = '/index.html';
    </script>";
    exit;
} else {
    echo "<script>
        alert('Usuario o contraseña incorrectos');
        window.location.href='/iniciarsesion.html';
    </script>";
    exit;
}
?>
