<?php
require('../conexion.php');

session_start();

$nombre_usuario = $_SESSION['username'];

if (!$nombre_usuario) {
    header('Location: ../index.php');
    exit();
}

// Variables para almacenar proveedores
$proveedores = [];
$proveedor = null; 

// Función para obtener proveedores con filtros
function obtenerProveedores($filtro_nombre = '') {
    global $conn;

    $sql = "SELECT * FROM proveedores WHERE 1"; 

    // Aplicar el filtro de nombre si se ha introducido
    if (!empty($filtro_nombre)) {
        $sql .= " AND nombre_completo LIKE '%" . $conn->real_escape_string($filtro_nombre) . "%'";
    }

    // Ejecutar la consulta
    $result = $conn->query($sql);

    // Crear un array para almacenar 
    $proveedores = [];

    // Comprobar si hay resultados
    if ($result->num_rows > 0) {
        // Almacenar los resultados en el array
        while ($row = $result->fetch_assoc()) {
            $proveedores[] = $row;
        }
    }

    return $proveedores;
}

// Acción de agregar/editar Proveedor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del formulario
    $id_proveedor = $_POST['id_proveedor'] ?? null;
    $nombre_completo = $_POST['nombre_completo'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $nif = $_POST['nif'];

    if ($_POST['accion'] === 'agregar') {
        // Insertar Proveedor
        $stmt = $conn->prepare("
            INSERT INTO proveedores (nombre_completo, telefono, correo, direccion, nif) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $nombre_completo, $telefono, $correo, $direccion, $nif);
        $stmt->execute();
    } elseif ($_POST['accion'] === 'editar') {
        // Editar Proveedor
        $stmt = $conn->prepare("
            UPDATE proveedores 
            SET nombre_completo = ?, telefono = ?, correo = ?, direccion = ?, nif = ? 
            WHERE id_proveedor = ?
        ");
        $stmt->bind_param("sssssi", $nombre_completo, $telefono, $correo, $direccion, $nif, $id_proveedor);
        $stmt->execute();
    }

    // Redirigir al listado de proveedores después de agregar o editar
    header('Location: gestion_proveedores.php');
    exit();
}

// Acción de eliminar Proveedor
if (isset($_GET['delete'])) {
    $id_proveedor = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM proveedores WHERE id_proveedor = ?");
    $stmt->bind_param("i", $id_proveedor);
    $stmt->execute();

    // Redirigir al listado de proveedores después de eliminar
    header('Location: gestion_proveedores.php');
    exit();
}

// Acción de cargar un Proveedor para edición
if (isset($_GET['edit'])) {
    $id_proveedor = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM proveedores WHERE id_proveedor = ?");
    $stmt->bind_param("i", $id_proveedor);
    $stmt->execute();
    $result = $stmt->get_result();
    $proveedor = $result->fetch_assoc();
}

// Obtener los filtros de búsqueda
$filtro_nombre = isset($_GET['filtro_nombre']) ? $_GET['filtro_nombre'] : '';

// Obtener todos los proveedores con filtros
$proveedores = obtenerProveedores($filtro_nombre);

$conn->close();

// Incluir el archivo HTML
include('gestion_proveedores.html');
?>
