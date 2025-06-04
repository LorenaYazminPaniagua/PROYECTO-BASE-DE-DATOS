<?php 
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

$conn = new mysqli($host, $usuario, $clave, $bd);

// Asignar empleado_id en variable de sesión MySQL, si es necesario
$empleado_id = $_SESSION['id'] ?? 1;
$conn->query("SET @empleado_id = $empleado_id");

// Consulta a la vista
$sql = "SELECT * FROM VistaClientesActivos";
$resultado = $conn->query($sql);

// No cerramos la conexión todavía para poder usar $resultado en la página
?>

<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>

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
        <h2>Clientes Activos</h2>
<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Tipo Cliente</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Límite de Crédito</th>
                            <th>Crédito Disponible</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($cliente = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['Nombre'] . ' ' . $cliente['Paterno'] . ' ' . $cliente['Materno']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['TipoCliente'] ?? 'Sin categoría'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['Email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($cliente['Telefono'] ?? ''); ?></td>
                                <td>$<?php echo number_format($cliente['Credito'] ?? 0, 2); ?></td>
                                <td>$<?php echo number_format($cliente['Limite'] ?? 0, 2); ?></td>
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
$dompdf->stream('Clientes.pdf',['Attachment'=>true]);

?>