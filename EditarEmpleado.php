<?php
session_start();

// Configuración de conexión
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

// Obtener el id del empleado desde la URL para edición
$idEmpleadoEdicion = isset($_GET['idEmpleado']) ? (int)$_GET['idEmpleado'] : null;
if (!$idEmpleadoEdicion) {
    die("Empleado no encontrado.");
}

// Obtener datos del empleado junto con domicilio, persona, y código postal legible (d_CP)
$sql = "SELECT e.idEmpleado, e.Puesto, e.RFC, e.NumeroSeguroSocial, e.Usuario,
               p.Nombre, p.Paterno, p.Materno, p.Telefono, p.Email, p.Edad, p.Sexo, p.idDomicilio,
               d.Calle, d.Numero, cp.d_CP
        FROM Empleados e
        JOIN Personas p ON e.idPersona = p.idPersona
        JOIN Domicilios d ON p.idDomicilio = d.idDomicilio
        JOIN CodigosPostales cp ON d.c_CP = cp.c_CP
        WHERE e.idEmpleado = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idEmpleadoEdicion);
$stmt->execute();
$resultado = $stmt->get_result();
$empleado = $resultado->fetch_assoc();
$stmt->close();

if (!$empleado) {
    die("Empleado no encontrado.");
}

// Variables para mensajes de error y éxito
$error = "";
$exito = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpiar y obtener valores del formulario
    $nombre = trim($_POST['Nombre']);
    $paterno = trim($_POST['Paterno']);
    $materno = trim($_POST['Materno']);
    $telefono = trim($_POST['Telefono']);
    $email = trim($_POST['Email']);
    $edad = (int)$_POST['Edad'];
    $sexo = $_POST['Sexo']; // 'H' o 'M'

    // Domicilio
    $calle = trim($_POST['Calle']);
    $numero = (int)$_POST['Numero'];
    $d_CP_input = trim($_POST['d_CP']);

    // Empleado específico
    $puesto = $_POST['Puesto'];
    $rfc = trim($_POST['RFC']);
    $nss = trim($_POST['NumeroSeguroSocial']);
    $usuario = trim($_POST['Usuario']);

    // Validar campos mínimos (puedes extender esto)
    if (!$nombre || !$paterno || !$materno || !$telefono || !$email || !$calle || !$d_CP_input || !$puesto || !$usuario) {
        $error = "Por favor complete todos los campos obligatorios.";
    } elseif (!preg_match('/^[HM]$/', $sexo)) {
        $error = "Sexo inválido.";
    } else {
        // Llamar al procedimiento almacenado
        $sqlSP = "CALL ActualizarEmpleado(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtSP = $conexion->prepare($sqlSP);
        if (!$stmtSP) {
            $error = "Error en la preparación del procedimiento almacenado: " . $conexion->error;
        } else {
            $stmtSP->bind_param(
                "isssssisissssss",
                $idEmpleadoEdicion,
                $nombre,
                $paterno,
                $materno,
                $telefono,
                $email,
                $edad,
                $sexo,
                $calle,
                $numero,
                $d_CP_input,
                $puesto,
                $rfc,
                $nss,
                $usuario
            );

            if ($stmtSP->execute()) {
                $exito = "Empleado actualizado correctamente.";
                // Refrescar datos para mostrar los cambios en el formulario
                $stmtSP->close();

                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("i", $idEmpleadoEdicion);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $empleado = $resultado->fetch_assoc();
                $stmt->close();
            } else {
                // Detectar si es error generado por SIGNAL
                if (strpos($stmtSP->error, 'El código postal') !== false) {
                    $error = $stmtSP->error;
                } else {
                    $error = "Error al actualizar empleado: " . $stmtSP->error;
                }
                $stmtSP->close();
            }
        }
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ActualizarDatosEmpleado</title>
    <link rel="stylesheet" type="text/css" href="EditarEmpleado2.css" />
    <link rel="icon" type="image" href="Imagenes2/Destornillador.jpg" />
</head>
<body>
    <div class="titulin">
        <h1>HERRERIA "METALURGIA 360"</h1>
        <a href="Empleados.php">
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
                    <?php elseif ($exito): ?>
                        <p style="color: green;"><?php echo htmlspecialchars($exito); ?></p>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="titulo">
                            <h2>Actualizar Datos</h2>
                            
                        </div>

                        <!-- Nombre -->
                        <div class="input-group">
                            <input type="text" name="Nombre" value="<?php echo htmlspecialchars($empleado['Nombre']); ?>" required placeholder=" " />
                            <label for="Nombre">Nombre</label>
                        </div>

                        <!-- Paterno -->
                        <div class="input-group">
                            <input type="text" name="Paterno" value="<?php echo htmlspecialchars($empleado['Paterno']); ?>" required placeholder=" " />
                            <label for="Paterno">Apellido Paterno</label>
                        </div>

                        <!-- Materno -->
                        <div class="input-group">
                            <input type="text" name="Materno" value="<?php echo htmlspecialchars($empleado['Materno']); ?>" required placeholder=" " />
                            <label for="Materno">Apellido Materno</label>
                        </div>

                        <!-- Teléfono -->
                        <div class="input-group">
                            <input type="tel" name="Telefono" value="<?php echo htmlspecialchars($empleado['Telefono']); ?>" pattern="[0-9]{10}" required placeholder=" " />
                            <label for="Telefono">Número Telefónico</label>
                        </div>

                        <!-- Email -->
                        <div class="input-group">
                            <input type="email" name="Email" value="<?php echo htmlspecialchars($empleado['Email']); ?>" required placeholder=" " />
                            <label for="Email">Correo Electrónico</label>
                        </div>

                        <!-- Edad -->
                        <div class="input-group">
                            <input type="number" name="Edad" min="0" max="120" value="<?php echo htmlspecialchars($empleado['Edad']); ?>" required placeholder=" " />
                            <label for="Edad">Edad</label>
                        </div>

                        <!-- Sexo -->
                        <div class="input-group">
                            <select name="Sexo" required>
                                <option value="" disabled hidden>Seleccione sexo</option>
                                <option value="H" <?php echo ($empleado['Sexo'] == 'H') ? 'selected' : ''; ?>>Hombre</option>
                                <option value="M" <?php echo ($empleado['Sexo'] == 'M') ? 'selected' : ''; ?>>Mujer</option>
                            </select>
                            <label for="Sexo">Sexo</label>
                        </div>

                        <!-- Calle -->
                        <div class="input-group">
                            <input type="text" name="Calle" value="<?php echo htmlspecialchars($empleado['Calle']); ?>" required placeholder=" " />
                            <label for="Calle">Calle</label>
                        </div>

                        <!-- Número -->
                        <div class="input-group">
                            <input type="number" name="Numero" min="1" value="<?php echo htmlspecialchars($empleado['Numero']); ?>" required placeholder=" " />
                            <label for="Numero">Número</label>
                        </div>

                        <!-- Código Postal -->
                        <div class="input-group">
                            <input type="text" name="d_CP" value="<?php echo htmlspecialchars($empleado['d_CP']); ?>" required placeholder=" " />
                            <label for="d_CP">Código Postal</label>
                        </div>

                        <!-- Puesto -->
                        <div class="input-group">
                            <select name="Puesto" required>
                                <option value="" disabled hidden>Seleccione puesto</option>
                                <option value="Administrador" <?php echo ($empleado['Puesto'] == 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                                <option value="Cajero" <?php echo ($empleado['Puesto'] == 'Cajero') ? 'selected' : ''; ?>>Cajero</option>
                                <option value="Agente de Venta" <?php echo ($empleado['Puesto'] == 'Agente de Venta') ? 'selected' : ''; ?>>Agente de Venta</option>
                            </select>
                            <label for="Puesto">Puesto</label>
                        </div>

                        <!-- RFC -->
                        <div class="input-group">
                            <input type="text" name="RFC" value="<?php echo htmlspecialchars($empleado['RFC']); ?>" maxlength="13" placeholder=" " />
                            <label for="RFC">RFC</label>
                        </div>

                        <!-- Número Seguro Social -->
                        <div class="input-group">
                            <input type="text" name="NumeroSeguroSocial" value="<?php echo htmlspecialchars($empleado['NumeroSeguroSocial']); ?>" maxlength="11" placeholder=" " />
                            <label for="NumeroSeguroSocial">Número Seguro Social</label>
                        </div>

                        <!-- Usuario -->
                        <div class="input-group">
                            <input type="text" name="Usuario" value="<?php echo htmlspecialchars($empleado['Usuario']); ?>" required placeholder=" " />
                            <label for="Usuario">Usuario</label>
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
