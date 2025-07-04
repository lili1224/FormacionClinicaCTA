<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");

$usuario = $_POST['usuario'];
$email = $_POST['email'];
$contraseña = $_POST['contraseña'];

$sql = "INSERT INTO usuarios (username, email, password) VALUES ('$usuario', '$email', '$contraseña')";

if ($conn->query($sql) === TRUE) {
    echo "Usuario registrado correctamente";
} else {
    echo "Error al registrar usuario: " . $conn->error;
}
?>
