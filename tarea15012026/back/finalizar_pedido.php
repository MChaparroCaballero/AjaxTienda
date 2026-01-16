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
    // Validamos la sesión
    if (!isset($_SESSION['usuario'])) {
        throw new Exception("Sesión no válida.");
    }

    // Validar que hay un carrito en sesión
    if (!isset($_SESSION['CodCarro'])) {
        throw new Exception("No hay carrito para finalizar.");
    }
    //validar que el carrito no esté vacío
    if (!isset($_SESSION['carrito'])) {
        throw new Exception("Carrito no válido en sesión.");
    }

    // Obtener el código del carrito y el usuario que se va a usar
    $codCarro = (int)$_SESSION['CodCarro'];
    $usuarioGmail = $_SESSION['usuario']['gmail']; // Obtener el gmail del usuario logueado
    
    
    // actualizamos el estado del carrito a enviado y reducimos el stock de los productos comprados en 3 pasos:
    $sqlUpdate = "UPDATE carro SET Enviado = 1 WHERE CodCarro = ?";
    
    $stmtUpdate = $bd->prepare($sqlUpdate);
    $resultUpdate = $stmtUpdate->execute([$codCarro]);

    // Verificamos que se haya actualizado correctamente
    if (!$resultUpdate || $stmtUpdate->rowCount() === 0) {
        throw new Exception("No se pudo actualizar el pedido. Código: " . $codCarro);
    }

    //pasamos a reducir el stock de los productos comprados

    // Obtener todos los productos del carrito desde la BD que se han comprado
    $sqlProductosCarro = "SELECT CodProd, Unidades FROM carroproductos WHERE CodCarro = ?";
    $stmtProductosCarro = $bd->prepare($sqlProductosCarro);
    $stmtProductosCarro->execute([$codCarro]);
    $productosComprados = $stmtProductosCarro->fetchAll(PDO::FETCH_ASSOC);


    // Reducimos stock de cada producto
    foreach ($productosComprados as $producto) {
        $codProd = (int)$producto['CodProd'];
        $unidades = (int)$producto['Unidades'];
        
        $sqlReduceStock = "UPDATE productos SET stock = stock - ? WHERE CodProd = ?";
        $stmtReduceStock = $bd->prepare($sqlReduceStock);
        $resultReduceStock = $stmtReduceStock->execute([$unidades, $codProd]);
        
        if (!$resultReduceStock) {
            throw new Exception("No se pudo reducir el stock del producto: " . $codProd);
        }
        
        }

    //Por ultimo creamos un nuevo carrito al usuario por si quiere seguir comprando que no le modifique el que acaba de finalizar
    $sqlNewCarro = "INSERT INTO carro (Usuario, Fecha, Enviado, Total) VALUES (?, ?, ?, ?)";
    $stmtNewCarro = $bd->prepare($sqlNewCarro);
    $resultNewCarro = $stmtNewCarro->execute([$usuarioGmail, date('Y-m-d H:i:s'),0,0.00]);

    if (!$resultNewCarro) {
        throw new Exception("No se pudo crear el nuevo carrito.");
    }

    //almacenamos el nuevo codigo de carro en una variable
    $newCodCarro = $bd->lastInsertId();
    

    // actualizamos la sesión con el nuevo codigo de carro y vaciamos el array de productos del carrito
    $_SESSION['CodCarro'] = $newCodCarro;
    $_SESSION['carrito'] = []; // Carrito vacío

    //preparamos la respuesta json de éxito
    $response['exito'] = true;
    $response['mensaje'] = "Pedido completado exitosamente y stock actualizado.";
    $response['newCodCarro'] = $newCodCarro;

   
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
?>
