<?php
$conn = new mysqli("db", "root", "root", "usuariosDB");

$usuario = $_POST['usuario'];
$contraseña = $_POST['contraseña'];

$sql = "SELECT * FROM usuarios WHERE username='$usuario' AND password='$contraseña'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Inicio de sesión exitoso";
} else {
    echo "Usuario o contraseña incorrectos";
}
?>
