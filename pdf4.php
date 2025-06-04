<?php
// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

// Crear la conexión
$conexion = new mysqli($host, $usuario, $clave, $bd);

// No parece que necesites @empleado_id para esta consulta, pero si quieres mantenerlo:
$empleado_id = $_SESSION['id'] ?? 1;
$conexion->query("SET @empleado_id = $empleado_id");

// Consulta a la vista de todos los proveedores
$sql = "SELECT * FROM Vista_Todos_Proveedores";
$resultado = $conexion->query($sql);

?>
<?php ob_start(); ?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores</title>

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
        <h2>Proveedores</h2>
<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead>
                        <tr>
                            <th>Nombre</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($proveedor = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($proveedor['Nombre']); ?></td>
                               
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
    </table>
</div>






</body>
</html>

<?php  $html1=ob_get_clean(); ?>



    <?php   
require 'vendor/autoload.php';

// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();
$dompdf->loadHtml("$html1");

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream('Proveedores.pdf',['Attachment'=>true]);

?>