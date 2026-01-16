<?php
// Configuración para JSON limpio
header('Content-Type: application/json; charset=utf-8');
ob_start();

require_once 'funciones_sesion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['exito' => false];
$bd = leer_config();
try {
    // Validamos la sesión
    if (!isset($_SESSION['usuario'])) {
        throw new Exception("Sesión no válida.");
    }

    // Recibimos los datos
    if (!isset($_POST['id'])) {
        throw new Exception("Faltan datos.");
    }

    //guardamos los datos en variables una vez validados
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

    // decrementamos las unidades del producto en el carrito
    $unidadesActuales = $_SESSION['carrito'][$codProd];
    $nuevasCantidad = $unidadesActuales - 1;

    if ($nuevasCantidad <= 0) {
        // Si llega a 0, se elimina el producto completamente
        $sqlDelete = "DELETE FROM carroproductos WHERE CodCarro = ? AND CodProd = ?";
        $stmtDelete = $bd->prepare($sqlDelete);
        $stmtDelete->execute([$codCarro, $codProd]);

        // y lo borramos de la sesión
        unset($_SESSION['carrito'][$codProd]);

        $response['eliminado'] = true;
    } else {
        // Si es mayor que 0 se actualiza en BD
        $sqlUpdate = "UPDATE carroproductos SET Unidades = ? WHERE CodCarro = ? AND CodProd = ?";
        $stmtUpdate = $bd->prepare($sqlUpdate);
        $stmtUpdate->execute([$nuevasCantidad, $codCarro, $codProd]);

        // Actualizar en sesión
        $_SESSION['carrito'][$codProd] = $nuevasCantidad;
        // Indicamos que no se ha eliminado el producto
        $response['eliminado'] = false;
    }

    //luego calculamos el nuevo total del carrito para que vaya actualizado en el frontend
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

    //redondeamos el total
    $nuevoTotal = round($nuevoTotal, 2);

    //actualizamos el total en la tabla carro
    $sqlUpdateTotal = "UPDATE carro SET Total = ? WHERE CodCarro = ?";
    $stmtUpdateTotal = $bd->prepare($sqlUpdateTotal);
    $stmtUpdateTotal->execute([$nuevoTotal, $codCarro]);

    //preparamos la respuesta json
    $response['exito'] = true;
    $response['nuevoTotal'] = $nuevoTotal;
    $response['cantidadProductos'] = count($_SESSION['carrito']);

} catch (Exception $e) {
    //en caso de error lo indicamos en la respuesta json para que no colapse
    $response['error'] = $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
?>
