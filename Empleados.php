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

$sql = "SELECT * FROM VistaEmpleadosActivos";
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
    <title>Empleados Activos</title>
    <link rel="stylesheet" type="text/css" href="Empleados2.css">
    <link rel="icon" type="image/jpg" href="Imagenes2/Destornillador.jpg">
</head>
<body>
    <div class="overlay"></div>

    <header class="titulo">
        <div class="boton-atras-contenedor">
            <a href="ADMINISTRADORES.html">
            <img src="Imagenes2/regresar.jpg" alt="Menú" class="boton-atras" />
            </a>
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
                            <th>Acciones</th>
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
                                        <a href='EditarEmpleado.php?idEmpleado=" . urlencode($empleado['idEmpleado']) . "'>
                                            <img src='Imagenes2/editar.jpg' alt='Editar' class='mi-imagen'>
                                        </a>
                                        <a href='BorrarEmpleado.php?idEmpleado=" . urlencode($empleado['idEmpleado']) . "'>
                                            <img src='Imagenes2/menos.jpg' alt='Eliminar' class='mi-imagen'>
                                        </a>
                                    </div>
                                </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='13'>No se encontraron empleados activos.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div class="boton-agregar-container">
                     <a href="Registro.php" title="Agregar"><img src="Imagenes2/Mas.jpg" alt="Agregar" class="agregar"></a>
                     <a href="EmpleadosInactivos.php" title="Descargar"><img src="Imagenes2/empleadosmenos.jpg" alt="Agregar" class="agregar"></a>
                     <a href="pdf.php" target="_blank" title="Descargar"><img src="Imagenes2/descargar.jpg" alt="Agregar" class="agregar"></a>
                </div>
            </div>
        </section>
    </main>

</body>
</html>
