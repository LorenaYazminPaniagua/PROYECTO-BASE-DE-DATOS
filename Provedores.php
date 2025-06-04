<?php
session_start(); // Asegúrate de iniciar sesión para acceder a $_SESSION

// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

// Crear la conexión
$conexion = new mysqli($host, $usuario, $clave, $bd);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// No parece que necesites @empleado_id para esta consulta, pero si quieres mantenerlo:
$empleado_id = $_SESSION['id'] ?? 1;
$conexion->query("SET @empleado_id = $empleado_id");

// Consulta a la vista de todos los proveedores
$sql = "SELECT * FROM Vista_Todos_Proveedores";
$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error al consultar los proveedores: " . $conexion->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proveedores</title>
    <link rel="stylesheet" type="text/css" href="Provedores2.css">
    <link rel="icon" type="image/jpg" href="Imagenes2/DESTORNILLADOR.jpg">
</head>
<body>
    <div class="overlay"></div>

    <header class="titulo">
        <!-- Botón de atrás -->
        <div class="boton-atras-contenedor">
            <a href="ADMINISTRADORES.html">
            <img src="Imagenes2/regresar.jpg" alt="Menú" class="boton-atras" />
            </a>
        </div>

        <h1>HERRERIA "METALURGIA 360"</h1>
    </header>

    <main class="contenido">
        <section class="container">
            <!-- Contenedor para la tabla -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($proveedor = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($proveedor['Nombre']); ?></td>
                                <td>
                                    <div class="acciones">
                                        <!-- Enlace para editar el proveedor -->
                                        <a href="EditarProveedor.php?idProveedor=<?php echo urlencode($proveedor['idProveedor']); ?>">
                                            <img src="Imagenes2/editar.jpg" alt="Editar" class="mi-imagen">
                                        </a>
                                        <!-- Enlace para eliminar el proveedor -->
                                        <a href="BorrarProveedor.php?idProveedor=<?php echo urlencode($proveedor['idProveedor']); ?>">
                                            <img src="Imagenes2/menos.jpg" alt="Eliminar" class="mi-imagen">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Contenedor para el botón de agregar proveedor -->
            <div class="boton-agregar-container">
                <a href="AgregarProveedor.php">
                    <img src="Imagenes2/Mas.jpg" alt="Agregar Proveedor" class="agregar">
                </a>
                <a href="pdf4.php" target="_blank" title="Descargar"><img src="Imagenes2/descargar.jpg" alt="Agregar" class="agregar"></a>
            </div>
        </section>
    </main>
</body>
</html>
