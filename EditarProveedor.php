<?php
session_start(); // Inicia sesión para usar $_SESSION

// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

$conexion = new mysqli($host, $usuario, $clave, $bd);

// Verifica conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Establece variable para empleado si la necesitas (solo si la usas en algún lado)
$empleado_id = $_SESSION['id'] ?? 1;
$conexion->query("SET @empleado_id = $empleado_id");

// Verificar si se ha enviado el formulario
if (isset($_POST['Boton'])) {
    // Obtén idProveedor de la URL (GET)
    $idProveedor = $_GET['idProveedor'] ?? null;
    if (!$idProveedor) {
        die("ID de proveedor no especificado.");
    }

    // Obtener datos del formulario
    $nombre = $_POST['Nombre'] ?? '';

    // Preparar la llamada al procedimiento almacenado
    $stmt = $conexion->prepare("CALL ActualizarProveedor(?, ?)");
    if (!$stmt) {
        die("Error en la preparación del statement: " . $conexion->error);
    }

    // Liga parámetros: int, string
    $stmt->bind_param("is", $idProveedor, $nombre);

    if ($stmt->execute()) {
        // Redirige a la página de proveedores después de actualizar
        header("Location: Provedores.php");
        exit();
    } else {
        echo "Error al actualizar el proveedor: " . $stmt->error;
    }

    $stmt->close();
}

// Cargar datos del proveedor para mostrar en formulario
if (isset($_GET['idProveedor'])) {
    $idProveedor = $_GET['idProveedor'];

    // Preparar consulta para obtener datos actuales (usa prepared statement para seguridad)
    $stmt = $conexion->prepare("SELECT idProveedor, Nombre FROM Proveedores WHERE idProveedor = ?");
    if (!$stmt) {
        die("Error en la preparación del statement: " . $conexion->error);
    }
    $stmt->bind_param("i", $idProveedor);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        $proveedor = $resultado->fetch_assoc();
    } else {
        die("Proveedor no encontrado.");
    }

    $stmt->close();
} else {
    die("ID del proveedor no proporcionado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Actualizar Proveedor</title>
    <link rel="stylesheet" href="EditarProveedor2.css" />
    <link rel="icon" type="image/jpeg" href="Imagenes2/DESTORNILLADOR.jpg">
</head>
<body>
    <div class="titulin">
        <h1>HERRERIA "METALURGIA 360"</h1>
        <a href="Provedores.php">
            <img src="Imagenes2/regresar.jpg" alt="Botón Atrás" class="boton-atras" />
        </a>
    </div>
    <main>
        <section class="izquierda"></section>
    <section class="centro">
        <br><br>
        <div class="log">
            <div class="login">
                <form method="POST" action="">
                    <div class="titulo">
                        <h2>Editar Proveedor</h2>

                        
                    </div>

                <div class="input-group">
                    <input type="text" name="Nombre" required
                           value="<?php echo htmlspecialchars($proveedor['Nombre']); ?>" />
                    <label>Nombre</label>
                </div>

                    <button type="submit" class="Acceder" name="Boton">Actualizar</button>
                </form>
            </div>
        </div>
    </section>
    <section class="derecha"></section>
    </main>
</body>
</html>