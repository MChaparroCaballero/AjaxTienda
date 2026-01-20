<?php
header('Content-Type: application/json'); // Indicamos que la respuesta es JSON
require_once 'conexion.php'; 

try {
    $bd = leer_config();
    
    // Verificamos si recibimos el ID de la categoría
    $codCat = isset($_GET['catID']) ? $_GET['catID'] : null;

    if ($codCat) {
        // Consulta para obtener los productos de la categoría dada
        $ins = "SELECT CodProd, Nombre, Descripcion, Stock, Precio FROM productos WHERE CodCat = ?";
        $stmt = $bd->prepare($ins);
        $stmt->execute([$codCat]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($productos);
    } else {
        echo json_encode([]); // Si no hay ID, enviamos un array vacío
    }
} catch (Exception $e) {
    // Si hay un error de SQL, lo enviamos como JSON para no romper el JS
    echo json_encode(["error" => $e->getMessage()]);
}
exit;

?>
