/**Función para realizar el login del usuario. Envía las credenciales al servidor y maneja la respuesta*/
function login(){
    // Obtener los valores del formulario
    var usuario = document.getElementById("usuario").value;
    var contrasena = document.getElementById("contrasena").value;
    
    // Preparar los datos para enviar
    var datos = "usuario=" + usuario + "&clave=" + contrasena;
    
    // Crear petición AJAX
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        // Cuando la petición se completa exitosamente
        if (this.readyState == 4 && this.status == 200) {
            var respuesta = JSON.parse(this.responseText);
            
            // Si el login fue exitoso
            if (respuesta.login) {
                // Eliminar mensaje de error previo si existe
                var alertError = document.getElementById("alertError");
                if (alertError) {
                    alertError.remove();
                }
                
                // Guardar datos en sessionStorage para que sean visibles en DevTools > Application
                sessionStorage.setItem('usuario', respuesta.nombre);
                sessionStorage.setItem('logueado', 'true');
                if (respuesta.num_productos !== undefined) {
                    sessionStorage.setItem('num_productos', respuesta.num_productos);
                }
                
                // Ocultar el formulario de login
                var formulario = document.getElementById("formularioLogin");
                formulario.classList.add("d-none");
                
                // Ocultar el botón de "Aceptar"
                var botonIniciar = document.getElementById("btnIniciarSesion");
                botonIniciar.classList.add("d-none");
                
                // Mostrar el botón de "Cerrar Sesión"
                var botonCerrar = document.getElementById("btnCerrarSesion");
                botonCerrar.classList.remove("d-none");
                botonCerrar.style.display = "inline";
                
                // Mostrar el nombre del usuario
                var apodo = document.getElementById("datosUsuario");
                apodo.classList.remove("d-none");
                apodo.style.display = "inline";
                apodo.innerHTML = "Bienvenido " + respuesta.nombre;
                
                // Si hay productos en la cesta, mostrarla automáticamente
                if (respuesta.num_productos && respuesta.num_productos > 0) {
                    mostrarCesta();
                }
            } else {
                // Si el login falló (usuario/contraseña incorrectos) se muestra el error rojo con un alert
                var alertError = document.getElementById("alertError");
                if (!alertError) {
                    // Creamos el alert 
                    alertError = document.createElement("div");
                    alertError.id = "alertError";
                    alertError.className = "alert alert-danger alert-dismissible fade show m-0 ms-3";
                    alertError.style.padding = "0.5rem 1rem";
                    alertError.innerHTML = `
                        <strong>Error:</strong> ${respuesta.mensaje}
                        <button type="button" class="btn-close" style="padding: 0.25rem;" onclick="this.parentElement.remove()"></button>
                    `;
                    document.getElementById("contenedorLogin").appendChild(alertError);
                } else {
                    // Actualizar el mensaje si ya existe
                    alertError.innerHTML = `
                        <strong>Error:</strong> ${respuesta.mensaje}
                        <button type="button" class="btn-close" style="padding: 0.25rem;" onclick="this.parentElement.remove()"></button>
                    `;
                }
            }
           
        }
    };
    
    // Enviar petición POST al servidor
    xhttp.open("POST", "../back/login.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(datos);
}


/**Función para cerrar la sesión del usuario,limpia los datos de sesión del servidor y del navegador*/
function cerrarSesion() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var respuesta = JSON.parse(this.responseText);
            
            if (respuesta.status === "ok") {
                // Limpiar sessionStorage del navegador
                sessionStorage.clear();
                
                // Volver a mostrar el formulario de login
                var formulario = document.getElementById("formularioLogin");
                formulario.classList.remove("d-none");
                
                // Mostrar el botón de "Aceptar"
                var botonIniciar = document.getElementById("btnIniciarSesion");
                botonIniciar.classList.remove("d-none");
                
                // Ocultar los datos del usuario
                document.getElementById("datosUsuario").classList.add("d-none");
                
                // Ocultar el botón de "Cerrar Sesión"
                document.getElementById("btnCerrarSesion").classList.add("d-none");

                // Limpiar los campos del formulario
                document.getElementById("usuario").value = "";
                document.getElementById("contrasena").value = "";
                
            }
        }
    };
    // Llamar al archivo logout.php para destruir la sesión en el servidor
    xhttp.open("GET", "../back/logout.php", true);
    xhttp.send();
}

/*Función para cargar las categorías disponibles desde el servidor*/
function cargarCategorias(){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // Parsear la respuesta JSON con las categorías
            var categorias = JSON.parse(this.responseText);
            var contenedor = document.getElementById("contenedorCategorias");
            contenedor.innerHTML = "";

            // Crear un elemento <li> para cada categoría
            categorias.forEach(cat => {
                var li = document.createElement("li");
                li.className = "nav-item";
                
                // Crear el enlace con el evento onclick para cargar productos
                li.innerHTML = `<a class="nav-link" href="#" onclick="cargarProductos(this, ${cat.CodCat})">${cat.Nombre}</a>`;
                
                contenedor.appendChild(li);
            });
        }
    };
    // Solicitar las categorías al servidor
    xhttp.open("GET", "../back/categorias.php?cargarCats=true", true);
    xhttp.send();
}
function cargarProductos(elemento, id){
    // Resaltar la pestaña activa quitando la clase 'active' de todas las demás
    var links = document.querySelectorAll('.nav-link');
    links.forEach(l => l.classList.remove('active'));
    elemento.classList.add('active');

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log("Respuesta del servidor:", this.responseText);
            
            try {
                var productos = JSON.parse(this.responseText);
                var contenedor = document.getElementById("contenedorProductos");
                contenedor.innerHTML = "";

                // Verificar si hay error en la respuesta
                if (productos.error) {
                    contenedor.innerHTML = "<p class='text-center w-100 text-danger'>Error: " + productos.error + "</p>";
                    return;
                }

                // Verificar si no hay productos
                if (productos.length === 0) {
                    contenedor.innerHTML = "<p class='text-center w-100'>No hay productos disponibles.</p>";
                    return;
                }

                // Crear una tarjeta (card) para cada producto
                productos.forEach(p => {
                    var rutaImagen = '../img/' + p.CodProd + '.png';
                    contenedor.innerHTML += `
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm d-flex flex-column">
                            <img src="${rutaImagen}" class="card-img-top" alt="${p.Nombre}" 
                                     style="height: 200px; object-fit: contain; padding: 10px;">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">${p.Nombre}</h5>
                                    <p class="card-text text-muted small">${p.Descripcion}</p>
                                    <hr>
                                    <div class="mb-3">
                                        <span class="badge bg-secondary">Stock: ${p.Stock}</span>
                                    </div>
                                    <div class="mt-auto text-center">
                                        <button class="btn btn-sm btn-success w-100" onclick="abrirModal('${p.CodProd}', '${p.Nombre}', '${p.Descripcion}', ${p.Stock})">Añadir</button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
            } catch (e) {
                console.error("Error al procesar los productos:", e);
            }
        }
    };
    // Solicitar los productos de la categoría seleccionada
    xhttp.open("GET", "../back/productos.php?catID=" + id, true);
    xhttp.send();
}

/**Función para verificar si hay una sesión activa en el servidor Se ejecuta al cargar la página para mantener al usuario logueado después de refrescar*/
function verificarSesion() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var respuesta = JSON.parse(this.responseText);
            console.log("Estado de sesión:", respuesta);
            
            // Si hay una sesión activa en el servidor
            if (respuesta.logueado) {
                // Guardar datos en sessionStorage para que sean visibles en DevTools > Application
                sessionStorage.setItem('usuario', respuesta.nombre);
                sessionStorage.setItem('email', respuesta.email);
                sessionStorage.setItem('logueado', 'true');
                if (respuesta.num_productos !== undefined) {
                    sessionStorage.setItem('num_productos', respuesta.num_productos);
                }
                if (respuesta.codCarro !== undefined) {
                    sessionStorage.setItem('codCarro', respuesta.codCarro);
                }
                
                // Ocultar el formulario de login
                var formulario = document.getElementById("formularioLogin");
                formulario.classList.add("d-none");
                
                // Ocultar el botón de "Aceptar"
                var botonIniciar = document.getElementById("btnIniciarSesion");
                botonIniciar.classList.add("d-none");
                
                // Mostrar el nombre del usuario
                var apodo = document.getElementById("datosUsuario");
                apodo.classList.remove("d-none");
                apodo.style.display = "inline";
                apodo.innerHTML = respuesta.nombre;
                
                // Mostrar el botón de "Cerrar Sesión"
                var botonCerrar = document.getElementById("btnCerrarSesion");
                botonCerrar.classList.remove("d-none");
                botonCerrar.style.display = "inline";
            }
        }
    };
    // Consultar al servidor si hay una sesión activa
    xhttp.open("GET", "../back/comprobar_sesion.php", true);
    xhttp.send();
}


window.onload = function() {
    cargarCategorias();           // Cargar las pestañas de categorías
    verificarSesion();            // Verificar si hay sesión activa (clave para mantener login)
    cargarProductosPorDefecto(1); // Mostrar productos de la primera categoría
};

//Similar a cargarProductos, pero se ejecuta automáticamente sin click del usuario, es para cargar un predefinido//
function cargarProductosPorDefecto(idCategoria) {
    console.log("Cargando productos de categoría:", idCategoria);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log("Respuesta productos por defecto:", this.responseText);
            try {
                var productos = JSON.parse(this.responseText);
                var contenedor = document.getElementById("contenedorProductos");
                contenedor.innerHTML = "";

                // Verificar si hay error en la respuesta
                if (productos.error) {
                    contenedor.innerHTML = "<p class='text-center w-100 text-danger'>Error: " + productos.error + "</p>";
                    return;
                }

                // Verificar si no hay productos
                if (productos.length === 0) {
                    contenedor.innerHTML = "<p class='text-center w-100'>No hay productos disponibles.</p>";
                    return;
                }

                // Crear una tarjeta (card) para cada producto
                productos.forEach(p => {
                    var rutaImagen = '../img/' + p.CodProd + '.png';
                    contenedor.innerHTML += `
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm d-flex flex-column">
                            <img src="${rutaImagen}" class="card-img-top" alt="${p.Nombre}" 
                                     style="height: 200px; object-fit: contain; padding: 10px;">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">${p.Nombre}</h5>
                                    <p class="card-text text-muted small">${p.Descripcion}</p>
                                    <hr>
                                    <div class="mb-3">
                                        <span class="badge bg-secondary">Stock: ${p.Stock}</span>
                                    </div>
                                    <div class="mt-auto text-center">
                                        <button class="btn btn-sm btn-success w-100" onclick="abrirModal('${p.CodProd}', '${p.Nombre}', '${p.Descripcion}', ${p.Stock})">Añadir</button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
                
                // Marca la primera categoría como activa después de un pequeño delay
                // (para asegurar que las categorías ya se han cargado)
                setTimeout(() => {
                    var primerLink = document.querySelector('.nav-link');
                    if (primerLink) {
                        primerLink.classList.add('active');
                    }
                }, 100);
            } catch (e) {
                console.error("Error al procesar los productos:", e);
            }
        }
    };
    // Solicitar los productos de la categoría especificada
    xhttp.open("GET", "../back/productos.php?catID=" + idCategoria, true);
    xhttp.send();
}


function mostrarCesta(){
    var tarjetaPrincipal = document.getElementById("contenedorCesta");
    var cuerpoCesta = document.getElementById("contenedorCestaBody");

    // Limpiar contenedor anterior
    cuerpoCesta.innerHTML = '';

    tarjetaPrincipal.classList.remove("d-none");
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            try {
                var respuesta = JSON.parse(this.responseText);

                // Ponemos el título
                cuerpoCesta.insertAdjacentHTML('beforeend', "<h1 style='font-size: 1.2rem; font-weight: bold; margin-bottom: 20px;'>Cesta de compra</h1>");

                // Verificar si hay productos
                if (respuesta.productos && respuesta.productos.length > 0) {
                    
                    // Crear contenedor para productos
                    var htmlProductos = '<div class="cesta-productos">';
                    
                    respuesta.productos.forEach(p => {
                        var precioPorUnidad = parseFloat(p.precio).toFixed(2);
                        var precioTotal = parseFloat(p.precio_total).toFixed(2);
                        var unidades = p.unidades_compra;
                        var codProd = p.CodProd;
                        
                        // Construimos el HTML de cada producto en la cesta
                        htmlProductos += `
                            <div class="card mb-3" style="border-left: 4px solid #007bff;">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <h6 class="card-title mb-1">${p.Nombre}</h6>
                                            <small class="text-muted">(Código: ${codProd})</small>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <button class="btn btn-sm btn-outline-danger" onclick="decrementarCarrito(${codProd})">−</button>
                                                <input type="number" class="form-control form-control-sm text-center" value="${unidades}" min="1" style="width: 60px;" readonly>
                                                <button class="btn btn-sm btn-outline-success" onclick="incrementarCarrito(${codProd})">+</button>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <div class="text-end">
                                                <small class="text-muted d-block">Unitario</small>
                                                <strong>$${precioPorUnidad}</strong>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <div class="text-end">
                                                <small class="text-muted d-block">Subtotal</small>
                                                <strong class="text-success">$${precioTotal}</strong>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-1">
                                            <button class="btn btn-sm btn-danger w-100" onclick="eliminarProductoCarrito(${codProd})">
                                                <img src="../img/iconos/papelera.png" alt="Eliminar" style="width: 20px; height: 20px;">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    htmlProductos += '</div>';
                    cuerpoCesta.insertAdjacentHTML('beforeend', htmlProductos);

                    // Mostrar total del carrito
                    var totalCarrito = parseFloat(respuesta.total).toFixed(2);
                    var htmlTotal = `
                        <div class="card mt-4" style="background-color: #f8f9fa; border: 2px solid #28a745;">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-10">
                                        <h5 class="mb-0" style="font-weight: bold;">TOTAL DEL CARRITO</h5>
                                    </div>
                                    <div class="col-md-2">
                                        <h4 class="text-success text-end mb-0">$${totalCarrito}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    cuerpoCesta.insertAdjacentHTML('beforeend', htmlTotal);

                    // Botón para procesar pedido
                    cuerpoCesta.insertAdjacentHTML('beforeend', '<button class="btn btn-success w-100 mt-3" style="padding: 12px; font-size: 1.1rem;" onclick="finalizarPedido()">✓ Comprar Pedido</button>');

                } else {
                    cuerpoCesta.insertAdjacentHTML('beforeend', "<p class='text-muted text-center' style='padding: 30px;'>Tu cesta está vacía.</p>");
                }

            } catch (e) {
                console.error("Error JS:", e);
            }
        }
    };

    xhttp.open("GET", "../back/carro.php", true);
    xhttp.send();
}

// Función para incrementar unidades en el carrito
function incrementarCarrito(codProd) {
    var formData = new FormData();
    formData.append('id', codProd);
    formData.append('cantidad', 1);
    
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            try {
                var respuesta = JSON.parse(this.responseText);
                if (respuesta.exito) {
                    mostrarCesta(); // Recargar la cesta
                } else {
                    mostrarAlertaError(respuesta.error);
                }
            } catch (e) {
                console.error("Error JS:", e);
            }
        }
    };
    
    xhttp.open("POST", "../back/anadir_carrito.php", true);
    xhttp.send(formData);
}

// Función para decrementar unidades en el carrito
function decrementarCarrito(codProd) {
    var formData = new FormData();
    formData.append('id', codProd);
    
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            try {
                var respuesta = JSON.parse(this.responseText);
                if (respuesta.exito) {
                    mostrarCesta(); // Recargar la cesta
                } else {
                    alert('Error: ' + respuesta.error);
                }
            } catch (e) {
                console.error("Error JS:", e);
            }
        }
    };
    
    xhttp.open("POST", "../back/decrementar_carrito.php", true);
    xhttp.send(formData);
}

// Función para eliminar un producto del carrito
function eliminarProductoCarrito(codProd) {
    if (confirm('¿Estás seguro de que deseas eliminar este producto del carrito?')) {
        var formData = new FormData();
        formData.append('id', codProd);
        
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                try {
                    var respuesta = JSON.parse(this.responseText);
                    if (respuesta.exito) {
                        mostrarCesta(); // Recargar la cesta
                    } else {
                        alert('Error: ' + respuesta.error);
                    }
                } catch (e) {
                    console.error("Error JS:", e);
                }
            }
        };
        
        xhttp.open("POST", "../back/eliminar_carrito.php", true);
        xhttp.send(formData);
    }
}


// Variables globales para saber qué estamos comprando
let productoActualID = null;
let stockActual = 0;

// 1. ABRIR EL MODAL
function abrirModal(id, nombre, descripcion, stock) {
    // Guardamos los datos en variables globales para usarlos luego
    productoActualID = id;
    stockActual = parseInt(stock);

    // Rellenamos el HTML del modal con los datos del producto
    document.getElementById("modalTitulo").innerText = nombre;
    document.getElementById("modalDescripcion").innerText = descripcion;
    
    // Reseteamos la cantidad a 1 siempre que abrimos
    document.getElementById("modalCantidad").value = 1;

    // Mostramos el modal quitando la clase d-none
    document.getElementById("modalOverlay").classList.remove("d-none");
}

// 2. CERRAR EL MODAL
function cerrarModal() {
    document.getElementById("modalOverlay").classList.add("d-none");
    productoActualID = null; // Limpiamos variable
}

// 3. AJUSTAR CANTIDAD (+ / -)
function ajustarCantidad(cambio) {
    let input = document.getElementById("modalCantidad");
    let valorActual = parseInt(input.value);
    let nuevoValor = valorActual + cambio;

    // Validaciones: No bajar de 1 y no superar el stock
    if (nuevoValor >= 1 && nuevoValor <= stockActual) {
        input.value = nuevoValor;
    } else if (nuevoValor > stockActual) {
        alert("¡No hay suficiente stock! Máximo: " + stockActual);
    }
}

// 4. CONFIRMAR (Paso previo al backend)
function confirmarAgregarAlCarro() {
    let estaLogueado = sessionStorage.getItem('logueado');

    if (estaLogueado !== 'true') {
        mostrarAlertaError("Debes iniciar sesión para poder comprar.");
        cerrarModal(); // Cerramos el modal
        
        // Opcional: Si quieres que el foco vaya al usuario
        document.getElementById("usuario").focus(); 
        return; // ¡IMPORTANTE! Esto detiene la función aquí. No se envía nada al servidor.
    }
    let cantidad = document.getElementById("modalCantidad").value;
    
   // Preparar datos para enviar
    let datos = new FormData();
    datos.append("id", productoActualID);
    datos.append("cantidad", cantidad);

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            try {
                var respuesta = JSON.parse(this.responseText);
                
                if (respuesta.exito) {
                    cerrarModal();
                    
                    // Actualizamos la cesta visualmente llamando a tu función
                    mostrarCesta(); 

                    // Opcional: Feedback visual
                    // alert("Producto añadido correctamente");
                } else {
                    mostrarAlertaError(respuesta.error);
                }
            } catch (e) {
                console.error("Error respuesta:", this.responseText);
            }
        }
    };

    xhttp.open("POST", "../back/anadir_carrito.php", true);
    xhttp.send(datos);
    
    cerrarModal(); // Cerramos tras confirmar
}

// Función para mostrar alertas de error personalizadas
function mostrarAlertaError(mensaje) {
    var contenedor = document.body;
    var alertaHTML = `
        <div class="alert-error" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
            <span> ${mensaje}</span>
            <button type="button" class="btn-close" onclick="this.parentElement.remove();" style="background-color: transparent; border: none; cursor: pointer; font-size: 1.5rem;">&times;</button>
        </div>
    `;
    document.body.insertAdjacentHTML('afterbegin', alertaHTML);
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        var alert = document.querySelector('.alert-error');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Función para mostrar alertas de éxito personalizadas
function mostrarAlertaExito(mensaje) {
    var alertaHTML = `
        <div class="alert-success" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
            ${mensaje}
        </div>
    `;
    document.body.insertAdjacentHTML('afterbegin', alertaHTML);
    
    // Auto-cerrar después de 3 segundos
    setTimeout(() => {
        var alert = document.querySelector('.alert-success');
        if (alert) {
            alert.remove();
        }
    }, 3000);
}
// Función para mostrar modal de confirmación Bootstrap
function mostrarModalConfirmacion(titulo, mensaje, callback) {
    var modalHTML = `
        <div class="modal fade" id="modalConfirmacion" tabindex="-1" style="display: block; background-color: rgba(0, 0, 0, 0.7);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" style="color: white; font-weight: bold;"> ${titulo}</h5>
                        <button type="button" class="btn-close" onclick="cerrarModalConfirmacion()"></button>
                    </div>
                    <div class="modal-body">
                        <p style="font-size: 1.1rem;">${mensaje}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="cerrarModalConfirmacion()">Cancelar</button>
                        <button type="button" class="btn btn-success" onclick="confirmarModalConfirmacion()">✓ Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Agregar modal al body
    var existente = document.getElementById('modalConfirmacion');
    if (existente) existente.remove();
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Guardar callback global para poder usarlo al confirmar
    window.callbackConfirmacion = callback;
}

// Función para finalizar el pedido
function finalizarPedido() {
    mostrarModalConfirmacion('¿Completar Pedido?', '¿Estás seguro de que deseas completar este pedido?', function() {
        var formData = new FormData();
        
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                try {
                    var respuesta = JSON.parse(this.responseText);
                    if (respuesta.exito) {
                        mostrarAlertaExito(respuesta.mensaje);
                        
                        // Limpiar y recargar la cesta después de 1 segundo
                        setTimeout(() => {
                            var cuerpoCesta = document.getElementById("contenedorCestaBody");
                            cuerpoCesta.innerHTML = '';
                            mostrarCesta();
                        }, 1000);
                    } else {
                        mostrarAlertaError(respuesta.error);
                    }
                } catch (e) {
                    console.error("Error JS:", e);
                    mostrarAlertaError("Error al procesar el pedido");
                }
            }
        };
        
        xhttp.open("POST", "../back/finalizar_pedido.php", true);
        xhttp.send(formData);
    });
}



// Función para cerrar modal de confirmación
function cerrarModalConfirmacion() {
    var modal = document.getElementById('modalConfirmacion');
    if (modal) modal.remove();
    window.callbackConfirmacion = null;
}

// Función para confirmar la acción en el modal
function confirmarModalConfirmacion() {
    if (window.callbackConfirmacion) {
        window.callbackConfirmacion();
    }
    cerrarModalConfirmacion();
}