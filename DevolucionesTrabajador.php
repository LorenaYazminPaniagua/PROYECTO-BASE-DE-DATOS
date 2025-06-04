<?php
session_start();
if (!isset($_SESSION['id'])) {
    die("Acceso denegado. Debes iniciar sesión.");
}

$empleadoId = intval($_SESSION['id']);
$mysqli = new mysqli('localhost', 'root', '', 'HerreriaUG');
if ($mysqli->connect_errno) {
    die("Error de conexión a la base de datos: " . $mysqli->connect_error);
}

if (!$mysqli->query("SET @id_empleado_sesion := $empleadoId")) {
    die("Error al establecer variable de sesión en MySQL: " . $mysqli->error);
}

$mensaje = '';
$productosVenta = [];
$ventaSeleccionada = intval($_POST['venta_id'] ?? 0);
$fechaSeleccionada = $_POST['fecha'] ?? '';
$tipoDevolucion = $_POST['tipo_devolucion'] ?? '';
$productoSeleccionado = intval($_POST['producto_id'] ?? 0);
$cantidadDevolver = intval($_POST['cantidad'] ?? 0);

function obtenerVentas($mysqli, $empleadoId, $fecha) {
    $ventas = [];
    $sql = "SELECT v.idVenta, GROUP_CONCAT(p.Nombre SEPARATOR ', ') AS NombreProductos
            FROM Ventas v
            JOIN DetalleVenta dv ON v.idVenta = dv.idVenta
            JOIN Productos p ON dv.idProducto = p.idProducto
            WHERE DATE(v.Fecha) = ? AND v.idEmpleado = ? AND v.Estatus != 'Cancelada'
            GROUP BY v.idVenta
            ORDER BY v.idVenta";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('si', $fecha, $empleadoId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $ventas[] = $row;
    }
    $stmt->close();
    return $ventas;
}

function obtenerProductosVenta($mysqli, $idVenta) {
    $productos = [];
    $sql = "SELECT dv.idProducto, p.Nombre AS NombreProducto, dv.Cantidad
            FROM DetalleVenta dv
            JOIN Productos p ON dv.idProducto = p.idProducto
            WHERE dv.idVenta = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $idVenta);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $productos[] = $row;
    }
    $stmt->close();
    return $productos;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['devolver'])) {
    if (!$fechaSeleccionada) {
        $mensaje = "Seleccione una fecha válida.";
    } elseif (!$ventaSeleccionada) {
        $mensaje = "Seleccione una venta válida.";
    } elseif (!in_array($tipoDevolucion, ['pieza', 'venta'])) {
        $mensaje = "Seleccione un tipo de devolución válido.";
    } else {
        try {
            if ($tipoDevolucion === 'pieza') {
                if (!$productoSeleccionado || $cantidadDevolver <= 0) {
                    $mensaje = "Seleccione un producto y una cantidad válida para devolver.";
                } else {
                    $stmt = $mysqli->prepare("CALL DevolverProductoIndividual(?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param('iii', $ventaSeleccionada, $productoSeleccionado, $cantidadDevolver);
                        $stmt->execute();
                        $stmt->close();
                        while ($mysqli->more_results() && $mysqli->next_result()) {
                            $mysqli->use_result();
                        }
                        $mensaje = "Producto devuelto correctamente.";
                    } else {
                        $mensaje = "Error en prepare(): " . $mysqli->error;
                    }
                }
            } else {
                $stmt = $mysqli->prepare("CALL DevolverVentaCompleta(?)");
                if ($stmt) {
                    $stmt->bind_param('i', $ventaSeleccionada);
                    $stmt->execute();
                    $stmt->close();
                    while ($mysqli->more_results() && $mysqli->next_result()) {
                        $mysqli->use_result();
                    }
                    $mensaje = "Venta completa devuelta correctamente.";
                } else {
                    $mensaje = "Error en prepare(): " . $mysqli->error;
                }
            }
        } catch (mysqli_sql_exception $e) {
            $mensaje = "Error al procesar la devolución: " . $e->getMessage();
        }
    }
}

if ($fechaSeleccionada && $ventaSeleccionada && $tipoDevolucion === 'pieza') {
    $productosVenta = obtenerProductosVenta($mysqli, $ventaSeleccionada);
}

$ventas = [];
if ($fechaSeleccionada) {
    $ventas = obtenerVentas($mysqli, $empleadoId, $fechaSeleccionada);
}

$enlaceInicio = 'Inicio.php';
if (isset($_SESSION['rol'])) {
    switch ($_SESSION['rol']) {
        case 'Administrador':
            $enlaceInicio = 'InicioAdministradores.php';
            break;
        case 'Cajero':
            $enlaceInicio = 'InicioTrabajadores.php';
            break;
    }
}

$mysqli->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Devoluciones Cajeros</title>
    <link rel="stylesheet" href="DevolucionesTrabajador2.css" />
    <link rel="icon" type="image/jpeg" href="Imagenes2/DESTORNILLADOR.jpg">
    <script>
        function mostrarProductos() {
            const tipo = document.getElementById('tipoDevolucion').value;
            const productosDiv = document.getElementById('productosDiv');
            if (tipo === 'pieza') {
                productosDiv.style.display = 'block';
                document.getElementById('producto_id').required = true;
                document.getElementById('cantidad').required = true;
            } else {
                productosDiv.style.display = 'none';
                document.getElementById('producto_id').required = false;
                document.getElementById('cantidad').required = false;
            }
        }
        window.onload = mostrarProductos;
    </script>
</head>
<body>
    <header class="titulin">
        <h1>HERRERIA "METALURGIA 360"</h1>
        <label for="menu-toggle" class="btn-atras-contenedor">
            <a href="CAJEROS.html">
            <img src="Imagenes2/regresar.jpg" alt="Menú" class="boton-atras" />
            </a>
        </label>
    </header>
<main>
    <section class="izquierda"></section>
    <section class="centro">
        <label for="menu-toggle" class="btn-atras-contenedor">
            <img src="Imagenes/Menu.png" alt="Atrás" class="boton-atras" id="boton-menu">
        </label>

        <div class="log">
            <div class="login">
                <?php if ($mensaje): ?>
                    <p style="color: red; font-weight: bold;"><?= htmlspecialchars($mensaje) ?></p>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="input-group">
                        <input type="date" id="fecha" name="fecha" required
                               value="<?= htmlspecialchars($fechaSeleccionada) ?>"
                               onchange="this.form.submit()" />
                        <label for="fecha">Fecha:</label>
                    </div>

                    <div class="input-group">
                        <select id="venta_id" name="venta_id" required onchange="this.form.submit()">
                            <option value="" disabled <?= $ventaSeleccionada ? '' : 'selected' ?>>Seleccione la venta</option>
                            <?php foreach ($ventas as $v): ?>
                                <option value="<?= $v['idVenta'] ?>" <?= ($v['idVenta'] == $ventaSeleccionada) ? 'selected' : '' ?>>
                                    Venta #<?= $v['idVenta'] ?> - Producto(s): <?= htmlspecialchars($v['NombreProductos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="venta_id">Ventas del día:</label>
                    </div>

                    <div class="input-group">
                        <select id="tipoDevolucion" name="tipo_devolucion" required onchange="mostrarProductos(); this.form.submit();">
                            <option value="" disabled <?= empty($tipoDevolucion) ? 'selected' : '' ?>>Seleccione tipo de devolución</option>
                            <option value="pieza" <?= ($tipoDevolucion === 'pieza') ? 'selected' : '' ?>>Por pieza</option>
                            <option value="venta" <?= ($tipoDevolucion === 'venta') ? 'selected' : '' ?>>Por venta completa</option>
                        </select>
                        <label for="tipoDevolucion">Tipo de devolución:</label>
                    </div>

                    <div class="input-group" id="productosDiv" style="display:none;">
                        <select id="producto_id" name="producto_id">
                            <option value="" disabled selected>Seleccione un producto</option>
                            <?php foreach ($productosVenta as $prod): ?>
                                <option value="<?= $prod['idProducto'] ?>">
                                    <?= htmlspecialchars($prod['NombreProducto']) ?> (Cantidad: <?= $prod['Cantidad'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="producto_id">Producto a devolver:</label>

                        <input type="number" name="cantidad" id="cantidad" min="1" placeholder="Cantidad a devolver" />
                    </div>

                    <button type="submit" name="devolver">Procesar devolución</button>
                </form>
            </div>
        </div>
    </section>
    <section class="derecha"></section>
</main>


</body>
</html>
