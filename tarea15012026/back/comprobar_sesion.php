<?php
session_start();
require_once 'funciones_sesion.php';
header('Content-Type: application/json');

if (isset($_SESSION['usuario'])) {
    // Si la sesión existe, devolvemos los datos del usuario
    $numProductos = 0;
    if (isset($_SESSION['carrito'])) {
        $numProductos = count($_SESSION['carrito']);
    }
    
    $bd = leer_config();
    // Obtener el código del carro que usa el usuario
    $codCarro = obtener_codigo_carro($bd, $_SESSION['usuario']['gmail']);
    
    // Devolver los datos en formato JSON
    echo json_encode([
        "logueado" => true,
        "email" => $_SESSION['usuario']['gmail'],
        "nombre" => $_SESSION['usuario']['nombre'],
        "num_productos" => $numProductos,
        "codCarro" => $codCarro
    ]);
} else {
    echo json_encode(["logueado" => false]);
}
exit;
?>
?>