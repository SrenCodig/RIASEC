// Modes.js - Script para alternar modo claro/oscuro con animación de icono
// Menú de usuario con ventana emergente y opciones avanzadas
// Comentarios añadidos para explicar cada bloque y variables clave.

// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // --------------------------------------------------
    // Bloque: manejo de modo claro/oscuro (dark mode)
    // --------------------------------------------------
    const darkModeSwitch = document.getElementById('darkModeSwitch');
    if (darkModeSwitch) {
        // Leer preferencia desde localStorage y aplicar clase 'active' al body
        // Nota: en CSS se usa body.active para estilos del modo claro/oscuro
        if (localStorage.getItem('modo') === 'claro') {
            document.body.classList.add('active');
        } else {
            document.body.classList.remove('active');
        }
        // Al hacer clic en el switch, alternar la clase y guardar la preferencia
        darkModeSwitch.addEventListener('click', function() {
            document.body.classList.toggle('active');
            if (document.body.classList.contains('active')) {
                localStorage.setItem('modo', 'claro');
            } else {
                localStorage.setItem('modo', 'oscuro');
            }
        });
    }

    // --------------------------------------------------
    // Bloque: UI de usuario / modal de sesión (login/register)
    // --------------------------------------------------
    // Ubicación en el DOM donde se inyecta el botón/menú de usuario
    const userMenu = document.getElementById('user-menu');
    if (!userMenu) return; // Si no existe el contenedor, salir silenciosamente

    // Crear botón dinámicamente para mostrar nombre/estado del usuario
    const userBtn = document.createElement('button');
    userBtn.id = 'user-btn';
    userBtn.className = 'user-btn';
    // Texto por defecto (se actualiza tras consultar /auth.php?action=status)
    userBtn.textContent = 'Usuario';
    userMenu.appendChild(userBtn);

    // Forzar repaint del botón cuando cambian estilos para evitar glitches visuales
    function actualizarColorUserBtn() {
        userBtn.style.display = 'none';
        void userBtn.offsetWidth; // lectura forzada que provoca reflow
        userBtn.style.display = '';
    }

    // Consulta al backend para conocer si hay sesión activa y obtener el nombre
    function actualizarUserBtn() {
        fetch('/RIASEC/PHP/auth.php?action=status')
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json().catch(() => ({ logged: false }));
            })
            .then(data => {
                if (data && data.logged && typeof data.nombre === 'string' && data.nombre.trim() !== '') {
                    userBtn.textContent = data.nombre;
                } else {
                    userBtn.textContent = 'Usuario';
                }
                actualizarColorUserBtn();
            })
            .catch(err => {
                // Loguear en consola para debugging y mantener UI funcional
                console.warn('No se pudo obtener estado de sesión:', err);
                userBtn.textContent = 'Usuario';
                actualizarColorUserBtn();
            });
    }
    // Ejecutar al cargar la página
    actualizarUserBtn();

    // Re-evaluar el botón tras cambiar el modo (para que el color se actualice)
    document.getElementById('darkModeSwitch')?.addEventListener('click', function() {
        setTimeout(actualizarUserBtn, 100);
    });

    // Crear modal que mostrará el formulario o acciones del usuario
    const modal = document.createElement('div');
    modal.className = 'user-modal';
    modal.style.display = 'none';
    modal.innerHTML = `
        <div class="user-modal-content">
            <span class="close-modal">&times;</span>
            <div id="user-modal-body"></div>
        </div>
    `;
    document.body.appendChild(modal);

    // Mostrar modal al hacer clic sobre el botón de usuario
    userBtn.onclick = function() {
        cargarEstadoUsuario();
        modal.style.display = 'block';
    };

    // Cerrar modal
    modal.querySelector('.close-modal').onclick = function() {
        modal.style.display = 'none';
    };
    window.onclick = function(event) {
        if (event.target === modal) modal.style.display = 'none';
    };

    // Cargar y renderizar el contenido del modal según el estado de autenticación
    function cargarEstadoUsuario() {
        fetch('/RIASEC/PHP/auth.php?action=status')
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json().catch(() => ({ logged: false }));
            })
            .then(data => {
                const body = modal.querySelector('#user-modal-body');
                body.innerHTML = '';
                if (data && data.logged) {
                    // Usuario autenticado: mostrar nombre y acciones (cambiar pass, eliminar, logout)
                    let nombreUsuario = (typeof data.nombre === 'string' && data.nombre.trim() !== '') ? data.nombre : 'Usuario no identificado';
                    body.innerHTML = `
                        <div class="user-info">
                            <span class="user-name">${nombreUsuario}</span>
                        </div>
                        <button class="user-action" id="change-pass-btn">Cambiar contraseña</button>
                        <button class="user-action" id="delete-account-btn">Eliminar cuenta</button>
                        <button class="user-action" id="logout-btn">Cerrar sesión</button>
                    `;
                    // Si no tenemos nombre fiable, ocultar acciones sensibles
                    if (nombreUsuario === 'Usuario no identificado') {
                        body.querySelector('#change-pass-btn').style.display = 'none';
                        body.querySelector('#delete-account-btn').style.display = 'none';
                    }
                    // Logout: POST a auth.php?action=logout y recargar página
                    body.querySelector('#logout-btn').onclick = function() {
                        fetch('/RIASEC/PHP/auth.php?action=logout', { method: 'POST' })
                            .then(() => window.location.reload());
                    };
                    // Mostrar formulario para cambiar contraseña
                    body.querySelector('#change-pass-btn').onclick = function() {
                        mostrarFormulario('cambiar');
                    };
                    // Eliminar cuenta con confirmación y llamada al endpoint
                    body.querySelector('#delete-account-btn').onclick = function() {
                        if (confirm('¿Seguro que deseas eliminar tu cuenta? Esta acción no se puede deshacer.')) {
                            fetch('/RIASEC/PHP/auth.php?action=delete_account', { method: 'POST' })
                                .then(res => res.json())
                                .then(resp => {
                                    if (resp.success) {
                                        alert('Cuenta eliminada correctamente');
                                        window.location.reload();
                                    } else {
                                        alert(resp.error || resp.message || 'Error al eliminar la cuenta');
                                    }
                                });
                        }
                    };
                } else {
                    // Usuario no autenticado: mostrar botones de login y registro
                    body.innerHTML = `
                        <button class="user-action" id="login-btn">Iniciar sesión</button>
                        <button class="user-action" id="register-btn">Crear usuario</button>
                    `;
                    body.querySelector('#login-btn').onclick = function() {
                        mostrarFormulario('login');
                    };
                    body.querySelector('#register-btn').onclick = function() {
                        mostrarFormulario('registro');
                    };
                }
            })
            .catch(err => {
                console.warn('Error al cargar estado de usuario:', err);
                const body = modal.querySelector('#user-modal-body');
                body.innerHTML = '';
                // Fallback: mostrar botones de login/registro para no bloquear la UI
                body.innerHTML = `
                    <button class="user-action" id="login-btn">Iniciar sesión</button>
                    <button class="user-action" id="register-btn">Crear usuario</button>
                `;
                body.querySelector('#login-btn').onclick = function() { mostrarFormulario('login'); };
                body.querySelector('#register-btn').onclick = function() { mostrarFormulario('registro'); };
            });
    }

    // Renderiza formularios dinámicos para login/registro/cambio de contraseña
    function mostrarFormulario(tipo) {
        const body = modal.querySelector('#user-modal-body');
        if (tipo === 'login') {
            // Formulario de login: nombre + contraseña
            body.innerHTML = `
                <form id="user-form">
                    <input type="text" name="nombre" placeholder="Usuario" required><br>
                    <input type="password" name="contrasena" placeholder="Contraseña" required><br>
                    <button type="submit">Entrar</button>
                    <button type="button" id="cancelar">Cancelar</button>
                </form>
            `;
        } else if (tipo === 'registro') {
            // Registro: nombre, correo y doble contraseña
            body.innerHTML = `
                <form id="user-form">
                    <input type="text" name="nombre" placeholder="Usuario" required><br>
                    <input type="email" name="correo" placeholder="Correo" required><br>
                    <input type="password" name="contrasena" placeholder="Contraseña" required><br>
                    <input type="password" name="contrasena2" placeholder="Repetir contraseña" required><br>
                    <button type="submit">Registrar</button>
                    <button type="button" id="cancelar">Cancelar</button>
                </form>
            `;
        } else if (tipo === 'cambiar') {
            // Formulario para cambiar contraseña (solicita actual y nueva)
            body.innerHTML = `
                <form id="user-form">
                    <input type="password" name="actual" placeholder="Contraseña actual" required><br>
                    <input type="password" name="nueva" placeholder="Nueva contraseña" required><br>
                    <input type="password" name="nueva2" placeholder="Repetir nueva contraseña" required><br>
                    <button type="submit">Cambiar</button>
                    <button type="button" id="cancelar">Cancelar</button>
                </form>
            `;
        }
        // Cancelar vuelve a mostrar el estado de usuario (refrescar contenido del modal)
        document.getElementById('cancelar').onclick = function() { cargarEstadoUsuario(); };

        // Manejar envío del formulario según el tipo
        const form = document.getElementById('user-form');
        form.onsubmit = function(e) {
            e.preventDefault();
            const datos = Object.fromEntries(new FormData(form));
            // Validaciones front: contraseñas coincidentes
            if (tipo === 'registro' && datos.contrasena !== datos.contrasena2) {
                alert('Las contraseñas no coinciden');
                return;
            }
            if (tipo === 'cambiar' && datos.nueva !== datos.nueva2) {
                alert('Las contraseñas nuevas no coinciden');
                return;
            }
            let url = '';
            let payload = {};
            if (tipo === 'login') {
                // Login: enviar nombre y contraseña al endpoint
                url = '/RIASEC/PHP/auth.php?action=login';
                payload = datos;
            } else if (tipo === 'registro') {
                // Para el primer registro podría querer asignar rol administrador
                // Se consulta status para detectar si no hay usuarios y otorgar rol 1
                fetch('/RIASEC/PHP/auth.php?action=status')
                    .then(res => res.json())
                    .then(data => {
                        let id_rol = 2; // rol por defecto = Usuario
                        // Nota: el backend actual no devuelve la lista de usuarios en status,
                        // este bloque intenta detectar primer usuario pero depende de la respuesta
                        if (data && Array.isArray(data.usuarios) && data.usuarios.length === 0) {
                            id_rol = 1; // si no hay usuarios, asignar Administrador
                        }
                        url = '/RIASEC/PHP/auth.php?action=register';
                        payload = { ...datos, id_rol };
                        // Enviar petición de registro
                        fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        })
                        .then(res => res.json())
                        .then(resp => {
                            if (resp.success) {
                                window.location.reload();
                            } else {
                                alert(resp.message || 'Error');
                            }
                        });
                    });
                return; // ya se hace la petición dentro del then
            } else if (tipo === 'cambiar') {
                // Cambio de contraseña: enviar nueva contraseña al endpoint específico
                if (!datos.nueva) {
                    alert('Debes ingresar la nueva contraseña');
                    return;
                }
                url = '/RIASEC/PHP/auth.php?action=change_password';
                payload = { nueva_contrasena: datos.nueva };
            }
            // Enviar petición (para login y change_password)
            if (tipo !== 'registro') {
                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(resp => {
                    if (resp.success) {
                        window.location.reload();
                    } else {
                        alert(resp.message || 'Error');
                    }
                });
            }
        };
    }

    // --------------------------------------------------
    // Bloque: carrusel (historial de pruebas)
    // --------------------------------------------------
    // Seleccionar elementos del DOM que forman el carrusel
    const carrusel = document.querySelector('.carrusel');
    const items = document.querySelectorAll('.carrusel-item');
    const prevButton = document.querySelector('.carrusel-prev');
    const nextButton = document.querySelector('.carrusel-next');
    let currentIndex = 0; // índice del item actualmente visible

    // Función que activa/desactiva la clase 'active' en cada item según currentIndex
    function updateCarrusel() {
        items.forEach((item, index) => {
            if (index === currentIndex) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }

    // Hooks de navegación: prev/next con wrap-around
    if (prevButton && nextButton && items.length) {
        prevButton.addEventListener('click', () => {
            currentIndex = (currentIndex === 0) ? items.length - 1 : currentIndex - 1;
            updateCarrusel();
        });
        nextButton.addEventListener('click', () => {
            currentIndex = (currentIndex === items.length - 1) ? 0 : currentIndex + 1;
            updateCarrusel();
        });
        // Inicializar estado del carrusel
        updateCarrusel();
    }

    // Asegurar que el botón "Finalizar" envíe el formulario aunque aparezca deshabilitado
    // (caso en que la página calcula $todasRespondidas desde la sesión y aún no contiene
    //  la respuesta seleccionada en el DOM). Si no hay opción marcada, mostrar alerta.
    (function() {
        const finalizarBtn = document.querySelector('form button[name="finalizar"]');
        if (!finalizarBtn) return;
        finalizarBtn.addEventListener('click', function(e) {
            const form = finalizarBtn.closest('form');
            if (!form) return;
            // Si el botón está deshabilitado, intentar enviar si hay una opción marcada
            if (finalizarBtn.disabled) {
                const checked = form.querySelector('input[type="radio"]:checked');
                if (checked) {
                    // Forzar submit programático (no usamos preventDefault)
                    // Hacemos submit directamente para incluir el valor seleccionado en la petición
                    form.submit();
                } else {
                    // Prevenir envío y avisar
                    e.preventDefault();
                    alert('Debes seleccionar una opción antes de finalizar.');
                }
            }
            // Si no está deshabilitado, dejamos que el formulario se envíe normalmente
        });
    })();
});

// --------------------------------------------------
// Bloque: validación de formularios de carreras (movido desde Carreras.php)
// Se ejecuta después de DOMContentLoaded y reutiliza los mismos IDs/elementos
// que la vista proporciona. No modifica nada en la vista, sólo añade
// listeners si los formularios existen.
(function() {
    function validarPorcentajes(letras, formId, errorId) {
        let valido = true;
        let error = '';
        let total = 0;
        letras.forEach(function(letra) {
            let inputId = 'porcentaje_' + letra + (formId === 'form-carrera-edit' ? '_edit' : '');
            let input = document.getElementById(inputId);
            if (!input) return;
            let val = parseInt(input.value, 10);
            if (isNaN(val) || val < 0 || val > 100) {
                valido = false;
                error = 'Todos los porcentajes deben ser números entre 0 y 100.';
            }
            total += isNaN(val) ? 0 : val;
        });
        if (valido && total > 100 * letras.length) {
            valido = false;
            error = 'La suma total de porcentajes no debe exceder ' + (100 * letras.length) + '.';
        }
        const errEl = document.getElementById(errorId);
        if (errEl) {
            errEl.style.display = valido ? 'none' : 'block';
            errEl.textContent = error;
        }
        return valido;
    }

    // Registrar listeners si existen los formularios
    document.addEventListener('DOMContentLoaded', function() {
        const letras = ['R','I','A','S','E','C'];
        const formAdd = document.getElementById('form-carrera');
        if (formAdd) {
            formAdd.addEventListener('submit', function(e) {
                if (!validarPorcentajes(letras, 'form-carrera', 'error-carrera')) e.preventDefault();
            });
        }
        const formEdit = document.getElementById('form-carrera-edit');
        if (formEdit) {
            formEdit.addEventListener('submit', function(e) {
                if (!validarPorcentajes(letras, 'form-carrera-edit', 'error-carrera-edit')) e.preventDefault();
            });
        }
    });
})();
