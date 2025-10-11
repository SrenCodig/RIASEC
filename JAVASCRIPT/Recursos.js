// Modes.js - Script para alternar modo claro/oscuro con animación de icono
// Menú de usuario con ventana emergente y opciones avanzadas

// --- MODOS ---
document.addEventListener('DOMContentLoaded', function() {
    const darkModeSwitch = document.getElementById('darkModeSwitch');
    if (darkModeSwitch) {
        if (localStorage.getItem('modo') === 'claro') {
            document.body.classList.add('active');
        } else {
            document.body.classList.remove('active');
        }
        darkModeSwitch.addEventListener('click', function() {
            document.body.classList.toggle('active');
            if (document.body.classList.contains('active')) {
                localStorage.setItem('modo', 'claro');
            } else {
                localStorage.setItem('modo', 'oscuro');
            }
        });
    }

    // --- LOGIN ---
    const userMenu = document.getElementById('user-menu');
    if (!userMenu) return;
    const userBtn = document.createElement('button');
    userBtn.id = 'user-btn';
    userBtn.className = 'user-btn';
    // Por defecto muestra 'Usuario', luego se actualiza si está logueado
    userBtn.textContent = 'Usuario';
    userMenu.appendChild(userBtn);
    // Actualizar el color del botón de usuario al cambiar modo o nombre
    function actualizarColorUserBtn() {
        // Forzar repaint del botón para que el CSS se aplique correctamente
        userBtn.style.display = 'none';
        void userBtn.offsetWidth;
        userBtn.style.display = '';
    }
    // Consultar estado y actualizar el texto del botón
    function actualizarUserBtn() {
        fetch('/RIASEC/PHP/auth.php?action=status')
            .then(res => res.json())
            .then(data => {
                if (data.logged && typeof data.nombre === 'string' && data.nombre.trim() !== '') {
                    userBtn.textContent = data.nombre;
                } else {
                    userBtn.textContent = 'Usuario';
                }
                actualizarColorUserBtn();
            });
    }
    actualizarUserBtn();
    // Actualizar color y nombre al cambiar modo
    document.getElementById('darkModeSwitch')?.addEventListener('click', function() {
        setTimeout(actualizarUserBtn, 100);
    });
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
    userBtn.onclick = function() {
        cargarEstadoUsuario();
        modal.style.display = 'block';
    };
    modal.querySelector('.close-modal').onclick = function() {
        modal.style.display = 'none';
    };
    window.onclick = function(event) {
        if (event.target === modal) modal.style.display = 'none';
    };
    function cargarEstadoUsuario() {
        fetch('/RIASEC/PHP/auth.php?action=status')
            .then(res => res.json())
            .then(data => {
                const body = modal.querySelector('#user-modal-body');
                body.innerHTML = '';
                if (data.logged) {
                    let nombreUsuario = (typeof data.nombre === 'string' && data.nombre.trim() !== '') ? data.nombre : 'Usuario no identificado';
                    body.innerHTML = `
                        <div class="user-info">
                            <span class="user-name">${nombreUsuario}</span>
                        </div>
                        <button class="user-action" id="change-pass-btn">Cambiar contraseña</button>
                        <button class="user-action" id="delete-account-btn">Eliminar cuenta</button>
                        <button class="user-action" id="logout-btn">Cerrar sesión</button>
                    `;
                    if (nombreUsuario === 'Usuario no identificado') {
                        // Si el nombre está mal, solo permitir cerrar sesión
                        body.querySelector('#change-pass-btn').style.display = 'none';
                        body.querySelector('#delete-account-btn').style.display = 'none';
                    }
                    body.querySelector('#logout-btn').onclick = function() {
                        fetch('/RIASEC/PHP/auth.php?action=logout', { method: 'POST' })
                            .then(() => window.location.reload());
                    };
                    body.querySelector('#change-pass-btn').onclick = function() {
                        mostrarFormulario('cambiar');
                    };
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
            });
    }
    function mostrarFormulario(tipo) {
        const body = modal.querySelector('#user-modal-body');
        if (tipo === 'login') {
            body.innerHTML = `
                <form id="user-form">
                    <input type="text" name="nombre" placeholder="Usuario" required><br>
                    <input type="password" name="contrasena" placeholder="Contraseña" required><br>
                    <button type="submit">Entrar</button>
                    <button type="button" id="cancelar">Cancelar</button>
                </form>
            `;
        } else if (tipo === 'registro') {
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
        document.getElementById('cancelar').onclick = function() { cargarEstadoUsuario(); };
        const form = document.getElementById('user-form');
        form.onsubmit = function(e) {
            e.preventDefault();
            const datos = Object.fromEntries(new FormData(form));
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
                url = '/RIASEC/PHP/auth.php?action=login';
                payload = datos;
            } else if (tipo === 'registro') {
                // --- Lógica para primer usuario admin ---
                fetch('/RIASEC/PHP/auth.php?action=status')
                    .then(res => res.json())
                    .then(data => {
                        let id_rol = 2;
                        if (data && Array.isArray(data.usuarios) && data.usuarios.length === 0) {
                            id_rol = 1;
                        }
                        url = '/RIASEC/PHP/auth.php?action=register';
                        payload = { ...datos, id_rol };
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
                return;
            } else if (tipo === 'cambiar') {
                if (!datos.nueva) {
                    alert('Debes ingresar la nueva contraseña');
                    return;
                }
                url = '/RIASEC/PHP/auth.php?action=change_password';
                payload = { nueva_contrasena: datos.nueva };
            }
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
    // --- Carrusel para historial de pruebas ---
    const carrusel = document.querySelector('.carrusel');
    const items = document.querySelectorAll('.carrusel-item');
    const prevButton = document.querySelector('.carrusel-prev');
    const nextButton = document.querySelector('.carrusel-next');
    let currentIndex = 0;

    function updateCarrusel() {
        items.forEach((item, index) => {
            if (index === currentIndex) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }

    if (prevButton && nextButton && items.length) {
        prevButton.addEventListener('click', () => {
            currentIndex = (currentIndex === 0) ? items.length - 1 : currentIndex - 1;
            updateCarrusel();
        });
        nextButton.addEventListener('click', () => {
            currentIndex = (currentIndex === items.length - 1) ? 0 : currentIndex + 1;
            updateCarrusel();
        });
        updateCarrusel();
    }
});
