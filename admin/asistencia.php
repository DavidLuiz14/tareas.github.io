<?php
require('../conexion.php'); 
session_start();

$nombre_usuario = $_SESSION['username'];

// Verificar si el cliente está autenticado
if (!$nombre_usuario) {
    header('Location: ../index.php');
    exit();
}

// Obtener la lista de trabajadores para mostrar en el formulario
$queryTrabajadores = $conn->query("SELECT * FROM trabajadores");
$trabajadores = $queryTrabajadores->fetch_all(MYSQLI_ASSOC);
//var_dump($trabajadores);  

// Obtener la lista de asistencias
$asistencias = [];
$query = $conn->query("
    SELECT a.id_asistencia, t.nombre_completo AS nombre_trabajador, a.fecha, a.hora_llegada, a.hora_salida, a.horas_trabajadas
    FROM asistencia a
    JOIN trabajadores t ON a.id_trabajador = t.id_trabajador
");

if ($query) {
    $asistencias = $query->fetch_all(MYSQLI_ASSOC); // Recupera todas las asistencias
}

// Si se está realizando una inserción o modificación de una asistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_asistencia = isset($_POST['id_asistencia']) ? $_POST['id_asistencia'] : null;
    $id_trabajador = $_POST['id_trabajador'];
    $fecha = $_POST['fecha'];
    $hora_llegada = $_POST['hora_llegada'];
    $hora_salida = $_POST['hora_salida'];

    // Calcular horas trabajadas 
    $hora_llegada_timestamp = strtotime($fecha . ' ' . $hora_llegada);
    $hora_salida_timestamp = $hora_salida ? strtotime($fecha . ' ' . $hora_salida) : time(); // Si no tiene salida, usar el momento actual
    $horas_trabajadas = ($hora_salida_timestamp - $hora_llegada_timestamp) / 3600;

    if ($id_asistencia) {
        // Si existe el ID, actualizar la asistencia
        $stmt = $conn->prepare("
            UPDATE asistencia 
            SET id_trabajador = ?, fecha = ?, hora_llegada = ?, hora_salida = ?, horas_trabajadas = ? 
            WHERE id_asistencia = ?
        ");
        $stmt->bind_param("isssdi", $id_trabajador, $fecha, $hora_llegada, $hora_salida, $horas_trabajadas, $id_asistencia);
        $stmt->execute();

        header("Location: asistencia.php?message=Asistencia actualizada correctamente");
        exit;
    } else {
        // Si no existe el ID, insertar una nueva asistencia
        $stmt = $conn->prepare("
            INSERT INTO asistencia (id_trabajador, fecha, hora_llegada, hora_salida, horas_trabajadas) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssd", $id_trabajador, $fecha, $hora_llegada, $hora_salida, $horas_trabajadas);
        $stmt->execute();

        header("Location: asistencia.php?message=Asistencia registrada correctamente");
        exit;
    }
}

// Manejo de la eliminación de una asistencia
if (isset($_GET['delete'])) {
    $id_asistencia = $_GET['delete'];

    // Eliminar la asistencia
    $stmt = $conn->prepare("DELETE FROM asistencia WHERE id_asistencia = ?");
    $stmt->bind_param("i", $id_asistencia);
    $stmt->execute();

    header("Location: asistencia.php?message=Asistencia eliminada correctamente");
    exit;
}

// Si se desea editar una asistencia
if (isset($_GET['edit'])) {
    $id_asistencia = $_GET['edit'];

    // Consultar los datos de la asistencia
    $stmt = $conn->prepare("SELECT * FROM asistencia WHERE id_asistencia = ?");
    $stmt->bind_param("i", $id_asistencia);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si la asistencia existe
    if ($result->num_rows > 0) {
        $asistencia = $result->fetch_assoc(); // Obtener los datos de la asistencia
    } else {
        echo "Asistencia no encontrada";
        exit;
    }
}

include('asistencia.html');
?>
