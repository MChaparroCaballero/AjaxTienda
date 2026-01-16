<?php
require_once 'conexion.php';

//funcion para cargar las categorias de la bd
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
        // En caso de no haber categorías, devolvemos un mensaje de error en JSON
        echo json_encode(["error" => "No hay categorías"]);
    }
    exit;
}
?>