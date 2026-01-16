<?php
// Configuración para JSON limpio
header('Content-Type: application/json; charset=utf-8');
ob_start();

require_once 'conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['exito' => false];
$bd = leer_config();
try {
    // Validar sesión
    if (!isset($_SESSION['usuario'])) {
        throw new Exception("Sesión no válida.");
    }

    // Recibir datos
    if (!isset($_POST['id'])) {
        throw new Exception("Faltan datos.");
    }

    $codProd = (int)$_POST['id'];
    $usuarioGmail = $_SESSION['usuario']['gmail'];

    // Verificar que el producto existe en el carrito
    if (!isset($_SESSION['carrito'][$codProd]) || $_SESSION['carrito'][$codProd] < 1) {
        throw new Exception("Producto no encontrado en el carrito.");
    }

    // Obtener el ID del carrito de la sesión
    if (!isset($_SESSION['CodCarro'])) {
        throw new Exception("Carrito no válido.");
    }

    $codCarro = $_SESSION['CodCarro'];

    // --------------------------------------------------
    // 1. DECREMENTAR LA UNIDAD
    // --------------------------------------------------
    $unidadesActuales = $_SESSION['carrito'][$codProd];
    $nuevasCantidad = $unidadesActuales - 1;

    if ($nuevasCantidad <= 0) {
        // Si llega a 0, eliminar el producto completamente
        
        // Borrar de BD
        $sqlDelete = "DELETE FROM carroproductos WHERE CodCarro = ? AND CodProd = ?";
        $stmtDelete = $bd->prepare($sqlDelete);
        $stmtDelete->execute([$codCarro, $codProd]);

        // Borrar de sesión
        unset($_SESSION['carrito'][$codProd]);

        $response['eliminado'] = true;
    } else {
        // Actualizar en BD
        $sqlUpdate = "UPDATE carroproductos SET Unidades = ? WHERE CodCarro = ? AND CodProd = ?";
        $stmtUpdate = $bd->prepare($sqlUpdate);
        $stmtUpdate->execute([$nuevasCantidad, $codCarro, $codProd]);

        // Actualizar en sesión
        $_SESSION['carrito'][$codProd] = $nuevasCantidad;

        $response['eliminado'] = false;
    }

    // --------------------------------------------------
    // 2. CALCULAR EL NUEVO TOTAL DEL CARRITO
    // --------------------------------------------------
    $nuevoTotal = 0.00;

    foreach ($_SESSION['carrito'] as $idProdSession => $unidadesSession) {
        $sqlPrecio = "SELECT Precio FROM productos WHERE CodProd = ?";
        $stmtPrecio = $bd->prepare($sqlPrecio);
        $stmtPrecio->execute([$idProdSession]);
        $datosProd = $stmtPrecio->fetch(PDO::FETCH_ASSOC);

        if ($datosProd) {
            $nuevoTotal += $datosProd['Precio'] * $unidadesSession;
        }
    }

    $nuevoTotal = round($nuevoTotal, 2);

    // --------------------------------------------------
    // 3. ACTUALIZAR EL TOTAL EN LA TABLA CARRO
    // --------------------------------------------------
    $sqlUpdateTotal = "UPDATE carro SET Total = ? WHERE CodCarro = ?";
    $stmtUpdateTotal = $bd->prepare($sqlUpdateTotal);
    $stmtUpdateTotal->execute([$nuevoTotal, $codCarro]);

    $response['exito'] = true;
    $response['nuevoTotal'] = $nuevoTotal;
    $response['cantidadProductos'] = count($_SESSION['carrito']);

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
?>
