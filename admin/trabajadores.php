<?php
require('../conexion.php'); 

session_start();

$nombre_usuario = $_SESSION['username'];

if (!$nombre_usuario) {
    header('Location: ../index.php');
    exit();
}


$salarioBase = 100.00; 
$trabajadores = [];


// Consulta para obtener todos los trabajadores
$query = "SELECT * FROM trabajadores";
$result = $conn->query($query);

if ($result) {
    $trabajadores = $result->fetch_all(MYSQLI_ASSOC); // Recupera todos los trabajadores
}

// Si se está realizando una inserción o modificación de un trabajador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_trabajador = isset($_POST['id_trabajador']) ? $_POST['id_trabajador'] : null;
    $nombre_completo = $_POST['nombre_completo'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $puesto = $_POST['puesto'];
    $dias_trabajados = $_POST['dias_trabajados'];
    $salarioBase = $_POST['salario_base'];

    // Validación del salario base y cálculo del salario calculado
    $salario_calculado = $dias_trabajados * $salarioBase;

    if ($id_trabajador) {
        // Si existe el ID, actualizar los datos del trabajador
        $stmt = $conn->prepare("
            UPDATE trabajadores 
            SET 
                nombre_completo = ?, 
                telefono = ?, 
                direccion = ?, 
                puesto = ?, 
                dias_trabajados = ?, 
                salario_base = ? 
            WHERE id_trabajador = ?
        ");
        $stmt->bind_param("ssssidi", $nombre_completo, $telefono, $direccion, $puesto, $dias_trabajados, $salarioBase, $id_trabajador);
        $stmt->execute();

        // Redirigir con un mensaje de éxito
        header("Location: trabajadores.php?message=Trabajador actualizado correctamente");
        exit;
    } else {
        // Si no existe el ID, insertar un nuevo trabajador
        $stmt = $conn->prepare("
            INSERT INTO trabajadores (nombre_completo, telefono, direccion, puesto, dias_trabajados, salario_base, fecha_registro) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("ssssii", $nombre_completo, $telefono, $direccion, $puesto, $dias_trabajados, $salarioBase);
        $stmt->execute();

        // Redirigir con un mensaje de éxito
        header("Location: trabajadores.php?message=Trabajador agregado correctamente");
        exit;
    }
}

// Manejo de la eliminación de un trabajador
if (isset($_GET['delete'])) {
    $id_trabajador = $_GET['delete'];

    // Eliminar el trabajador
    $stmt = $conn->prepare("DELETE FROM trabajadores WHERE id_trabajador = ?");
    $stmt->bind_param("i", $id_trabajador);
    $stmt->execute();

    // Redirigir con un mensaje de éxito
    header("Location: trabajadores.php?message=Trabajador eliminado correctamente");
    exit;
}

// Si se desea editar un trabajador
if (isset($_GET['edit'])) {
    $id_trabajador = $_GET['edit'];

    // Consultar los datos del trabajador
    $stmt = $conn->prepare("SELECT * FROM trabajadores WHERE id_trabajador = ?");
    $stmt->bind_param("i", $id_trabajador);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si el trabajador existe
    if ($result->num_rows > 0) {
        $trabajador = $result->fetch_assoc(); // Obtener los datos del trabajador
    } else {
        echo "Trabajador no encontrado";
        exit;
    }
}

include('trabajadores.html');
?>
