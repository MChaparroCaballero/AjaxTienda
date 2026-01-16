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
    // Doble seguridad: Aunque el JS lo pare, el servidor también debe protegerse
    if (!isset($_SESSION['usuario'])) {
        throw new Exception("Sesión no válida.");
    }

    // Recibir datos
    if (!isset($_POST['id']) || !isset($_POST['cantidad'])) {
        throw new Exception("Faltan datos.");
    }

    $codProd = (int)$_POST['id'];
    $cantidad = (int)$_POST['cantidad'];
    $usuarioGmail = $_SESSION['usuario']['gmail']; // Según tu login guardas 'gmail'

    // --------------------------------------------------
    // 0. VALIDAR CANTIDAD
    // --------------------------------------------------
    if ($cantidad < 1) {
        throw new Exception("La cantidad debe ser mínimo 1.");
    }

    // --------------------------------------------------
    // 1. OBTENER STOCK DISPONIBLE DEL PRODUCTO
    // --------------------------------------------------
    $sqlStock = "SELECT Precio, stock FROM productos WHERE CodProd = ?";
    $stmtStock = $bd->prepare($sqlStock);
    $stmtStock->execute([$codProd]);
    $producto = $stmtStock->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        throw new Exception("Producto no encontrado.");
    }

    $stockDisponible = $producto['stock'];
    $precioProd = $producto['Precio'];

    // --------------------------------------------------
    // 2. GESTIÓN DEL ID DE CARRITO
    // --------------------------------------------------
    $codCarro = null;

    if (isset($_SESSION['CodCarro'])) {
        $codCarro = $_SESSION['CodCarro'];
    } else {
        // Si por lo que sea se perdió la sesión del ID del carro, lo buscamos en BD
        $sql = "SELECT CodCarro FROM carro WHERE Usuario = ? AND Enviado = 0";
        $stmt = $bd->prepare($sql);
        $stmt->execute([$usuarioGmail]);
        $carro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($carro) {
            $codCarro = $carro['CodCarro'];
        } else {
            // Crear carro nuevo
            $sqlInsert = "INSERT INTO carro (Usuario, Fecha, Enviado, Total) VALUES (?, ?, ?, 0.00)";
            $stmtInsert = $bd->prepare($sqlInsert);
            $stmtInsert->execute([$usuarioGmail, date('Y-m-d H:i:s'), 0]);
            $codCarro = $bd->lastInsertId();
        }
        $_SESSION['CodCarro'] = $codCarro; // Actualizamos sesión
    }

    // -------------Gestionamos el detalle del carroproducto-------------------------------------
    
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
        
        $sqlUpdate = "UPDATE carroproductos SET Unidades = ? WHERE CodCarro = ? AND CodProd = ?";
        $stmtUpdate = $bd->prepare($sqlUpdate);
        $stmtUpdate->execute([$nuevaCantidadTotal, $codCarro, $codProd]);
        
        // ¡IMPORTANTE! Actualizamos la variable de sesión
        $_SESSION['carrito'][$codProd] = $nuevaCantidadTotal;

    } else {
        //si no existe en el carro, insertamos el producto
        
        // Validamos que no supere el stock 
        if ($cantidad > $stockDisponible) {
            throw new Exception("No hay suficiente stock. Disponibles: " . $stockDisponible . ", solicitadas: " . $cantidad);
        }
        
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

ob_end_clean();
echo json_encode($response);
?>