
// Este script gestiona el menú de usuario en todas las páginas PHP
// Requiere un <div id="user-menu"></div> en el HTML donde quieras mostrar el menú

document.addEventListener('DOMContentLoaded', function() {
	const userMenu = document.getElementById('user-menu');
	if (!userMenu) return;

	// Consulta el estado de sesión
	fetch('/RIASEC/PHP/auth.php?action=status')
		.then(res => res.json())
		.then(data => {
			userMenu.innerHTML = '';
			if (data.logged) {
				// Usuario logueado: mostrar botón con nombre y menú
				const btn = document.createElement('button');
				btn.textContent = data.nombre;
				btn.id = 'user-btn';
				btn.type = 'button';
				btn.onclick = function() {
					menu.classList.toggle('show');
				};
				const menu = document.createElement('div');
				menu.className = 'user-dropdown';
				menu.style.display = 'none';
				menu.innerHTML = `
					<button id="logout-btn">Cerrar sesión</button><br>
					<button id="change-pass-btn">Cambiar contraseña</button>
				`;
				userMenu.appendChild(btn);
				userMenu.appendChild(menu);

				// Mostrar/ocultar menú
				btn.addEventListener('click', function() {
					menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
				});
				document.addEventListener('click', function(e) {
					if (!userMenu.contains(e.target)) menu.style.display = 'none';
				});

				// Cerrar sesión
				menu.querySelector('#logout-btn').onclick = function() {
					fetch('/RIASEC/PHP/auth.php?action=logout', { method: 'POST' })
						.then(() => window.location.reload());
				};
				// Cambiar contraseña (puedes personalizar esta acción)
				menu.querySelector('#change-pass-btn').onclick = function() {
					alert('Funcionalidad de cambio de contraseña próximamente.');
				};
			} else {
				// No logueado: mostrar botones de login y registro
				const loginBtn = document.createElement('button');
				loginBtn.textContent = 'Iniciar sesión';
				loginBtn.type = 'button';
				loginBtn.onclick = function() {
					mostrarFormulario('login');
				};
				const regBtn = document.createElement('button');
				regBtn.textContent = 'Crear usuario';
				regBtn.type = 'button';
				regBtn.onclick = function() {
					mostrarFormulario('registro');
				};
				userMenu.appendChild(loginBtn);
				userMenu.appendChild(regBtn);
			}
		});

	// Muestra el formulario de login o registro
	function mostrarFormulario(tipo) {
		userMenu.innerHTML = '';
		const form = document.createElement('form');
		form.id = 'user-form';
		if (tipo === 'login') {
			form.innerHTML = `
				<input type="text" name="nombre" placeholder="Usuario" required><br>
				<input type="password" name="contrasena" placeholder="Contraseña" required><br>
				<button type="submit">Entrar</button>
				<button type="button" id="cancelar">Cancelar</button>
			`;
		} else {
			form.innerHTML = `
				<input type="text" name="nombre" placeholder="Usuario" required><br>
				<input type="email" name="correo" placeholder="Correo" required><br>
				<input type="password" name="contrasena" placeholder="Contraseña" required><br>
				<input type="password" name="contrasena2" placeholder="Repetir contraseña" required><br>
				<button type="submit">Registrar</button>
				<button type="button" id="cancelar">Cancelar</button>
			`;
		}
		userMenu.appendChild(form);
		document.getElementById('cancelar').onclick = function() { window.location.reload(); };

		form.onsubmit = function(e) {
			e.preventDefault();
			const datos = Object.fromEntries(new FormData(form));
			if (tipo === 'registro' && datos.contrasena !== datos.contrasena2) {
				alert('Las contraseñas no coinciden');
				return;
			}
			fetch(`/RIASEC/PHP/auth.php?action=${tipo === 'login' ? 'login' : 'register'}`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(datos)
			})
			.then(res => res.json())
			.then(resp => {
				if (resp.success) {
					window.location.reload();
				} else {
					alert(resp.message || 'Error');
				}
			});
		};
	}
});
