<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");

$usuario = $_POST['usuario'];
$correo = $_POST['correo'];
$contraseña = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

// Comprobar si el usuario o correo ya existen
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $usuario, $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Ya existe
    echo "<script>alert('Usuario o correo ya registrado'); window.location.href='/iniciarsesion.html';</script>";
    exit;
}

// Insertar nuevo usuario
$stmt = $conn->prepare("INSERT INTO usuarios (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $usuario, $correo, $contraseña);
$stmt->execute();

echo "<script>alert('Registro exitoso. Ya puedes iniciar sesión.'); window.location.href='/iniciarsesion.html';</script>";
?>
