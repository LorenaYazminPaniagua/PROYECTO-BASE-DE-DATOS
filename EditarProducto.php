<?php
session_start();

if (!isset($_SESSION['id'])) {
    echo "No tienes permisos para realizar esta acción.";
    exit;
}

$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

$conexion = new mysqli($host, $usuario, $clave, $bd);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Asignar variable de sesión de empleado como variable de usuario en MySQL
$idEmpleado = $_SESSION['id'];
$conexion->query("SET @id_empleado_sesion := $idEmpleado");

$producto = [];

// Obtener producto por ID (GET)
if (isset($_GET['idProducto']) && is_numeric($_GET['idProducto'])) {
    $idProducto = intval($_GET['idProducto']);

    $consulta = $conexion->prepare("SELECT * FROM Productos WHERE idProducto = ?");
    $consulta->bind_param("i", $idProducto);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows > 0) {
        $producto = $resultado->fetch_assoc();
    } else {
        echo "Producto no encontrado.";
        exit;
    }

    $consulta->close();
} elseif ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo "ID de producto no válido.";
    exit;
}

// Procesar formulario POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ActualizarProducto'])) {
    $idProducto = intval($_POST['idProducto']);
    $Nombre = $_POST['Nombre'];
    $PrecioCompra = $_POST['PrecioCompra'];
    $PrecioVenta = $_POST['PrecioVenta'];
    $CodigoBarras = $_POST['CodigoBarras'];
    $Stock = $_POST['Stock'];
    $idCategoria = $_POST['idCategoria'];
    $idProveedor = $_POST['idProveedor'];

    $stmt = $conexion->prepare("CALL ActualizarProductoCompleto(?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param(
            "isddsiii",
            $idProducto,
            $Nombre,
            $PrecioCompra,
            $PrecioVenta,
            $CodigoBarras,
            $Stock,
            $idCategoria,
            $idProveedor
        );

        if ($stmt->execute()) {
            header("Location: Almacen.php");
            exit;
        } else {
            echo "Error al actualizar: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conexion->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditarProducto</title>
    <link rel="stylesheet" href="EditarProducto2.css">
    <link rel="icon" href="Imagenes2/DESTORNILLADOR.jpg" type="image/jpg">
</head>
<body>
    <div class="titulin">
        <h1>HERRERIA "METALURGIA 360"</h1>
                                <a href="Almacen.php">
                            <img src="Imagenes2/regresar.jpg" alt="Botón Atrás" class="boton-atras">
                        </a>
    </div>
<main>
    <section class="centro">
        <div class="log">
            <div class="login">
                <form method="POST" action="EditarProducto.php">
                    <div class="titulo">
                        <h2>Editar producto</h2>

                    </div>

                    <input type="hidden" name="idProducto" value="<?php echo htmlspecialchars($producto['idProducto']); ?>">

                    <div class="input-group">
                        <input type="text" name="Nombre" value="<?php echo htmlspecialchars($producto['Nombre']); ?>" required placeholder=" ">
                        <label for="Nombre">Nombre del Producto</label>
                    </div>

                    <div class="input-group">
                        <input type="number" step="0.01" name="PrecioCompra" value="<?php echo htmlspecialchars($producto['PrecioCompra']); ?>" min="0" required placeholder=" ">
                        <label for="PrecioCompra">Precio de Compra</label>
                    </div>

                    <div class="input-group">
                        <input type="number" step="0.01" name="PrecioVenta" value="<?php echo htmlspecialchars($producto['PrecioVenta']); ?>" min="0" required placeholder=" ">
                        <label for="PrecioVenta">Precio de Venta</label>
                    </div>

                    <div class="input-group">
                        <input type="number" name="CodigoBarras" value="<?php echo htmlspecialchars($producto['CodigoBarras']); ?>" required placeholder=" ">
                        <label for="CodigoBarras">Código de Barras</label>
                    </div>

                    <div class="input-group">
                        <input type="number" name="Stock" value="<?php echo htmlspecialchars($producto['Stock']); ?>" min="0" required placeholder=" ">
                        <label for="Stock">Stock</label>
                    </div>

                    <div class="input-group">
                        <select class="Opcion" name="idCategoria" required>
                            <option disabled selected>Selecciona una Categoría</option>
                            <?php
                            $consultaCategorias = $conexion->query("SELECT idCategoria, Nombre FROM Categorias");
                            if ($consultaCategorias) {
                                while ($categoria = $consultaCategorias->fetch_assoc()) {
                                    echo "<option value='" . $categoria['idCategoria'] . "' " .
                                        ($producto['idCategoria'] == $categoria['idCategoria'] ? 'selected' : '') . ">" .
                                        htmlspecialchars($categoria['Nombre']) . "</option>";
                                }
                            } else {
                                echo "<option>Error al cargar categorías</option>";
                            }
                            ?>
                        </select>
                        <label for="idCategoria">Categoría</label>
                    </div>

                    <div class="input-group">
                        <select class="Opcion" name="idProveedor" required>
                            <option disabled selected>Selecciona un Proveedor</option>
                            <?php
                            $consultaProveedores = $conexion->query("SELECT idProveedor, Nombre FROM Proveedores");
                            if ($consultaProveedores) {
                                while ($proveedor = $consultaProveedores->fetch_assoc()) {
                                    echo "<option value='" . $proveedor['idProveedor'] . "' " .
                                        ($producto['idProveedor'] == $proveedor['idProveedor'] ? 'selected' : '') . ">" .
                                        htmlspecialchars($proveedor['Nombre']) . "</option>";
                                }
                            } else {
                                echo "<option>Error al cargar proveedores</option>";
                            }
                            ?>
                        </select>
                        <label for="idProveedor">Proveedor</label>
                    </div>
                    
                    <button type="submit" class="Acceder" name="ActualizarProducto">Actualizar</button>
                </form>

            </div>
        </div>
    </section>
</main>
</body>
</html>