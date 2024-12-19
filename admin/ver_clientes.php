<?php
require('../conexion.php');

session_start();

$nombre_usuario = $_SESSION['username'];

if (!$nombre_usuario) {
    header('Location: ../index.php');
    exit();
}

$clientes = [];
$cliente = null;

// Función para obtener clientes con filtros
function obtenerClientes($filtro_nombre = '') {
    global $conn;

    $sql = "SELECT * FROM clientes WHERE 1";

    if (!empty($filtro_nombre)) {
        $sql .= " AND nombre_completo LIKE '%" . $conn->real_escape_string($filtro_nombre) . "%'";
    }

    $result = $conn->query($sql);

    $clientes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }
    }

    return $clientes;
}

// Acción de editar Cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = $_POST['id_cliente'];
    $nombre_completo = $_POST['nombre_completo'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $nif = $_POST['nif'];

    if ($_POST['accion'] === 'editar') {
        $stmt = $conn->prepare("
            UPDATE clientes 
            SET nombre_completo = ?, telefono = ?, correo = ?, direccion = ?, nif = ? 
            WHERE id_cliente = ?
        ");
        $stmt->bind_param("sssssi", $nombre_completo, $telefono, $correo, $direccion, $nif, $id_cliente);
        $stmt->execute();
    }

    header('Location: gestion_clientes.php');
    exit();
}

// Acción de eliminar Cliente
if (isset($_GET['delete'])) {
    $id_cliente = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();

    header('Location: gestion_clientes.php');
    exit();
}

// Acción de cargar un Cliente para edición
if (isset($_GET['edit'])) {
    $id_cliente = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();
}

// Obtener los filtros de búsqueda
$filtro_nombre = $_GET['filtro_nombre'] ?? '';

// Obtener todos los clientes con filtros
$clientes = obtenerClientes($filtro_nombre);

$conn->close();

include('ver_clientes.html');
?>
 