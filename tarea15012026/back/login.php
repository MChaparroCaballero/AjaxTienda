<?php
require_once 'conexion.php';

// Obtener la conexión a la base de datos
$bd = leer_config();

function comprobar_usuario($usuario, $contrasena){
    global $bd;
    
    // Consulta preparada para comprobar usuario y contraseña
    $ins = "select nombre, gmail from usuarios where gmail = ? and clave = ?";
    $stmt = $bd->prepare($ins);
    $resul = $stmt->execute(array($usuario, $contrasena));
    
    // Verificar si hay error en la ejecución
    if (!$resul) {
        throw new Exception("Error al verificar el usuario.");
    }
    
    // Si encontramos exactamente 1 usuario con esas credenciales
    if($stmt->rowCount() === 1){        
        return $stmt->fetch(); // Devolver datos del usuario
    } else {
        return FALSE; // Usuario/contraseña incorrectos
    }
}

//función para cargar el carrito pendiente
function cargar_carro_pendiente($usuario){
    global $bd;
    
    // Consulta para buscar carritos no enviados (Enviado = 0)
    $ins = "select CodCarro, enviado from carro where Usuario = ? and Enviado = 0";
    $stmt = $bd->prepare($ins);
    $resul = $stmt->execute(array($usuario));
    
    if (!$resul) {
        throw new Exception("Error al buscar carro pendiente.");
    }
    
    // Si encuentra un carrito pendiente
    if($stmt->rowCount() === 1){        
        return $stmt->fetch(); // Devolver el carrito
    } else {
        return FALSE; // No hay carrito pendiente
    }
}
// Función para cargar los productos de un carrito pendiente
function cargar_productos_carrito_pendiente($codCarroExistente){
    global $bd;
    $carrito_sesion = [];
    
    // Obtener todos los productos de este carrito
    $sql = "SELECT CodProd, Unidades FROM carroproductos WHERE CodCarro = ?";
    $stmt = $bd->prepare($sql);
        
    $resul = $stmt->execute(array($codCarroExistente));
    if ($resul === false) {
        throw new Exception("Error al ejecutar la consulta de carrito.");
    }

    // Recorrer cada producto del carrito
    foreach($stmt as $producto){
         $codProd = $producto['CodProd'];
         $unidades = (int)$producto['Unidades'];
         
         // Guardar el producto y sus unidades en el array del carrito
         $carrito_sesion[$codProd] = $unidades;
    }

    return $carrito_sesion;
}
// Función principal para iniciar sesión de usuario y basicamente el proceso de login
function iniciar_usuario() {
    // Indicar al navegador que es JSON
    header('Content-Type: application/json');
    $respuesta = [];

    try {
        //Verificamos las credenciales
        $usu = comprobar_usuario($_POST['usuario'], $_POST['clave']);

        if ($usu === false) {
            //si las Credenciales incorrectas
            $respuesta['login'] = false;
            $respuesta['mensaje'] = "Usuario o contraseña incorrectos.";
        } else {
            //si las Credenciales son correctas iniciamos sesión
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Guardar el email/usuario para la consulta de carritos
            $usuario_email = $_POST['usuario'];
            
            // Guardamos datos del usuario en la sesión del servidor
            $_SESSION['usuario'] = $usu; // Esto persiste hasta que se destruya la sesión

            // Intentamos cargar el carrito pendiente del usuario
            try {
                $idCarro = cargar_carro_pendiente($usuario_email);

                if ($idCarro == FALSE) {
                    // Si no hay carrito pendiente creamos uno vacío
                    $_SESSION['carrito'] = [];
                } else {
                    // Si hay carrito pendiente se cargan sus productos
                    $codCarroExistente = $idCarro['CodCarro'];
                    $_SESSION['CodCarro'] = $codCarroExistente; // Guardar el ID del carrito
                    $_SESSION['carrito'] = cargar_productos_carrito_pendiente($codCarroExistente);
                }
                
                // Si todo ha ido bien y el Login es exitoso preparamos la respuesta JSON
                $respuesta['login'] = true;
                $respuesta['nombre'] = $usu['nombre']; // Para mostrar en el HTML el apodo
                $respuesta['num_productos'] = count($_SESSION['carrito']); // Cantidad de productos en el carrito

            } catch (Exception $e) {
                // Si falla la carga del carrito, aún así permitir login
                // (el usuario puede comprar aunque no pueda recuperar carrito anterior)
                $respuesta['login'] = true;
                $respuesta['nombre'] = $usu['nombre'];
                $respuesta['error_carrito'] = "Sesión iniciada, pero no se pudo cargar tu carrito: " . $e->getMessage();
                $_SESSION['carrito'] = []; // Crear carrito vacío
            }
        }
    } catch (Exception $e) {
        // Error crítico en el servidor
        $respuesta['login'] = false;
        $respuesta['mensaje'] = "Error crítico en el servidor: " . $e->getMessage();
    }

    // Devolver la respuesta JSON al frontend
    echo json_encode($respuesta);
    exit; // Detener la ejecución
}

iniciar_usuario();
?>
