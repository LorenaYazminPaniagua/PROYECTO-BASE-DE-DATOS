<?php
session_start();
$conn = new mysqli("localhost", "root", "", "HerreriaUG");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if (!isset($_SESSION['usuario_recuperar']) || !isset($_SESSION['id'])) {
    header("Location: OlvideClave.php");
    exit();
}

$mensaje = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nuevaClave = trim($_POST['nuevaClave']);

    if (strlen($nuevaClave) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $idEmpleado = (int)$_SESSION['id'];  // Obtener idEmpleado de la sesión
        $conn->query("SET @id_empleado_sesion := $idEmpleado"); // Asignar variable para el trigger

        $hash = password_hash($nuevaClave, PASSWORD_DEFAULT);

        // Actualizar contraseña directamente en Empleados usando Usuario
        $sql = "UPDATE Empleados SET Contraseña = ? WHERE Usuario = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = "Error al preparar la consulta: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $hash, $_SESSION['usuario_recuperar']);
            if ($stmt->execute()) {
                unset($_SESSION['usuario_recuperar']);
                unset($_SESSION['id']); // Limpia el id también para seguridad
                $mensaje = "Contraseña actualizada correctamente.";
            } else {
                $error = "Ocurrió un error al actualizar la contraseña: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Establecer Nueva Contraseña</title>
    <link rel="stylesheet" href="OlvideClave2.css">
</head>
<body>
<main>
    <section class="centro">
        <div class="login">
            <?php if ($mensaje): ?>
                <p style="color: green; text-align:center;"><?= htmlspecialchars($mensaje) ?> <a href="Login.php">Iniciar sesión</a></p>
            <?php else: ?>
                <form method="POST">
                    <div class="titulo">
                        <h2>Establece tu nueva contraseña</h2>
                    </div>

                    <?php if ($error): ?>
                        <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>

                    <div class="input-group">
                        <input type="password" name="nuevaClave" required placeholder=" ">
                        <label for="nuevaClave">Nueva contraseña</label>
                    </div>

                    <button type="submit" class="Acceder">Actualizar</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>