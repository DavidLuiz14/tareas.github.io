<?php
require('../conexion.php');
session_start();

$nombre_usuario = $_SESSION['username'];

if (!$nombre_usuario) {
    header('Location: ../index.php');
    exit();
}

// Actualizar el estado del pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido']) && isset($_POST['estado'])) {
    $idPedido = $_POST['id_pedido'];
    $estado = $_POST['estado'];

    // Verificar el estado actual del pedido
    $currentStateQuery = $conn->prepare("SELECT estado FROM pedidos WHERE id_pedido = ?");
    $currentStateQuery->bind_param("i", $idPedido);
    $currentStateQuery->execute();
    $result = $currentStateQuery->get_result();

    if ($result->num_rows > 0) {
        $currentState = $result->fetch_assoc()['estado'];

        // Verificar si el pedido ya está pagado
        if ($currentState === 'pagado') {
            echo "Este pedido ya ha sido pagado y no puede ser modificado.";
        } else {
            // Continuar con la actualización del estado
            if ($estado === 'pagado') {
                $updateQuery = $conn->prepare("UPDATE pedidos SET estado = 'pagado' WHERE id_pedido = ?");
                $updateQuery->bind_param("i", $idPedido);

                if ($updateQuery->execute()) {
                    $detalleQuery = $conn->query("SELECT d.id_producto, d.cantidad, p.id_cliente, d.precio_unitario
                                                  FROM detalles_pedido d
                                                  JOIN pedidos p ON d.id_pedido = p.id_pedido
                                                  WHERE p.id_pedido = '$idPedido'");

                    while ($detalle = $detalleQuery->fetch_assoc()) {
                        $idProducto = $detalle['id_producto'];
                        $cantidad = $detalle['cantidad'];
                        $idCliente = $detalle['id_cliente'];
                        $precioUnitario = $detalle['precio_unitario'];
                        $totalProducto = $cantidad * $precioUnitario;

                        $insertVentaQuery = $conn->prepare("INSERT INTO ventas (id_producto, id_cliente, cantidad, total) 
                                                            VALUES (?, ?, ?, ?)");
                        $insertVentaQuery->bind_param("iiid", $idProducto, $idCliente, $cantidad, $totalProducto);

                        if (!$insertVentaQuery->execute()) {
                            echo "Error al registrar la venta en la tabla ventas.";
                        }
                    }
                } else {
                    echo "Error al actualizar el estado del pedido.";
                }
            } else {
                $updateQuery = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
                $updateQuery->bind_param("si", $estado, $idPedido);

                if (!$updateQuery->execute()) {
                    echo "Error al actualizar el estado del pedido.";
                }
            }
        }
    } else {
        echo "Pedido no encontrado.";
    }
}

include('ver_pedidos.html');
?>
