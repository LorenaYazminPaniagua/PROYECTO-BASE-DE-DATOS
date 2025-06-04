<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: Login.php");
    exit();
}

// Datos de conexión
$host = "localhost";      // Nombre del contenedor Docker
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

// Crear conexión
$conn = new mysqli($host, $usuario, $clave, $bd);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el ID del proveedor desde GET
if (isset($_GET['id'])) {
    $idProveedor = intval($_GET['id']); // Asegurar que sea entero para seguridad

    // Preparar llamada al procedimiento almacenado
    $sql = "CALL EliminarProveedor(?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conn->error);
    }

    // Vincular parámetros
    $stmt->bind_param("i", $idProveedor);

    // Ejecutar el procedimiento
    if ($stmt->execute()) {
        echo "<p>Proveedor eliminado (estado cambiado a Inactivo) con éxito.</p>";
    } else {
        echo "<p>Error al eliminar proveedor: " . $stmt->error . "</p>";
    }

    $stmt->close();
} else {
    echo "<p>No se proporcionó ID de proveedor.</p>";
}

// Cerrar conexión
$conn->close();
?>
