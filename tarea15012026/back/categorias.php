<?php
require_once 'conexion.php';
function cargar_categorias(){
    $bd = leer_config();
    $ins = "select CodCat, Nombre from categoria"; 
    $resul = $bd->query($ins);    
    
    if (!$resul || $resul->rowCount() === 0) {
        return FALSE;
    }
    
    //  Convertimos el resultado en un array asociativo
    return $resul->fetchAll(PDO::FETCH_ASSOC); 
}

// Bloque para responder a la petición AJAX pasandolo a json
if (isset($_GET['cargarCats'])) {
    header('Content-Type: application/json');
    $categorias = cargar_categorias();
    
    if ($categorias) {
        echo json_encode($categorias);
    } else {
        echo json_encode(["error" => "No hay categorías"]);
    }
    exit;
}
?>