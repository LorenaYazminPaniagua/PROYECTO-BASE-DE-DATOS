<?php
session_start();

// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

$conexion = new mysqli($host, $usuario, $clave, $bd);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$idEmpleado = $_SESSION['id'] ?? 1;  // Ajusta según cómo tengas el id del empleado
$conexion->query("SET @id_empleado_sesion := $idEmpleado");

// Obtener el id del cliente desde la URL
$idCliente = isset($_GET['idCliente']) ? (int)$_GET['idCliente'] : null;
if (!$idCliente) {
    die("Cliente no encontrado.");
}

// Obtener datos del cliente junto con domicilio y descuento
$sql = "SELECT c.*, p.Nombre, p.Paterno, p.Materno, p.Telefono, p.Email, p.Edad, p.Sexo,
        d.Calle, d.Numero, cp.d_CP
        FROM Clientes c
        JOIN Personas p ON c.idPersona = p.idPersona
        JOIN Domicilios d ON p.idDomicilio = d.idDomicilio
        JOIN CodigosPostales cp ON d.c_CP = cp.c_CP
        WHERE c.idCliente = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idCliente);
$stmt->execute();
$resultado = $stmt->get_result();
$cliente = $resultado->fetch_assoc();
$stmt->close();

if (!$cliente) {
    die("Cliente no encontrado.");
}

// Obtener categorías para desplegable de descuentos
$sqlCategorias = "SELECT idDescuento, Categoria FROM Descuentos ORDER BY Categoria";
$resultCategorias = $conexion->query($sqlCategorias);
$categorias = [];
if ($resultCategorias) {
    while ($row = $resultCategorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpiar y obtener valores del formulario
    $nombre = trim($_POST['Nombre']);
    $paterno = trim($_POST['Paterno']);
    $materno = trim($_POST['Materno']);
    $telefono = trim($_POST['Telefono']);
    $email = trim($_POST['Email']);
    $edad = (int)$_POST['Edad'];
    $sexo = $_POST['Sexo']; // 'H' o 'M'
    $credito = (float)$_POST['Credito'];
    $limite = (float)$_POST['Limite'];
    $idDescuento = (int)$_POST['idDescuento'];

    // Campos nuevos para domicilio
    $calle = trim($_POST['Calle']);
    $numero = (int)$_POST['Numero'];
    $d_CP_input = trim($_POST['d_CP']);

    // Validar que el código postal existe (opcional, ya que el procedimiento lo valida)
    $stmtCP = $conexion->prepare("SELECT c_CP FROM CodigosPostales WHERE d_CP = ? LIMIT 1");
    $stmtCP->bind_param("s", $d_CP_input);
    $stmtCP->execute();
    $resultCP = $stmtCP->get_result();
    if ($rowCP = $resultCP->fetch_assoc()) {
        $c_CP = $rowCP['c_CP'];
    } else {
        $error = "Código postal inválido.";
    }
    $stmtCP->close();

    if (!$error) {
        // Preparar llamada al procedimiento almacenado (sin idDomicilio)
        $sql = "CALL ActualizarCliente(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conexion->error);
        }

        // Asignar parámetros exactamente en el orden del procedimiento
        $stmt->bind_param(
            "isssssisddisis",
            $idCliente,
            $nombre,
            $paterno,
            $materno,
            $telefono,
            $email,
            $edad,
            $sexo,
            $credito,
            $limite,
            $idDescuento,
            $calle,
            $numero,
            $d_CP_input
        );

        if ($stmt->execute()) {
            // Redirigir después de actualizar
            header("Location: Clientes.php?exito=1");
            exit;
        } else {
            // Capturar mensaje de error del procedimiento almacenado
            $error = "Error al actualizar el cliente: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Actualizar Cliente</title>
    <link rel="stylesheet" type="text/css" href="EditarCliente2.css" />
    <link rel="icon" type="image" href="Imagenes2/DESTORNILLADOR.jpg" />
</head>
<body>
    <div class="titulin">
        <h1>HERRERIA "METALURGIA 360"</h1>
        <a href="Clientes.php">
            <img src="Imagenes2/regresar.jpg" alt="Botón Atrás" class="boton-atras" />
        </a>
    </div>
    <main>
        <section class="izquierda"></section>
        <section class="centro">
            <br /><br />
            <div class="log">
                <div class="login">
                    <?php if ($error): ?>
                        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="titulo">
                            <h2>Actualizar Cliente</h2>
                        </div>

                        <!-- Nombre -->
                        <div class="input-group">
                            <input type="text" name="Nombre" value="<?php echo htmlspecialchars($cliente['Nombre']); ?>" required placeholder=" " />
                            <label for="Nombre">Nombre</label>
                        </div>

                        <!-- Paterno -->
                        <div class="input-group">
                            <input type="text" name="Paterno" value="<?php echo htmlspecialchars($cliente['Paterno']); ?>" required placeholder=" " />
                            <label for="Paterno">Apellido Paterno</label>
                        </div>

                        <!-- Materno -->
                        <div class="input-group">
                            <input type="text" name="Materno" value="<?php echo htmlspecialchars($cliente['Materno']); ?>" required placeholder=" " />
                            <label for="Materno">Apellido Materno</label>
                        </div>

                        <!-- Teléfono -->
                        <div class="input-group">
                            <input type="tel" name="Telefono" value="<?php echo htmlspecialchars($cliente['Telefono']); ?>" pattern="[0-9]{10}" required placeholder=" " />
                            <label for="Telefono">Número Telefónico</label>
                        </div>

                        <!-- Email -->
                        <div class="input-group">
                            <input type="email" name="Email" value="<?php echo htmlspecialchars($cliente['Email']); ?>" required placeholder=" " />
                            <label for="Email">Correo Electrónico</label>
                        </div>

                        <!-- Edad -->
                        <div class="input-group">
                            <input type="number" name="Edad" min="0" max="90" value="<?php echo htmlspecialchars($cliente['Edad']); ?>" required placeholder=" " />
                            <label for="Edad">Edad</label>
                        </div>

                        <!-- Sexo -->
                        <div class="input-group">
                            <select name="Sexo" required>
                                <option value="" disabled hidden>Seleccione sexo</option>
                                <option value="H" <?php echo ($cliente['Sexo'] == 'H') ? 'selected' : ''; ?>>Hombre</option>
                                <option value="M" <?php echo ($cliente['Sexo'] == 'M') ? 'selected' : ''; ?>>Mujer</option>
                            </select>
                            <label for="Sexo">Sexo</label>
                        </div>

                        <!-- Calle -->
                        <div class="input-group">
                            <input type="text" name="Calle" value="<?php echo htmlspecialchars($cliente['Calle']); ?>" required placeholder=" " />
                            <label for="Calle">Calle</label>
                        </div>

                        <!-- Número -->
                        <div class="input-group">
                            <input type="number" name="Numero" min="1" value="<?php echo htmlspecialchars($cliente['Numero']); ?>" required placeholder=" " />
                            <label for="Numero">Número</label>
                        </div>

                        <!-- Código Postal (d_CP) -->
                        <div class="input-group">
                            <input type="text" name="d_CP" value="<?php echo htmlspecialchars($cliente['d_CP']); ?>" required placeholder=" " />
                            <label for="d_CP">Código Postal</label>
                        </div>

                        <!-- Crédito -->
                        <div class="input-group">
                            <input type="number" step="0.01" name="Credito" min="0" value="<?php echo htmlspecialchars($cliente['Credito']); ?>" required placeholder=" " />
                            <label for="Credito">Crédito</label>
                        </div>

                        <!-- Límite -->
                        <div class="input-group">
                            <input type="number" step="0.01" name="Limite" min="0" value="<?php echo htmlspecialchars($cliente['Limite']); ?>" required placeholder=" " />
                            <label for="Limite">Límite</label>
                        </div>

                        <!-- Descuento (Categoría) -->
                        <div class="input-group">
                            <select name="idDescuento" required>
                                <option value="" disabled>Seleccione categoría</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['idDescuento']; ?>" 
                                        <?php echo ($cliente['idDescuento'] == $cat['idDescuento']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['Categoria']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="idDescuento">Categoría</label>
                        </div>

                        <button type="submit" class="button-1">Actualizar</button>
                    </form>
                </div>
            </div>
        </section>
        <section class="derecha"></section>
    </main>
</body>
</html>
