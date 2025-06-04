<?php $host = "localhost";
$usuario = "root";
$clave = "";
$bd = "HerreriaUG";

// Crear conexión
$conexion = new mysqli($host, $usuario, $clave, $bd);


// Consulta la vista VistaEstadoInventario
$sql = "SELECT * FROM VistaProductosActivos";
$resultado = $conexion->query($sql);

?>
<?php ob_start(); ?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario</title>

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
        <h2>Inventario</h2>
<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Proveedor</th>
                            <th>Stock</th>
                            <th>Precio Compra</th>
                            <th>Precio Venta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($producto = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['Producto']); ?></td>
                                <td><?php echo htmlspecialchars($producto['Categoria']); ?></td>
                                <td><?php echo htmlspecialchars($producto['Proveedor']); ?></td>
                                <td><?php echo htmlspecialchars($producto['Stock']); ?></td>
                                <td>$<?php echo number_format($producto['PrecioCompra'], 2); ?></td>
                                <td>$<?php echo number_format($producto['PrecioVenta'], 2); ?></td>
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
$dompdf->stream('Inventario.pdf',['Attachment'=>true]);

?>