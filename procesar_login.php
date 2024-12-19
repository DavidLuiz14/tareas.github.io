<?php
require('conexion.php'); 
session_start();

//$username = $_SESSION['username']; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];


    // Consulta para verificar el usuario
    $sql = "SELECT * FROM usuarios WHERE nombre_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($password, $user['contrasena'])) {
            // Almacenar datos en la sesión
            $_SESSION['username'] = $user['nombre_usuario'];
            $_SESSION['role'] = $user['rol'];

            // Redirigir según el rol
            if ($user['rol'] == 'administrador') {
                header('Location: admin/dashboard_admin.php');
                exit();
            } else if($user['rol'] === 'cliente') {
                // Consulta los datos del cliente
                $sql_cliente = "SELECT * FROM clientes WHERE nombre_usuario = ?";
                $stmt_cliente = $conn->prepare($sql_cliente);
                $stmt_cliente->bind_param("s", $username);
                $stmt_cliente->execute();
                $result_cliente = $stmt_cliente->get_result();

                if ($result_cliente->num_rows > 0) {
                    // Si se encuentra el cliente, redirige al dashboard de clientes
                    header('Location: cliente/dashboard_cliente.php');
                    exit();
                } else {
                    echo "No se encontraron datos del cliente.";
                }
            }
        } else {
            echo "Usuario o contraseña incorrectos.";
        }
    } else {
        echo "Usuario o contraseña incorrectos.";
    }
}
?>
