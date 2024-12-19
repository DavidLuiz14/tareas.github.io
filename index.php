<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tienda de Ropa</title>
    <link rel="stylesheet"  href="styles/login.css">
</head>
<body>
    <div class="login-form">
        <h2>Iniciar Sesión</h2>
        <form action="procesar_login.php" method="POST">
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Iniciar sesión</button>
            <a href="registro.php">Crear cuenta</a>
        </form>
    </div>
</body>
</html>
 