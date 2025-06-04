<?php
session_start();

$host = "localhost";        // Host del contenedor o servidor
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

// Crear conexión
$conn = new mysqli($host, $usuario, $clave, $bd);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario
    $usuarioInput = $_POST['Nombre'] ?? '';
    $claveInput = $_POST['Clave'] ?? '';

    // Preparar consulta segura
    $sql = "SELECT * FROM Empleados WHERE Usuario = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("s", $usuarioInput);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Verificar contraseña
        if (password_verify($claveInput, $usuario['Contraseña'])) {
            // Guardar datos en sesión
            $_SESSION['id'] = $usuario['idEmpleado'];
            $_SESSION['usuario'] = $usuario['Usuario'];
            $_SESSION['rol'] = $usuario['Puesto'];

            // Redirigir según el puesto
            switch ($usuario['Puesto']) {
                case 'Administrador':
                    header("Location: ADMINISTRADORES.html");
                    exit();
                case 'Cajero':
                    header("Location: CAJEROS.html");
                    exit();
                case 'Agente de Venta':
                    header("Location: HacerPedido.php");
                    exit();
                default:
                    echo "Puesto no reconocido.";
            }
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
    <link rel="stylesheet" type="text/css" href="Login.css">
    <link rel="icon" type="image" href="Imagenes2/DESTORNILLADOR.jpg">
</head>
<body>
    <div class="titulin">
        <h1>HERRERIA "METALURGIA 360"</h1>
    </div>
    <main>
        <section class="centro">
            <div class="log">
                <div class="login">
                    <form method="POST" action="Login.php">
                        <div class="titulo">
                            <h2>Iniciar sesion</h2>
                        </div>

                        <!-- Campo Nombre -->
                        <div class="input-group">
                            <input type="text" name="Nombre" required placeholder=" ">
                            <label for="Nombre">Usuario</label>
                        </div>

                        <!-- Campo Clave -->
                        <div class="input-group">
                            <input type="password" name="Clave" required placeholder=" ">
                            <label for="Clave">Clave</label>
                        </div>

                        <!-- Botón de acceso -->
                        <button type="submit" name="Boton" class="Acceder">Acceder</button>

                        <!-- Opciones -->
                        <div class="Opciones">
                            <a href="Registro.php"><button type="button">Registro</button></a>
                            <a href="OlvideClave.php"><button type="button">Olvidé Clave</button></a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

</body>
</html>