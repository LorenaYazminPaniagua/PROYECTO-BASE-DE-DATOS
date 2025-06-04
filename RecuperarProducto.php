<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: Login.php");
    exit();
}

// Obtener ID del empleado
$idEmpleado = $_SESSION['id'];

// Datos de conexión a la base de datos
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

// Conexión a la base de datos
$conexion = new mysqli($host, $usuario, $clave, $bd);

// Verificar conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Asignar ID del empleado como variable de sesión SQL (opcional)
$conexion->query("SET @id_empleado_sesion := $idEmpleado");

// Verificar si se envió el ID del producto a eliminar
if (isset($_GET['idProducto']) && is_numeric($_GET['idProducto'])) {
    $idProducto = intval($_GET['idProducto']);

    // Llamar al procedimiento almacenado EliminarProducto
    $sql = "CALL RecuperarProducto(?)";
    $stmt = $conexion->prepare($sql);

    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    $stmt->bind_param("i", $idProducto);

    if ($stmt->execute()) {
        $stmt->close();
        $conexion->close();
        // Redireccionar a Almacen.php tras éxito
        header("Location: AlmacenInactivos.php");
        exit();
    } else {
        $error = $stmt->error;
        $stmt->close();
        $conexion->close();
        die("Error al ejecutar el procedimiento: " . $error);
    }
} else {
    $conexion->close();
    header("Location: Almacen.php"); // Redirigir si no se recibe ID válido
    exit();
}
?>