<?php

// Iniciar la sesión
session_start();

// Función para destruir la sesión y cerrar sesión del usuario
function destruirSession(){
    // Limpiar todos los datos de $_SESSION
    $_SESSION = array();
    
    // Destruir la sesión completamente
    session_destroy();

    // Indicar al frontend que todo salió bien
    header('Content-Type: application/json');
    echo json_encode(["status" => "ok"]);
    exit;
}

// Ejecutar la función de logout
destruirSession();
?>

