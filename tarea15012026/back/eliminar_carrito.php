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
    // Validar sesión
    if (!isset($_SESSION['usuario'])) {
        throw new Exception("Sesión no válida.");
    }

    // Recibimos los datos y los validamos
    if (!isset($_POST['id'])) {
        throw new Exception("Faltan datos.");
    }

    //guardamos los datos en variables una vez validados y convertidos a sus tipos para asegurarnos que lo que tiene que ser entero sea entero
    $codProd = (int)$_POST['id'];
    $usuarioGmail = $_SESSION['usuario']['gmail'];

    // Verificamos que el producto existe en el carrito antes de intentar eliminarlo
    if (!isset($_SESSION['carrito'][$codProd])) {
        throw new Exception("Producto no encontrado en el carrito.");
    }

    // Obtener el ID del carrito de la sesión, primero validamos que existe un carrito en sesion
    if (!isset($_SESSION['CodCarro'])) {
        throw new Exception("Carrito no válido.");
    }

    $codCarro = $_SESSION['CodCarro'];

    //eliminamos el producto del carrito en 4 pasos:
    $sqlDelete = "DELETE FROM carroproductos WHERE CodCarro = ? AND CodProd = ?";
    $stmtDelete = $bd->prepare($sqlDelete);
    $stmtDelete->execute([$codCarro, $codProd]);

    //después lo eliminamos de la sesión
    unset($_SESSION['carrito'][$codProd]);

    //luego calculamos el nuevo total del carrito para que vaya actualizado en el frontend
    $nuevoTotal = 0.00;

    // Recorremos los productos que quedan en el carrito de la sesión para calcular el nuevo total
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

    // por ultimo actualizamos el total en la tabla carro
    $sqlUpdateTotal = "UPDATE carro SET Total = ? WHERE CodCarro = ?";
    $stmtUpdateTotal = $bd->prepare($sqlUpdateTotal);
    $stmtUpdateTotal->execute([$nuevoTotal, $codCarro]);

    //preparamos la respuesta json
    $response['exito'] = true;
    $response['nuevoTotal'] = $nuevoTotal;
    $response['cantidadProductos'] = count($_SESSION['carrito']);

} catch (Exception $e) {
    $response['error'] = $e->getMessage();//en caso de error lo indicamos en la respuesta json para que no colapse
}

ob_end_clean();
echo json_encode($response);
?>
