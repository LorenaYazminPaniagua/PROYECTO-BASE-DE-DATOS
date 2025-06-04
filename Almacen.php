<?php
// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

// Crear conexión
$conexion = new mysqli($host, $usuario, $clave, $bd);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta la vista VistaEstadoInventario
$sql = "SELECT * FROM VistaProductosActivos";
$resultado = $conexion->query($sql);

// Verificar si hubo error en la consulta
if (!$resultado) {
    die("Error al consultar la vista: " . $conexion->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario</title>
    <link rel="stylesheet" type="text/css" href="Inventario.css">
    <link rel="icon" type="image/jpg" href="Imagenes2/DESTORNILLADOR.jpg">
</head>
<body>
    <div class="overlay"></div>

    <header class="titulo">
        <div class="boton-atras-contenedor">
            <a href="ADMINISTRADORES.html">
            <img src="Imagenes2/regresar.jpg" alt="Menú" class="boton-atras" />
            </a>
        </div>



        <h1>Herreria "METALURGIA 360"</h1>
    </header>

    <main class="contenido">
        <section class="container">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Proveedor</th>
                            <th>Stock</th>
                            <th>Precio Compra</th>
                            <th>Precio Venta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($producto = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['Producto']); ?></td>
                                <td><?php echo htmlspecialchars($producto['Categoria']); ?></td>
                                <td><?php echo htmlspecialchars($producto['Proveedor']); ?></td>
                                <td><?php echo htmlspecialchars($producto['Stock']); ?></td>
                                <td>$<?php echo number_format($producto['PrecioCompra'], 2); ?></td>
                                <td>$<?php echo number_format($producto['PrecioVenta'], 2); ?></td>
                                <td>
                                    <div class="acciones">
                                        <a href="EditarProducto.php?idProducto=<?php echo urlencode($producto['idProducto']); ?>">
                                            <img src="Imagenes2/Editar.jpg" alt="Editar" class="mi-imagen">
                                        </a>
                                        <a href="BorrarProducto.php?idProducto=<?php echo urlencode($producto['idProducto']); ?>">
                                            <img src="Imagenes2/menos.jpg" alt="Eliminar" class="mi-imagen">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="boton-agregar-container">
                     <a href="AgregarProducto.php" title="Agregar"><img src="Imagenes2/Mas.jpg" alt="Agregar" class="agregar"></a>
                     <a href="AlmacenInactivos.php" title="Agregar"><img src="Imagenes2/almaceninactivo.jpg" alt="Agregar" class="agregar"></a>
                     <a href="pdf3.php" target="_blank" title="Descargar"><img src="Imagenes2/descargar.jpg" alt="Agregar" class="agregar"></a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
