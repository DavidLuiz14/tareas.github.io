<?php
require('../conexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/ProyectoV2.0/lib/fpdf/fpdf.php');

session_start();

$nombre_usuario = $_SESSION['username'];

if (!$nombre_usuario) {
    header('Location: ../index.php');
    exit();
} 

// Procesar la inserción de una compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'])) {
    $idProducto = $_POST['id_producto'];
    $idProveedor = $_POST['id_proveedor'];
    $cantidad = $_POST['cantidad'];
    $fechaCompra = $_POST['fecha_compra'];

   // echo "Fecha recibida: " . $_POST['fecha_compra'];


    // Obtener el precio de compra del producto y el stock_actual
    $productoQuery = $conn->prepare("SELECT precio_compra, stock_actual FROM productos WHERE id_producto = ?");
    $productoQuery->bind_param("i", $idProducto);
    $productoQuery->execute();
    $resultado = $productoQuery->get_result();

    if ($resultado->num_rows > 0) {
        $producto = $resultado->fetch_assoc();
        $precioCompra = $producto['precio_compra'];
        $stock_actual = $producto['stock_actual'];
    } else {
        echo "Error: Producto no encontrado.";
        exit;
    }

    // Validación básica de datos
    if (!is_numeric($cantidad) || $cantidad <= 0 || !is_numeric($precioCompra)) {
        echo "Error: Datos incorrectos.";
        exit;
    }

    // Calcular el total de la compra (cantidad * precio)
    $total = $cantidad * $precioCompra;

    // Iniciar la transacción para asegurar la integridad
    $conn->begin_transaction();

    try {
        // Insertar la compra en la base de datos
        $stmt = $conn->prepare("INSERT INTO compras (id_producto, id_proveedor, cantidad, fecha_compra, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $idProducto, $idProveedor, $cantidad, $fechaCompra, $total);

        if (!$stmt->execute()) {
            throw new Exception("Error al registrar la compra.");
        }

          // Actualizar el stock del producto en la tabla productos
        $nuevoStock = $stock_actual + $cantidad;
        $updateStockStmt = $conn->prepare("UPDATE productos SET stock_actual = ? WHERE id_producto = ?");
        $updateStockStmt->bind_param("ii", $nuevoStock, $idProducto);

        if (!$updateStockStmt->execute()) {
            throw new Exception("Error al actualizar el stock del producto.");
        }

        // Confirmar la compra
        $conn->commit();
        echo "Compra registrada correctamente.";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// Mostrar lista de compras
$comprasQuery = $conn->query("SELECT c.id_compra, p.nombre_producto, pr.nombre_completo AS proveedor, c.cantidad, c.total, c.fecha_compra
                              FROM compras c
                              JOIN productos p ON c.id_producto = p.id_producto
                              JOIN proveedores pr ON c.id_proveedor = pr.id_proveedor");

$compras = [];
while ($row = $comprasQuery->fetch_assoc()) {
    $compras[] = $row;
}

// Generar la factura en PDF
if (isset($_GET['generar_factura'])) {
    $idCompra = $_GET['generar_factura'];

    // Obtener los detalles de la compra
    $stmt = $conn->prepare("SELECT c.id_compra, p.nombre_producto, pr.nombre_completo AS proveedor, c.cantidad, c.fecha_compra, c.total, pr.nif, pr.direccion
                            FROM compras c
                            JOIN productos p ON c.id_producto = p.id_producto
                            JOIN proveedores pr ON c.id_proveedor = pr.id_proveedor
                            WHERE c.id_compra = ?");
    $stmt->bind_param("i", $idCompra);
    $stmt->execute();
    $result = $stmt->get_result();
    $compra = $result->fetch_assoc();

    // Si la compra existe, generamos la factura en PDF
    if ($compra) {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Factura de Compra', 0, 1, 'C');

        // Datos del proveedor
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, 'ID Compra: ' . $compra['id_compra']);
        $pdf->Ln();
        $pdf->Cell(50, 10, 'Producto: ' . $compra['nombre_producto']);
        $pdf->Ln();
        $pdf->Cell(50, 10, 'Proveedor: ' . $compra['proveedor']);
        $pdf->Ln();
        $pdf->Cell(50, 10, 'Cantidad: ' . $compra['cantidad']);
        $pdf->Ln();
        $pdf->Cell(50, 10, 'Total: ' . $compra['total']);
        $pdf->Ln();
        $pdf->Cell(50, 10, 'Fecha de Compra: ' . $compra['fecha_compra']);
        $pdf->Ln();
        $pdf->Cell(50, 10, 'Direccion: ' . $compra['direccion']);
        $pdf->Ln();
        $pdf->Cell(50, 10, 'NIF: ' . $compra['nif']);
        
        // Salvar el archivo
        $pdf->Output('D', 'factura_compra_' . $compra['id_compra'] . '.pdf');
    } else {
        echo "Compra no encontrada.";
    }
}
include('compras.html');
?>
