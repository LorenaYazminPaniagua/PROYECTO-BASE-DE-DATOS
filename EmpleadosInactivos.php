<?php
session_start();

// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

$conn = new mysqli($host, $usuario, $clave, $bd);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$empleado_id = $_SESSION['id'] ?? 1;
$conn->query("SET @empleado_id = $empleado_id");

$sql = "SELECT * FROM VistaEmpleadosInactivos";
$resultado = $conn->query($sql);
if (!$resultado) {
    die("Error en la consulta: " . $conn->error);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empleados Inactivos</title>
    <link rel="stylesheet" type="text/css" href="EmpleadosInactivos2.css">
    <link rel="icon" type="image/jpg" href="Imagenes2/DESTORNILLADOR.jpg">
</head>
<body>
    <div class="overlay"></div>

    <header class="titulo">
        <div class="boton-atras-contenedor">
            <a href="Empleados.php" title="Agregar"><img src="Imagenes2/regresar.jpg" alt="Agregar" class="agregar"></a>
        </div>
        <h1>HERRERIA "METALURGIA 360"</h1>
    </header>

    <main class="contenido">
        <section class="container">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellido Paterno</th>
                            <th>Apellido Materno</th>
                            <th>Teléfono</th>
                            <th>Puesto</th>
                            <th>Usuario</th>
                            <th>Recuperar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($resultado->num_rows > 0) {
                            while ($empleado = $resultado->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($empleado['Nombre']) . "</td>";
                                echo "<td>" . htmlspecialchars($empleado['Paterno']) . "</td>";
                                echo "<td>" . htmlspecialchars($empleado['Materno']) . "</td>";
                                echo "<td>" . htmlspecialchars($empleado['Telefono']) . "</td>";
                                echo "<td>" . htmlspecialchars($empleado['Puesto']) . "</td>";
                                echo "<td>" . htmlspecialchars($empleado['Usuario']) . "</td>";
                                echo "<td>
                                    <div class='acciones'>
                                        <a href='RecuperarEmpleado.php?idEmpleado=" . urlencode($empleado['idEmpleado']) . "'>
                                            <img src='Imagenes2/recuperar.jpg' alt='Editar' class='mi-imagen'>
                                        </a>
                                    </div>
                                </td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>


</body>
</html>
