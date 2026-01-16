<?php
// Establecer el header JSON ANTES de cualquier otra cosa
header('Content-Type: application/json; charset=utf-8');
ob_start(); // Capturar cualquier salida accidental

// Iniciar sesión PRIMERO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';

function cargar_productos_carrito() {
    $listaProductos = [];
    $totalCarrito = 0.00;
    $bd = leer_config();
    
    try {
        // 1. Verificar si hay carrito en la sesión
        if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
            ob_end_clean();
            echo json_encode(['productos' => [], 'total' => 0.00]);
            exit;
        }

        // 2. Recorrer la sesión para buscar los detalles de cada producto
        // $_SESSION['carrito'] es tipo [ id => cantidad ]
        foreach ($_SESSION['carrito'] as $codProd => $unidades) {
            
            $sql = "SELECT CodProd, Nombre, Descripcion, Stock, Precio FROM productos WHERE CodProd = ?";
            $stmt = $bd->prepare($sql);
            $stmt->execute([$codProd]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($producto) {
                // Convertir unidades y precio a números
                $unidades = (int)$unidades;
                $precio = (float)$producto['Precio'];
                $precioTotal = $precio * $unidades;
                
                // Añadimos los datos que necesita el frontend
                $producto['unidades_compra'] = $unidades;
                $producto['precio'] = $precio;
                $producto['precio_total'] = round($precioTotal, 2);
                
                $totalCarrito += $precioTotal;
                
                // Lo metemos en la lista final
                $listaProductos[] = $producto;
            }
        }

        // Redondear el total
        $totalCarrito = round($totalCarrito, 2);

        // 3. Devolver la lista completa al JavaScript con el total
        ob_end_clean();
        echo json_encode([
            'productos' => $listaProductos,
            'total' => $totalCarrito
        ]);

    } catch (Exception $e) {
        ob_end_clean();
        echo json_encode(['error' => 'Error al cargar el carrito: ' . $e->getMessage()]);
    }
}

cargar_productos_carrito();
?>