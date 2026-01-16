<?php
// Configuración para JSON limpio
header('Content-Type: application/json; charset=utf-8');
ob_start(); //para evitar problemas de cabeceras ya enviadas y garantizar que no haya salida antes del json, y que sea una salida de json valida

require_once 'funciones_sesion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['exito' => false];
$bd = leer_config(); 
try {
    // Doble seguridad: Aunque el JS lo pare, el servidor también debe protegerse, validando la sesión
    if (!isset($_SESSION['usuario'])) {
        throw new Exception("Sesión no válida.");
    }

    // Recibir datos
    if (!isset($_POST['id']) || !isset($_POST['cantidad'])) {
        throw new Exception("Faltan datos.");
    }

    $codProd = (int)$_POST['id'];
    $cantidad = (int)$_POST['cantidad'];
    $usuarioGmail = $_SESSION['usuario']['gmail'];

    // Antes de añadir nada validemos la cantidad para evitar tonterías de usuarios DE SUMAR +0 O NEGATIVAS
    if ($cantidad < 1) {
        throw new Exception("La cantidad debe ser mínimo 1.");
    }

    // Para controlar que el usuario no pueda basicamente añadir más productos de los que hay en stock, primero obtenemos el stock del producto
    $sqlStock = "SELECT Precio, stock FROM productos WHERE CodProd = ?";
    $stmtStock = $bd->prepare($sqlStock);
    $stmtStock->execute([$codProd]);
    $producto = $stmtStock->fetch(PDO::FETCH_ASSOC);

    // Si no existe el producto nos lanza excepción
    if (!$producto) {
        throw new Exception("Producto no encontrado.");
    }

    //guardamos el stock y precio del producto en variables
    $stockDisponible = $producto['stock'];
    $precioProd = $producto['Precio'];

    //miramos a ver si el usuario ya tiene un carro creado  y sino es así, se lo creamos
    $codCarro = obtener_codigo_carro($bd, $usuarioGmail);

    
    // Comprobamos si ya existe ese producto en este carro
    $sqlCheck = "SELECT Unidades FROM carroproductos WHERE CodCarro = ? AND CodProd = ?";
    $stmtCheck = $bd->prepare($sqlCheck);
    $stmtCheck->execute([$codCarro, $codProd]);
    $prodEnCarro = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($prodEnCarro) {
        // si existe sumamos lo que había más lo nuevo
        $nuevaCantidadTotal = $prodEnCarro['Unidades'] + $cantidad;
        
         // Validamos que no supere el stock 
        if ($nuevaCantidadTotal > $stockDisponible) {
            throw new Exception("No hay suficiente stock. Disponibles: " . $stockDisponible . ", solicitadas: " . $nuevaCantidadTotal);
        }
        
        // Actualizamos la cantidad en la base de datos
        $sqlUpdate = "UPDATE carroproductos SET Unidades = ? WHERE CodCarro = ? AND CodProd = ?";
        $stmtUpdate = $bd->prepare($sqlUpdate);
        $stmtUpdate->execute([$nuevaCantidadTotal, $codCarro, $codProd]);
        
        // Actualizamos la variable de sesión
        $_SESSION['carrito'][$codProd] = $nuevaCantidadTotal;

    } else {
        //si no existe en el carro, insertamos el producto
        
        // Validamos que no supere el stock 
        if ($cantidad > $stockDisponible) {
            throw new Exception("No hay suficiente stock. Disponibles: " . $stockDisponible . ", solicitadas: " . $cantidad);
        }
        //lo mismo, insertamos el nuevo producto en la tabla carroproductos
        $sqlInsertProd = "INSERT INTO carroproductos (CodCarro, CodProd, Unidades) VALUES (?, ?, ?)";
        $stmtInsertProd = $bd->prepare($sqlInsertProd);
        $stmtInsertProd->execute([$codCarro, $codProd, $cantidad]);
        
        // Actualizamos la variable de sesión
        $_SESSION['carrito'][$codProd] = $cantidad;
    }

    // -------------Calculamos el nuevo total a medida que añaden o modifican productos-------------------------------------
    $nuevoTotal = 0.00;

    // Recorrer todos los productos en la sesión
    foreach ($_SESSION['carrito'] as $idProdSession => $unidadesSession) {
        // Obtener precio de cada producto para poder calcular el nuevo total
        $sqlPrecio = "SELECT Precio FROM productos WHERE CodProd = ?";
        $stmtPrecio = $bd->prepare($sqlPrecio);
        $stmtPrecio->execute([$idProdSession]);
        $datosProd = $stmtPrecio->fetch(PDO::FETCH_ASSOC);
        
        if ($datosProd) {
            $nuevoTotal += $datosProd['Precio'] * $unidadesSession;
        }
    }

    // Redondear a 2 decimales para que cuadre bien
    $nuevoTotal = round($nuevoTotal, 2);

    //Actualizar el total en la tabla carro
    $sqlUpdateTotal = "UPDATE carro SET Total = ? WHERE CodCarro = ?";
    $stmtUpdateTotal = $bd->prepare($sqlUpdateTotal);
    $stmtUpdateTotal->execute([$nuevoTotal, $codCarro]);

    $response['exito'] = true;
    $response['nuevoTotal'] = $nuevoTotal;
    $response['cantidadProductos'] = count($_SESSION['carrito']);

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

ob_end_clean();//limpiamos el buffer de salida para evitar problemas de cabeceras ya enviadas y garantizar que no haya salida antes del json, y que sea una salida de json valida
echo json_encode($response);
?>