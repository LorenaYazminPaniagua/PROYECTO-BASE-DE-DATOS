<?php
session_start();

$conn = new mysqli("localhost", "root", "", "HerreriaUG");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verificar'])) {
    $nombre = trim($_POST['Nombre']);
    $correo = trim($_POST['Correo']);
    $puesto = trim($_POST['Puesto']);
    $telefono = trim($_POST['Telefono']);

    $sql = "SELECT E.idEmpleado 
            FROM Empleados E
            JOIN Personas P ON E.idPersona = P.idPersona
            WHERE P.Nombre = ? AND P.Email = ? AND E.Puesto = ? AND P.Telefono = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = "Error al preparar la consulta: " . $conn->error;
    } else {
        $stmt->bind_param("ssss", $nombre, $correo, $puesto, $telefono);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $_SESSION['usuario_recuperar'] = $correo;
            $_SESSION['id'] = $row['idEmpleado'];  // Guardamos el idEmpleado en sesión
            header("Location: NuevaClave.php");
            exit();
        } else {
            $error = "Los datos no coinciden con ningún empleado.";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Clave</title>
    <link rel="stylesheet" type="text/css" href="OlvideClave2.css">
    <link rel="icon" type="image/jpg" href="Imagenes2/DESTORNILLADOR.jpg">
</head>
<body>
    <div class="titulin">
        <h1>HERRERIA "METALURGIA 360"</h1>
                                <a href="Login.php">
                            <img src="Imagenes2/regresar.jpg" alt="Botón Atrás" class="boton-atras">
                        </a>
    </div>
<main>
    <section class="izquierda"></section>
    <section class="centro">
        <div class="log">
            <div class="login">
                <form method="POST">
                    <div class="titulo">
                        <h2>Recuperar Clave</h2>
                    </div>

                    <?php if ($error): ?>
                        <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>

                    <div class="input-group">
                        <input type="text" name="Nombre" required placeholder=" ">
                        <label for="Nombre">Nombre</label>
                    </div>

                    <div class="input-group">
                        <input type="email" name="Correo" required placeholder=" ">
                        <label for="Correo">Correo</label>
                    </div>

                    <div class="input-group">
                        <input type="text" name="Puesto" required placeholder=" ">
                        <label for="Puesto">Puesto</label>
                    </div>

                    <div class="input-group">
                        <input type="text" name="Telefono" required placeholder=" ">
                        <label for="Telefono">Teléfono</label>
                    </div>

                    <button type="submit" name="verificar" class="Acceder">Verificar</button>
                </form>
            </div>
        </div>
    </section>
    <section class="derecha"></section>
</main>
</body>
</html>