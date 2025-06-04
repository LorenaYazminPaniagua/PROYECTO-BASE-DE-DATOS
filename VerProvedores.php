<?php
// Conexión a la base de datos
$host = "localhost";                    // Nombre del servicio del contenedor (no localhost)
$usuario = "root";               // Usuario root por default
$clave = "";                // Contraseña definida en docker-compose
$bd = "TiendaAbarrotes";         // Nombre de la base de datos

$conn = new mysqli($host, $usuario, $clave, $bd);

$empleado_id = $_SESSION['id'] ?? 1;
$conn->query("SET @empleado_id = $empleado_id");

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Inicializar el arreglo de proveedores
$proveedores = [];

// Consulta a la vista VistaProveedoresPorDia
$sql = "SELECT * FROM VistaProveedoresPorDia";
$resultado = $conn->query($sql);

// Verificar si hay resultados
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $proveedores[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proveedores del Día</title>
    <link rel="stylesheet" href="VerProvedores.css">
    <link rel="icon" href="Imagenes/Logo.jpg" type="image/jpg">
</head>
<body>
    <header class="titulo">
        <!-- Botón de atrás -->
        <div class="boton-atras-contenedor">
            <img src="Imagenes/Menu.png" alt="Atrás" class="boton-atras">
        </div>

        <!-- Menú lateral -->
        <nav class="menu">
            <div class="menu-header">
                <img src="Imagenes/Casa.png" alt="Logo" class="menu-logo">
                <h2 class="menu-title">Abarrotes Guzmán</h2>
            </div>
            <ul class="list">
                <li><a href="InicioTrabajadores.php"><img src="Imagenes/Casa.png" alt=""> Inicio</a></li>
                <li><a href="ChecarPrecio.php"><img src="Imagenes/Buscar.png" alt=""> Buscar</a></li>
                <li><a href="Devolucion.php"><img src="Imagenes/Devolucion.png" alt=""> Devoluciones</a></li>
                <li><a href="VerProvedores.php"><img src="Imagenes/Proveedor.png" alt=""> Proveedores</a></li>
                <li><a href="VerPedidos.php"><img src="Imagenes/Pedido.png" alt=""> Pedidos</a></li>
                <li><a href="HacerRecarga.php"><img src="Imagenes/Recarga.png" alt=""> Recargas</a></li>
                <li><a href="Login.php"><img src="Imagenes/Salir.png" alt=""> Cerrar Sesión</a></li>
            </ul>
        </nav>

        <h1>Proveedores del Día</h1>
    </header>

    <main>
        <div class="container">
            <?php if (count($proveedores) > 0): ?>
                <?php foreach ($proveedores as $proveedor): ?>
                    <div class="card">
                        <!-- Cara 1 con la imagen del proveedor -->
                        <div class="face face1">
                            <div class="content">
                                <img src="Imagenes/Proveedores/<?php echo htmlspecialchars($proveedor['Imagen']); ?>" alt="Imagen del proveedor">
                            </div>
                        </div>

                        <!-- Cara 2 con la información del proveedor -->
                        <div class="face face2">
                            <div class="content">
                                <h3><?php echo htmlspecialchars($proveedor['Nombre'] . ' ' . $proveedor['ApellidoPaterno'] . ' ' . $proveedor['ApellidoMaterno']); ?></h3>
                                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($proveedor['Telefono']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($proveedor['Email']); ?></p>
                                <p><strong>Última actualización:</strong> <?php echo date("d/m/Y", strtotime($proveedor['FechaActualizacion'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay proveedores para hoy.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Diamonds Corporation. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
