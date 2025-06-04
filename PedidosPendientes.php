<?php
session_start();

$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUg";

// Procesar acción si viene un formulario enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idPedido'], $_POST['accion'])) {
    $idPedido = intval($_POST['idPedido']);
    $accion = $_POST['accion'];

    $conexion = new mysqli($host, $usuario, $clave, $bd);
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    switch ($accion) {
        case 'aceptado':
            $stmt = $conexion->prepare("CALL CambiarPedidoAAceptado(?)");
            break;
        case 'cancelado':
            $stmt = $conexion->prepare("CALL CambiarPedidoACancelado(?)");
            break;
        default:
            die("Acción no válida");
    }

    $stmt->bind_param("i", $idPedido);
    $stmt->execute();
    $stmt->close();
    $conexion->close();

    // Redirigir para evitar reenvío de formulario al refrescar
    header("Location: PedidosPendientes.php"); // Cambié el nombre del archivo para que sea coherente con Pendientes
    exit;
}

// Conexión para mostrar pedidos con productos
$conexion = new mysqli($host, $usuario, $clave, $bd);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$query = "SELECT * FROM VistaPedidosPendientes ORDER BY idPedido, idDetallePedido";
$result = $conexion->query($query);

$pedidos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Agrupar productos por idPedido
        $idPedido = $row['idPedido'];
        if (!isset($pedidos[$idPedido])) {
            // Datos generales del pedido
            $pedidos[$idPedido] = [
                'idPedido' => $idPedido,
                'Fecha' => $row['Fecha'],
                'Hora' => $row['Hora'],
                'Estatus' => $row['Estatus'],
                'idCliente' => $row['idCliente'],
                'NombreCliente' => $row['NombreCliente'],
                'idEmpleado' => $row['idEmpleado'],
                'NombreEmpleado' => $row['NombreEmpleado'],
                'productos' => []
            ];
        }
        // Agregar producto al pedido
        $pedidos[$idPedido]['productos'][] = [
            'idDetallePedido' => $row['idDetallePedido'],
            'idProducto' => $row['idProducto'],
            'NombreProducto' => $row['NombreProducto'],
            'Cantidad' => $row['Cantidad'],
            'PrecioUnitario' => $row['PrecioUnitario'],
            'Subtotal' => $row['Subtotal']
        ];
    }
}
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos administrador</title>
    <link rel="stylesheet" href="PedidosPendientes3.css">
    <link rel="icon" type="image/jpeg" href="Imagenes2/DESTORNILLADOR.jpg">
</head>
<body>
    <header class="titulo">
        <div class="boton-atras-contenedor">
            <a href="ADMINISTRADORES.html">
            <img src="Imagenes2/regresar.jpg" alt="Menú" class="boton-atras" />
            </a>
        </div>

        <h1>HERRERIA "METALURGIA 360"</h1>
    </header>

    <main>
        <div class="container">
            <?php if (empty($pedidos)): ?>
                <p>No hay pedidos pendientes.</p>
            <?php else: ?>
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3>Pedido #<?php echo $pedido['idPedido']; ?></h3>
                            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['NombreCliente']); ?> (ID: <?php echo $pedido['idCliente']; ?>)</p>
                            <p><strong>Fecha:</strong> <?php echo date("d/m/Y", strtotime($pedido['Fecha'])); ?></p>
                            <p><strong>Hora:</strong> <?php echo $pedido['Hora']; ?></p>
                            <p><strong>Estatus:</strong> <?php echo $pedido['Estatus']; ?></p>
                            <?php if ($pedido['NombreEmpleado']): ?>
                                <p><strong>Empleado asignado:</strong> <?php echo htmlspecialchars($pedido['NombreEmpleado']); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="productos">
                            <h4>Productos:</h4>
                            <ul>
                                <?php foreach ($pedido['productos'] as $producto): ?>
                                    <li>
                                        <?php echo htmlspecialchars($producto['NombreProducto']); ?> -
                                        Cantidad: <?php echo $producto['Cantidad']; ?> -
                                        Precio Unitario: $<?php echo number_format($producto['PrecioUnitario'], 2); ?> -
                                        Subtotal: $<?php echo number_format($producto['Subtotal'], 2); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="acciones">
                            <form method="POST" style="display:inline-block; margin-right: 10px;">
                                <input type="hidden" name="idPedido" value="<?php echo $pedido['idPedido']; ?>">
                                <input type="hidden" name="accion" value="aceptado">
                                <button type="submit" class="btn-recibido">Aceptar</button>
                            </form>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="idPedido" value="<?php echo $pedido['idPedido']; ?>">
                                <input type="hidden" name="accion" value="cancelado">
                                <button type="submit" class="btn-cancelado">Cancelar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
