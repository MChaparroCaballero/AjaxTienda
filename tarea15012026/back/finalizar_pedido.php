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

error_log("=== INICIANDO FINALIZAR PEDIDO ===");
error_log("Usuario en sesión: " . (isset($_SESSION['usuario']) ? "Sí" : "No"));
error_log("CodCarro en sesión: " . (isset($_SESSION['CodCarro']) ? $_SESSION['CodCarro'] : "No"));

try {
    // Validar sesión
    if (!isset($_SESSION['usuario'])) {
        throw new Exception("Sesión no válida.");
    }

    // Validar que hay un carrito en sesión
    if (!isset($_SESSION['CodCarro'])) {
        throw new Exception("No hay carrito para finalizar.");
    }

    if (!isset($_SESSION['carrito'])) {
        throw new Exception("Carrito no válido en sesión.");
    }

    $codCarro = $_SESSION['CodCarro'];
    $usuarioGmail = $_SESSION['usuario']['gmail']; // Obtener el gmail del usuario logueado
    
    error_log("CodCarro: " . $codCarro);
    error_log("Usuario: " . $usuarioGmail);

    // --------------------------------------------------
    // 1. ACTUALIZAR EL ESTADO DEL CARRO A ENVIADO (1)
    // --------------------------------------------------
    $sqlUpdate = "UPDATE carro SET Enviado = ? WHERE CodCarro = ?";
    error_log("Ejecutando UPDATE: " . $sqlUpdate . " con CodCarro: " . $codCarro);
    
    $stmtUpdate = $bd->prepare($sqlUpdate);
    $resultUpdate = $stmtUpdate->execute([1,$codCarro]);

    error_log("Resultado UPDATE execute: " . ($resultUpdate ? "true" : "false"));
    error_log("Filas afectadas: " . $stmtUpdate->rowCount());

    if (!$resultUpdate || $stmtUpdate->rowCount() === 0) {
        throw new Exception("No se pudo actualizar el pedido. Código: " . $codCarro);
    }

    // --------------------------------------------------
    // 2. CREAR UN NUEVO CARRITO VACÍO PARA EL USUARIO
    // --------------------------------------------------
    $sqlNewCarro = "INSERT INTO carro (Usuario, Fecha, Enviado, Total) VALUES (?, ?, ?, ?)";
    $stmtNewCarro = $bd->prepare($sqlNewCarro);
    $resultNewCarro = $stmtNewCarro->execute([$usuarioGmail, date('Y-m-d H:i:s'),0,0.00]);

    if (!$resultNewCarro) {
        throw new Exception("No se pudo crear el nuevo carrito.");
    }

    $newCodCarro = $bd->lastInsertId();
    error_log("Nuevo CodCarro creado: " . $newCodCarro);

    // --------------------------------------------------
    // 3. ACTUALIZAR LA SESIÓN
    // --------------------------------------------------
    $_SESSION['CodCarro'] = $newCodCarro;
    $_SESSION['carrito'] = []; // Carrito vacío

    $response['exito'] = true;
    $response['mensaje'] = "Pedido completado exitosamente.";
    $response['newCodCarro'] = $newCodCarro;

    error_log("ÉXITO: Pedido finalizado correctamente");

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    error_log("ERROR en finalizar_pedido.php: " . $e->getMessage());
}

ob_end_clean();
echo json_encode($response);
error_log("Respuesta JSON: " . json_encode($response));
?>
