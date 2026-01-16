<?php 
function leer_config(){
    $cadena_conexion = 'mysql:dbname=nutrimax;host=127.0.0.1;port=3306';
    $usuario = 'root';
    $clave = '';

    // Intentamos conectar
    try {
        $bd = new PDO($cadena_conexion, $usuario, $clave);
        
        // Configuramos el manejo de errores para que lance excepciones si hay error sql pues nos lanza esa excepción
        $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $bd;
        
    } catch (PDOException $e) {
        // En lugar de un throw genérico, lanzamos una excepción específica o manejamos el error
        // En un entorno real, no mostrarías $e->getMessage() al usuario final por seguridad
        throw new Exception("No se pudo conectar a la base de datos. Error: " . $e->getMessage());
    }
}
?>