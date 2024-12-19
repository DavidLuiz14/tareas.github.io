<?php
include('conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = $_POST['nombre_usuario'];
    $password = $_POST['password'];
    $rol = $_POST['rol'];

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Comienza la transacción
    $conn->begin_transaction();
 
    try {
        // Insertar en tabla usuarios
        $sql_usuarios = "INSERT INTO usuarios (nombre_usuario, contrasena, rol) VALUES (?, ?, ?)";
        $stmt_usuarios = $conn->prepare($sql_usuarios);
        $stmt_usuarios->bind_param("sss", $nombre_usuario, $password_hash, $rol);
        $stmt_usuarios->execute();

        // Si el rol es cliente, insertar en la tabla clientes
        if ($rol === "cliente") {
            $nombre_completo = $_POST['nombre_completo'];
            $telefono = $_POST['telefono'];
            $correo = $_POST['correo'];
            $direccion = $_POST['direccion'];
            $nif = $_POST['nif'];
            $nombre_usuario = $_POST['nombre_usuario']; 

            $sql_clientes = "INSERT INTO clientes (nombre_completo, telefono, correo, direccion, nif, nombre_usuario) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_clientes = $conn->prepare($sql_clientes);
            $stmt_clientes->bind_param("ssssss", $nombre_completo, $telefono, $correo, $direccion, $nif, $nombre_usuario);
            $stmt_clientes->execute();
        }

        // Confirmar transacción
        $conn->commit();
        echo "Registro exitoso. <a href='index.php'>Iniciar sesión</a>";
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        echo "Error al registrar el usuario: " . $e->getMessage();
    }
}
include('registro.html');
?>
