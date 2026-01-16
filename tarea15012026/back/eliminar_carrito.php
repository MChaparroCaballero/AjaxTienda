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
    if (!isset($_SESSION['carrito'][$codProd])) {
        throw new Exception("Producto no encontrado en el carrito.");
    }

    // Obtener el ID del carrito de la sesión
    if (!isset($_SESSION['CodCarro'])) {
        throw new Exception("Carrito no válido.");
    }

    $codCarro = $_SESSION['CodCarro'];

    // --------------------------------------------------
    // 1. ELIMINAR EL PRODUCTO DE BD
    // --------------------------------------------------
    $sqlDelete = "DELETE FROM carroproductos WHERE CodCarro = ? AND CodProd = ?";
    $stmtDelete = $bd->prepare($sqlDelete);
    $stmtDelete->execute([$codCarro, $codProd]);

    // --------------------------------------------------
    // 2. ELIMINAR DE LA SESIÓN
    // --------------------------------------------------
    unset($_SESSION['carrito'][$codProd]);

    // --------------------------------------------------
    // 3. CALCULAR EL NUEVO TOTAL DEL CARRITO
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
    // 4. ACTUALIZAR EL TOTAL EN LA TABLA CARRO
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
