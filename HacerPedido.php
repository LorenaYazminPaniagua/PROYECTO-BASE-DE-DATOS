<?php
session_start();
$conn = new mysqli("localhost", "root", "", "HerreriaUG");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$idEmpleado = $_SESSION['id'] ?? 1;
$id_empleado = (int)$idEmpleado;

// Establecer variable de sesión para usar en procedimientos o triggers
$conn->query("SET @id_empleado_sesion := $id_empleado");

$productos_registrados = [];
$clientes = [];
$mensaje_error = '';
$mensaje_exito = '';

// Obtener lista de clientes activos
$sql = "SELECT c.idCliente AS id, p.Nombre
        FROM Clientes c
        INNER JOIN Personas p ON c.idPersona = p.idPersona
        WHERE p.Estatus = 1";
$result = $conn->query($sql);
if ($result) {
    $clientes = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Error al obtener clientes: " . $conn->error);
}

$cliente_id = $_POST['cliente_id'] ?? '';
$tipo_cliente = $_POST['tipo_cliente'] ?? 'normal';
$termino_busqueda = $_POST['termino'] ?? '';
$es_credito = isset($_POST['es_credito']);

// Limpiar resultados pendientes
function limpiarResultadosPendientes($conn) {
    while ($conn->more_results()) {
        $conn->next_result();
        if ($result = $conn->store_result()) {
            $result->free();
        }
    }
}

// Buscar producto
if (isset($_POST['buscar'])) {
    $termino = trim($termino_busqueda);
    if ($termino !== '') {
        $producto = null;

        // Buscar por nombre primero
        $stmt = $conn->prepare("CALL BuscarProductoPorNombre(?)");
        if ($stmt === false) {
            die("Error en prepare BuscarProductoPorNombre: " . $conn->error);
        }
        $stmt->bind_param("s", $termino);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $producto = $resultado->fetch_assoc();
        $stmt->close();
        limpiarResultadosPendientes($conn);

        // Si no se encontró, buscar por código de barras
        if (!$producto) {
            $stmt = $conn->prepare("CALL BuscarProductoPorCodigoBarras(?)");
            if ($stmt === false) {
                die("Error en prepare BuscarProductoPorCodigoBarras: " . $conn->error);
            }
            $stmt->bind_param("s", $termino);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $producto = $resultado->fetch_assoc();
            $stmt->close();
            limpiarResultadosPendientes($conn);
        }

        if ($producto) {
            $cantidad = 1;
            $stmt = $conn->prepare("CALL AgregarAlCarrito(?, ?, ?)");
            if ($stmt === false) {
                die("Error en prepare AgregarAlCarrito: " . $conn->error);
            }
            $stmt->bind_param("iii", $id_empleado, $producto['idProducto'], $cantidad);
            if (!$stmt->execute()) {
                $mensaje_error = "No se pudo agregar el producto al carrito: " . $stmt->error;
            } else {
                $mensaje_exito = "Producto agregado al carrito correctamente.";
            }
            $stmt->close();
            limpiarResultadosPendientes($conn);
        } else {
            $mensaje_error = "Producto no encontrado por nombre ni por código de barras.";
        }
    } else {
        $mensaje_error = "Ingrese un término de búsqueda válido.";
    }
}

// Sumar cantidad
if (isset($_POST['sumar'])) {
    $idProducto = (int)($_POST['producto_id'] ?? 0);
    if ($idProducto > 0) {
        $stmt = $conn->prepare("CALL SumarCantidadProductoCarrito(?, ?)");
        if ($stmt === false) {
            die("Error en prepare SumarCantidadProductoCarrito: " . $conn->error);
        }
        $stmt->bind_param("ii", $id_empleado, $idProducto);
        if (!$stmt->execute()) {
            $mensaje_error = "Error al sumar cantidad: " . $stmt->error;
        }
        $stmt->close();
        limpiarResultadosPendientes($conn);
    }
}

// Restar cantidad
if (isset($_POST['restar'])) {
    $idProducto = (int)($_POST['producto_id'] ?? 0);
    if ($idProducto > 0) {
        $stmt = $conn->prepare("CALL RestarCantidadProductoCarrito(?, ?)");
        if ($stmt === false) {
            die("Error en prepare RestarCantidadProductoCarrito: " . $conn->error);
        }
        $stmt->bind_param("ii", $id_empleado, $idProducto);
        if (!$stmt->execute()) {
            $mensaje_error = "Error al restar cantidad: " . $stmt->error;
        }
        $stmt->close();
        limpiarResultadosPendientes($conn);
    }
}

// Obtener carrito
$stmt = $conn->prepare("CALL sp_ObtenerCarritoPorEmpleado(?)");
if ($stmt === false) {
    die("Error en prepare sp_ObtenerCarritoPorEmpleado: " . $conn->error);
}
$stmt->bind_param("i", $id_empleado);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $productos_registrados[] = [
        'idProducto' => $row['idProducto'],
        'nombre' => $row['Nombre'],
        'cantidad' => $row['Cantidad'],
        'precioProducto' => $row['PrecioVenta'],
        'precioVenta' => $row['Total'],
    ];
}
$stmt->close();
limpiarResultadosPendientes($conn);

// Procesar pedido
if (isset($_POST['procesar'])) {
    if (empty($cliente_id)) {
        $mensaje_error = "Debe seleccionar un cliente para procesar el pedido.";
    } elseif (empty($productos_registrados)) {
        $mensaje_error = "No hay productos en el carrito para procesar.";
    } else {
        $fecha_actual = date('Y-m-d');
        $stmt = $conn->prepare("CALL RegistrarPedido(?, ?, ?)");
        if ($stmt === false) {
            die("Error en prepare RegistrarPedido: " . $conn->error);
        }
        $stmt->bind_param("iis", $cliente_id, $id_empleado, $fecha_actual);
        if (!$stmt->execute()) {
            $mensaje_error = "Error al ejecutar RegistrarPedido: " . $stmt->error;
        } else {
            $mensaje_exito = "Pedido registrado correctamente.";
            $stmt->close();
            limpiarResultadosPendientes($conn);

            $stmt = $conn->prepare("CALL VaciarCarritoEmpleado(?)");
            if ($stmt) {
                $stmt->bind_param("i", $id_empleado);
                $stmt->execute();
                $stmt->close();
                limpiarResultadosPendientes($conn);
            }
            $productos_registrados = [];
        }
    }
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>AgentesDeVentas</title>
    <link rel="stylesheet" href="HacerPedido2.css" />
    <link rel="icon" type="image/jpg" href="Imagenes2/DESTORNILLADOR.jpg" />
</head>
<body>
    <div class="overlay"></div>
    <header class="titulo">
        <div class="boton-atras-contenedor">
            <a href="Login.php" title="Agregar"><img src="Imagenes2/regresar.jpg" alt="Menú" class="boton-atras" /></a>
        </div>
        <h1>HERRERIA "METALURGIA 360"</h1>
    </header>
    <main class="contenido">
        <section class="container">

            <?php if ($mensaje_error): ?>
                <div style="color:red; font-weight:bold; margin-bottom:10px;"><?= htmlspecialchars($mensaje_error) ?></div>
            <?php endif; ?>
            <?php if ($mensaje_exito): ?>
                <div style="color:green; font-weight:bold; margin-bottom:10px;"><?= htmlspecialchars($mensaje_exito) ?></div>
            <?php endif; ?>

            <form method="POST" class="barcode-form">
                <input type="text" name="termino" placeholder="Nombre o código de barras" value="<?= htmlspecialchars($termino_busqueda) ?>" />
                <select name="cliente_id" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= (int)$cliente['id'] ?>" <?= ($cliente_id == $cliente['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cliente['Nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>
                    <input type="checkbox" name="es_credito" <?= $es_credito ? 'checked' : '' ?> />
                    Crédito
                </label>
                <button type="submit" name="buscar">Buscar producto</button>
                <button type="submit" name="procesar">Hacer Pedido</button>
            </form>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr class="headtable">
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        if ($productos_registrados) {
                            foreach ($productos_registrados as $row) {
                                $total += $row['precioProducto'] * $row['cantidad'];
                                echo "<tr>
                                    <td>" . htmlspecialchars($row['nombre']) . "</td>
                                    <td>" . intval($row['cantidad']) . "</td>
                                    <td>$" . number_format($row['precioProducto'], 2) . "</td>
                                    <td>$" . number_format($row['precioVenta'], 2) . "</td>
                                    <td>
                                        <form method='POST' style='display:inline'>
                                            <input type='hidden' name='producto_id' value='" . intval($row['idProducto']) . "' />
                                            <button type='submit' name='sumar'>+</button>
                                            <button type='submit' name='restar'>-</button>
                                        </form>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No hay productos en el carrito.</td></tr>";
                        }
                        ?>
                        <tr>
                            <td colspan="3" style="text-align:right; font-weight:bold;">Total:</td>
                            <td colspan="2" style="font-weight:bold;">$<?= number_format($total, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
