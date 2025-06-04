<?php
session_start();
$idEmpleado = $_SESSION['id'] ?? 1;
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";
$conn = new mysqli($host, $usuario, $clave, $bd);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
$conn->query("SET @id_empleado_sesion := $idEmpleado");

$errorMsg = '';
$successMsg = '';

$descuentos = [];
$result = $conn->query("SELECT idDescuento, Categoria FROM Descuentos");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $descuentos[] = $row;
    }
    $result->free();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $calle = trim($_POST['Calle']);
    $numero = (int)$_POST['Numero'];
    $d_CP = trim($_POST['d_CP']);
    $nombre = trim($_POST['Nombre']);
    $paterno = trim($_POST['Paterno']);
    $materno = trim($_POST['Materno']);
    $telefono = trim($_POST['Telefono']);
    $email = trim($_POST['Email']);
    $edad = (int)$_POST['Edad'];
    $sexo = strtoupper(trim($_POST['Sexo']));
    $credito = (float)$_POST['Credito'];
    $limite = (float)$_POST['Limite'];
    $idDescuento = (int)$_POST['idDescuento'];

    if ($sexo !== 'H' && $sexo !== 'M') {
        $errorMsg = "Valor inválido para Sexo.";
    } elseif (empty($calle) || $numero <= 0 || empty($d_CP)) {
        $errorMsg = "Debe completar los datos de domicilio correctamente.";
    } elseif (!in_array($idDescuento, array_column($descuentos, 'idDescuento'))) {
        $errorMsg = "Seleccione un descuento válido.";
    }

    if (!$errorMsg) {
        $sql = "CALL RegistrarCliente(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $errorMsg = "Error al preparar el procedimiento: " . $conn->error;
        } else {
            $stmt->bind_param(
                "sissssssisdii",
                $calle,
                $numero,
                $d_CP,
                $nombre,
                $paterno,
                $materno,
                $telefono,
                $email,
                $edad,
                $sexo,
                $credito,
                $limite,
                $idDescuento
            );
            if ($stmt->execute()) {
                $successMsg = "Cliente agregado correctamente.";
            } else {
                $errorMsg = "Error al agregar cliente: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agregar Cliente</title>
    <link rel="stylesheet" href="AgregarCliente2.css" />
    <link rel="icon" type="image/jpeg" href="Imagenes2/DESTORNILLADOR.jpg">
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
            <div class="log">
                <div class="login">
                    <form method="POST" action="AgregarCliente.php" novalidate>
                        <h2>Nuevo Cliente</h2>
                        

                        <?php if ($errorMsg): ?>
                            <p style="color:red;"><?= htmlspecialchars($errorMsg) ?></p>
                        <?php elseif ($successMsg): ?>
                            <p style="color:green;"><?= htmlspecialchars($successMsg) ?></p>
                        <?php endif; ?>

                        <div class="input-group">
                            <input type="text" name="Calle" required placeholder=" " />
                            <label for="Calle">Calle</label>
                        </div>
                        <div class="input-group">
                            <input type="number" name="Numero" min="1" required placeholder=" " />
                            <label for="Numero">Número</label>
                        </div>
                        <div class="input-group">
                            <input type="text" name="d_CP" required placeholder=" " />
                            <label for="d_CP">Código Postal</label>
                        </div>

                        <div class="input-group">
                            <input type="text" name="Nombre" required placeholder=" " />
                            <label for="Nombre">Nombre</label>
                        </div>
                        <div class="input-group">
                            <input type="text" name="Paterno" required placeholder=" " />
                            <label for="Paterno">Apellido Paterno</label>
                        </div>
                        <div class="input-group">
                            <input type="text" name="Materno" required placeholder=" " />
                            <label for="Materno">Apellido Materno</label>
                        </div>
                        <div class="input-group">
                            <input type="tel" name="Telefono" pattern="[0-9]{10}" title="10 dígitos" required placeholder=" " />
                            <label for="Telefono">Teléfono</label>
                        </div>
                        <div class="input-group">
                            <input type="email" name="Email" required placeholder=" " />
                            <label for="Email">Email</label>
                        </div>
                        <div class="input-group">
                            <input type="number" name="Edad" min="0" max="120" required placeholder=" " />
                            <label for="Edad">Edad</label>
                        </div>
                        <div class="input-group">
                            <select name="Sexo" required>
                                <option value="" disabled selected hidden>Seleccione Sexo</option>
                                <option value="H">Hombre</option>
                                <option value="M">Mujer</option>
                            </select>
                            <label for="Sexo">Sexo</label>
                        </div>

                        <div class="input-group">
                            <input type="number" step="0.01" name="Credito" min="0" value="0" required placeholder=" " />
                            <label for="Credito">Crédito</label>
                        </div>
                        <div class="input-group">
                            <input type="number" step="0.01" name="Limite" min="0" value="0" required placeholder=" " />
                            <label for="Limite">Límite</label>
                        </div>

                        <div class="input-group">
                            <select name="idDescuento" required>
                                <option value="" disabled selected hidden>Seleccione Descuento</option>
                                <?php foreach ($descuentos as $desc): ?>
                                    <option value="<?= htmlspecialchars($desc['idDescuento']) ?>">
                                        <?= htmlspecialchars($desc['NombreDescuento']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="idDescuento">Descuento</label>
                        </div>

                        <button type="submit"class="button-1">Agregar</button>
                    </form>
                </div>
            </div>
        </section>
        <section class="derecha"></section>
    </main>
</body>
</html>
