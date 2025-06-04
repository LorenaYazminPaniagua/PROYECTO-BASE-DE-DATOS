<?php
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

$conn = new mysqli($host, $usuario, $clave, $bd);

$empleado_id = $_SESSION['id'] ?? 1;
$conn->query("SET @empleado_id = $empleado_id");

$sql = "SELECT * FROM VistaEmpleadosActivos";
$resultado = $conn->query($sql);
$conn->close();
?>



<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleados</title>

    <style>
        table{
            width:100%;
            border-collapse: collapse;
        }
        th, td{
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }
        h2{
            text-align: center;
        }
    </style>


</head>
<body>
    <h2>Empleados Activos</h2>
<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        
        <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellido Paterno</th>
                            <th>Apellido Materno</th>
                            <th>Teléfono</th>
                            <th>Puesto</th>
                            <th>Usuario</th>
                            
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
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='13'>No se encontraron empleados activos.</td></tr>";
                        }
                        ?>
                    </tbody>
    </table>
</div>




   
</body>
</html>

<?php $html=ob_get_clean(); ?>
 <?php

require 'vendor/autoload.php';
// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();
$dompdf->loadHtml("$html");

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream('empleados.pdf',['Attachment'=>true]);

?>
