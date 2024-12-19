<?php
$host = 'localhost'; 
$usuario = 'root';   
$contraseña = 'Canelita10';     
$nombre_bd = 'tienda_ropa'; 

// Crear conexión
$conn = new mysqli($host, $usuario, $contraseña, $nombre_bd);

// Comprobar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Establecer el conjunto de caracteres a UTF-8 para evitar problemas con caracteres especiales
$conn->set_charset('utf8');
?>
