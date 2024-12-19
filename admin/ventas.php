<?php
require('../conexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/ProyectoV2.0/lib/fpdf/fpdf.php');

session_start();

$nombre_usuario = $_SESSION['username'];

if (!$nombre_usuario) {
    header('Location: ../index.php');
    exit();
} 

// Procesar la inserción de una venta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'])) {
    $idProducto = $_POST['id_producto'];
    $idCliente = $_POST['id_cliente'];
    $cantidad = $_POST['cantidad'];
    $fechaVenta = $_POST['fecha_venta'];

    // Obtener el precio de venta del producto y el stock_actual
    $productoQuery = $conn->prepare("SELECT precio_venta, stock_actual FROM productos WHERE id_producto = ?");
    $productoQuery->bind_param("i", $idProducto);
    $productoQuery->execute();
    $resultado = $productoQuery->get_result();

    if ($resultado->num_rows > 0) {
        $producto = $resultado->fetch_assoc();
        $precioVenta = $producto['precio_venta'];
        $stock_actual = $producto['stock_actual'];
    } else {
        echo "Error: Producto no encontrado.";
        exit;
    }

    // Validación básica de datos
    if (!is_numeric($cantidad) || $cantidad <= 0 || !is_numeric($precioVenta)) {
        echo "Error: Datos incorrectos.";
        exit;
    }

    // Verificar si hay suficiente stock
    if ($cantidad > $stock_actual) {
        echo "Error: No hay suficiente stock.";
        exit;
    }

    // Calcular el total de la venta (cantidad * precio)
    $total = $cantidad * $precioVenta;

    // Iniciar la transacción para asegurar la integridad
    $conn->begin_transaction();

    try {
        // Insertar la venta en la base de datos
        $stmt = $conn->prepare("INSERT INTO ventas (id_producto, id_cliente, cantidad, fecha_venta, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $idProducto, $idCliente, $cantidad, $fechaVenta, $total);

        if (!$stmt->execute()) {
            throw new Exception("Error al registrar la venta.");
        }

        // Actualizar el stock del producto en la tabla productos
        $nuevoStock = $stock_actual - $cantidad;
        $updateStockStmt = $conn->prepare("UPDATE productos SET stock_actual = ? WHERE id_producto = ?");
        $updateStockStmt->bind_param("ii", $nuevoStock, $idProducto);

        if (!$updateStockStmt->execute()) {
            throw new Exception("Error al actualizar el stock del producto.");
        }

        // Confirmar la venta
        $conn->commit();
        echo "Venta registrada correctamente.";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// Mostrar lista de ventas
$ventasQuery = $conn->query("SELECT v.id_venta, p.nombre_producto, c.nombre_completo AS cliente, v.cantidad, v.total, v.fecha_venta
                             FROM ventas v
                             JOIN productos p ON v.id_producto = p.id_producto
                             JOIN clientes c ON v.id_cliente = c.id_cliente");

$ventas = [];
while ($row = $ventasQuery->fetch_assoc()) {
    $ventas[] = $row;
}

// Generar la factura en PDF
if (isset($_GET['generar_factura'])) {
    $idVenta = $_GET['generar_factura'];

    // Obtener los detalles de la venta
    $stmt = $conn->prepare("SELECT v.id_venta, p.nombre_producto, c.nombre_completo AS cliente, v.cantidad, v.fecha_venta, v.total, c.direccion, c.nif
                            FROM ventas v
                            JOIN productos p ON v.id_producto = p.id_producto
                            JOIN clientes c ON v.id_cliente = c.id_cliente
                            WHERE v.id_venta = ?");
    $stmt->bind_param("i", $idVenta);
    $stmt->execute();
    $result = $stmt->get_result();
    $venta = $result->fetch_assoc();

    // Si la venta existe, generamos la factura en PDF
    if ($venta) {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, "Factura de Venta", 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, "ID Venta: " . $venta['id_venta'], 0, 1);
        $pdf->Cell(50, 10, "Cliente: " . $venta['cliente'], 0, 1);
        $pdf->Cell(50, 10, "Producto: " . $venta['nombre_producto'], 0, 1);
        $pdf->Cell(50, 10, "Cantidad: " . $venta['cantidad'], 0, 1);
        $pdf->Cell(50, 10, "Total: " . $venta['total'], 0, 1);
        $pdf->Cell(50, 10, "Fecha: " . $venta['fecha_venta'], 0, 1);
        $pdf->Output();
    }
}
include('ventas.html');
?>
