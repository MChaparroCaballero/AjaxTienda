<?php
require_once 'conexion.php';

/**
 * Obtiene o crea el CodCarro del usuario Si CodCarro está en sesión, lo usa Si no, busca en BD. Si no existe, crea uno nuevo, no hace falta session start porque este se usa directamente en comprobar_sesion que ya lo lleva*/
function obtener_codigo_carro($bd, $usuarioGmail) {
    // comprobamos Si está en sesión, para usarlo
    if (isset($_SESSION['CodCarro'])) {
        return (int)$_SESSION['CodCarro'];
    }
    
    //Si no, buscar en BD un carrito no enviado de ese usuario
    $sql = "SELECT CodCarro FROM carro WHERE Usuario = ? AND Enviado = 0";
    $stmt = $bd->prepare($sql);
    $stmt->execute([$usuarioGmail]);
    $carro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    //si existe, usamos ese
    if ($carro) {
        $codCarro = (int)$carro['CodCarro'];
    } else {
        // Si no existe, crear uno nuevo
        $sqlInsert = "INSERT INTO carro (Usuario, Fecha, Enviado, Total) VALUES (?, ?, 0, 0.00)";
        $stmtInsert = $bd->prepare($sqlInsert);
        $stmtInsert->execute([$usuarioGmail, date('Y-m-d H:i:s')]);
        $codCarro = (int)$bd->lastInsertId();
    }
    
    //Guardamos en sesión el nuevo CodCarro
    $_SESSION['CodCarro'] = $codCarro;
    return $codCarro;
}
?>
