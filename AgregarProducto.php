<?php
session_start();

// Aquí asumo que ya tienes el id del empleado guardado en sesión bajo la clave 'id'
if (!isset($_SESSION['id'])) {
    // Si no está definido, puedes redirigir o mostrar un error
    die("No hay empleado en sesión. Por favor, inicia sesión.");
}

$idEmpleado = intval($_SESSION['id']);  // Asegúrate de que sea un número entero para evitar inyección

// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";
$conexion = new mysqli($host, $usuario, $clave, $bd);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Asignar variable de sesión en MySQL para el empleado activo
// Esto se puede usar dentro de procedimientos almacenados como @id_empleado_sesion
if (!$conexion->query("SET @id_empleado_sesion := $idEmpleado")) {
    die("Error al establecer variable de sesión en MySQL: " . $conexion->error);
}

// Ejecutar consultas para categorías y proveedores
$consultaCategorias = $conexion->query("SELECT idCategoria, Nombre FROM Categorias");
if (!$consultaCategorias) {
    die("Error al cargar categorías: " . $conexion->error);
}

$consultaProveedores = $conexion->query("SELECT idProveedor, Nombre FROM Proveedores");
if (!$consultaProveedores) {
    die("Error al cargar proveedores: " . $conexion->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['AgregarProducto'])) {
    // Preparar la llamada al procedimiento almacenado
    $stmt = $conexion->prepare("CALL AgregarProducto(?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Error en prepare(): " . $conexion->error);
    }

    // Recoger datos
    $nombre = $_POST['Nombre'];
    $precioCompra = $_POST['PrecioCompra'];
    $precioVenta = $_POST['PrecioVenta'];
    $codigoBarras = $_POST['CodigoBarras'];
    $stock = $_POST['Stock'];
    $idCategoria = $_POST['idCategoria'];
    $idProveedor = $_POST['idProveedor'];

    // Bind y execute
    $stmt->bind_param("sddsiii", $nombre, $precioCompra, $precioVenta, $codigoBarras, $stock, $idCategoria, $idProveedor);
    if (!$stmt->execute()) {
        die("Error en ejecución: " . $stmt->error);
    }

    // Redireccionar
    header("Location: Almacen.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto</title>
    <link rel="stylesheet" href="AgregarProducto2.css">
    <link rel="icon" type="image/jpeg" href="Imagenes2/DESTORNILLADOR.jpg">
</head>
<body>
    <div class="titulin">
        <h1>HERRERIA "METALURGIA 360"</h1>
        <a href="Almacen.php">
            <img src="Imagenes2/regresar.jpg" alt="Atrás" class="boton-atras">
        </a>
    </div>
    <main>
        <section class="centro">
            <div class="log">
                <div class="login">
                    <form method="POST" action="AgregarProducto.php">
                        <div class="titulo">
                            <h2>Agregar Producto</h2>
                            
                        </div>

                        <!-- Nombre -->
                        <div class="input-group">
                            <input type="text" name="Nombre" required placeholder=" ">
                            <label>Nombre del Producto</label>
                        </div>

                        <!-- Precio Compra -->
                        <div class="input-group">
                            <input type="number" step="0.01" name="PrecioCompra" required placeholder=" ">
                            <label>Precio de Compra</label>
                        </div>

                        <!-- Precio Venta -->
                        <div class="input-group">
                            <input type="number" step="0.01" name="PrecioVenta" required placeholder=" ">
                            <label>Precio de Venta</label>
                        </div>

                        <!-- Código de Barras -->
                        <div class="input-group">
                            <input type="number" name="CodigoBarras" required placeholder=" ">
                            <label>Código de Barras</label>
                        </div>

                        <!-- Stock -->
                        <div class="input-group">
                            <input type="number" name="Stock" min="0" required placeholder=" ">
                            <label>Cantidad en Stock</label>
                        </div>

                        <!-- Categoría -->
                        <div class="input-group">
                            <select class="Opcion" name="idCategoria" required>
                                <option disabled selected>Selecciona una Categoría</option>
                                <?php
                                while ($categoria = $consultaCategorias->fetch_assoc()) {
                                    echo "<option value='" . $categoria['idCategoria'] . "'>" .
                                        htmlspecialchars($categoria['Nombre']) . "</option>";
                                }
                                ?>
                            </select>
                            <label>Categoría</label>
                        </div>

                        <!-- Proveedor -->
                        <div class="input-group">
                            <select class="Opcion" name="idProveedor" required>
                                <option disabled selected>Selecciona un Proveedor</option>
                                <?php
                                while ($proveedor = $consultaProveedores->fetch_assoc()) {
                                    echo "<option value='" . $proveedor['idProveedor'] . "'>" .
                                        htmlspecialchars($proveedor['Nombre']) . "</option>";
                                }
                                ?>
                            </select>
                            <label>Proveedor</label>
                        </div>

                        <!-- Botón -->
                        <button type="submit" name="AgregarProducto" class="Acceder">Agregar Producto</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
