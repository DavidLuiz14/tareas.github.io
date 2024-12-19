<?php
session_start();
require('../conexion.php'); 


// Obtener el nombre de usuario de la sesión (asumiendo que está en la sesión después del login)
$nombre_usuario = $_SESSION['username'];

// Verificar si el usuario está autenticado
if (!$nombre_usuario) {
    header('Location: ../index.php');
    exit();
}

// Variables para almacenar los datos del cliente
$cliente = [];

// Función para obtener datos del cliente
function obtenerCliente($nombre_usuario) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE nombre_usuario = ?");
    $stmt->bind_param("s", $nombre_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Acción de editar perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'editar') {
    $nombre_completo = $_POST['nombre_completo'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $nif = $_POST['nif'];

    $stmt = $conn->prepare("
        UPDATE clientes 
        SET nombre_completo = ?, telefono = ?, correo = ?, direccion = ?, nif = ? 
        WHERE nombre_usuario = ?
    ");
    $stmt->bind_param("ssssss", $nombre_completo, $telefono, $correo, $direccion, $nif, $nombre_usuario);
    $stmt->execute();

    // Redirigir después de actualizar
    header('Location: perfil.php');
    exit();
}

// Acción de eliminar cuenta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'eliminar') {
    // Iniciar transacción para eliminar de ambas tablas
    $conn->begin_transaction();
    try {
        // Eliminar de clientes
        $stmt = $conn->prepare("DELETE FROM clientes WHERE nombre_usuario = ?");
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();

        // Eliminar de usuarios
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE nombre_usuario = ?");
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();

        // Confirmar transacción
        $conn->commit();

        // Destruir sesión y redirigir al login
        session_destroy();
        header('Location: ../index.php');
        exit();
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        echo "Error al eliminar la cuenta: " . $e->getMessage();
    }
}

// Obtener datos del cliente para mostrar en el formulario
$cliente = obtenerCliente($nombre_usuario);

// Cerrar conexión
$conn->close();

// Incluir el archivo HTML
include('perfil.html');
?>
