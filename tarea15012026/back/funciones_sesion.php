<?php
/**
 * funciones_sesion.php
 * Funciones reutilizables para manejo de sesión y carrito
 */

require_once 'conexion.php';

/**
 * Obtiene o crea el CodCarro del usuario
 * Si CodCarro está en sesión, lo usa
 * Si no, busca en BD. Si no existe, crea uno nuevo
 */
function obtener_codigo_carro($bd, $usuarioGmail) {
    // 1. Si está en sesión, usarlo
    if (isset($_SESSION['CodCarro'])) {
        return (int)$_SESSION['CodCarro'];
    }
    
    // 2. Si no, buscar en BD un carrito no enviado
    $sql = "SELECT CodCarro FROM carro WHERE Usuario = ? AND Enviado = 0";
    $stmt = $bd->prepare($sql);
    $stmt->execute([$usuarioGmail]);
    $carro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($carro) {
        $codCarro = (int)$carro['CodCarro'];
    } else {
        // 3. Si no existe, crear uno nuevo
        $sqlInsert = "INSERT INTO carro (Usuario, Fecha, Enviado, Total) VALUES (?, ?, 0, 0.00)";
        $stmtInsert = $bd->prepare($sqlInsert);
        $stmtInsert->execute([$usuarioGmail, date('Y-m-d H:i:s')]);
        $codCarro = (int)$bd->lastInsertId();
    }
    
    // 4. Guardar en sesión
    $_SESSION['CodCarro'] = $codCarro;
    return $codCarro;
}
?>
