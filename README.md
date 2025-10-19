# RIASEC — Prueba Vocacional (PHP, MySQL)

Descripción
-----------
RIASEC es una aplicación web que implementa una prueba vocacional basada en las seis dimensiones R/I/A/S/E/C. Permite a usuarios (anónimos o registrados) responder un cuestionario, almacenar resultados y consultar historiales. También incluye un panel administrativo para gestionar preguntas, opciones, carreras y usuarios.

Archivos y carpetas ocultas / sensibles
--------------------------------------
Algunos archivos y carpetas no deben compartirse públicamente porque contienen secretos o datos de configuración sensibles. En este repositorio están listados en `.gitignore` y deben tratarse como privados:

- `PHP/config.php` — Contiene las credenciales de la base de datos (host, usuario, contraseña, nombre de base de datos). Mantener fuera de repositorios públicos.
- Cualquier archivo `.env` o `config` adicional que pueda añadirse para entornos.
- Directorios de configuración del editor (`.idea/`, `.vscode/`) están listados en `.gitignore`.

Requisitos
---------
- Sistema operativo: Windows, Linux o macOS (el proyecto se ejecuta en un servidor web local como XAMPP/).
- PHP 7.4 o superior (compatible con funciones básicas de PHP y sesiones). Recomendado PHP 8.x en entornos actuales.
- Servidor web Apache (puede usarse el paquete XAMPP en Windows) o Nginx con PHP-FPM.
- MySQL o MariaDB (se usa para crear las tablas y almacenar datos). El script de esquema está en `DATABASE/Script.sql`.
- Navegador web moderno para acceder a las vistas.

Instalación
-------------------------
A continuación se describe, paso a paso y con detalle, cómo instalar y poner en funcionamiento el proyecto desde otra máquina.

1) Preparación del entorno (instalación de XAMPP en Windows)
   - Descarga de XAMPP
   - Iniciar servicios:
     1. Abre el Panel de Control de XAMPP.
     2. Inicia Apache y MySQL.
     3. Asegúrate de que no haya conflictos de puertos (por ejemplo, Skype o IIS pueden usar el puerto 80). Si hay conflicto, cambia el puerto de Apache o cierra la otra aplicación.

2) Preparar la base de datos
   - Crear la base de datos y tablas:
     1. Abre `http://localhost/phpmyadmin` en tu navegador.
     2. Crea una nueva base de datos (por ejemplo `test_vocacional`).
     3. Importa el archivo `DATABASE/Script.sql` desde la pestaña "Importar" para crear las tablas y los datos iniciales.
   - Usuarios y permisos:
     - El proyecto por defecto espera un usuario MySQL con permisos adecuados. En XAMPP, por defecto existe el usuario `root` sin contraseña. Si tu entorno exige contraseña, ajustar `PHP/config.php` en el proyecto.

3) Copiar el proyecto al directorio del servidor
   - Ubicación recomendada en Windows (XAMPP):
     1. Copia la carpeta del proyecto `RIASEC` a `C:\xampp\htdocs\`. Resultado: `C:\xampp\htdocs\RIASEC`.
   - Permisos:
     - Asegúrate de que los archivos tengan permisos de lectura por parte del servidor web.

4) Configurar las credenciales de la base de datos
   - Crea `PHP/config.php` y actualizar los valores según tu entorno:
     - `$host` — normalmente `localhost`.
     - `$user` — usuario MySQL (por defecto `root` en XAMPP).
     - `$pass` — contraseña del usuario MySQL (vacía en instalaciones XAMPP por defecto, o la que hayas definido).
     - `$db` — nombre de la base de datos creada (por ejemplo `test_vocacional`).
   - Guardar el archivo. Este archivo está en `.gitignore` para evitar subir credenciales accidentalmente.

5) Revisar el puerto y la URL base
   - En un entorno de desarrollo local con Apache en el puerto 80, la aplicación estará disponible en `http://localhost/RIASEC/`.
   - Si Apache está configurado en otro puerto (por ejemplo 8080), la URL será `http://localhost:8080/RIASEC/`.

6) Probar la aplicación
   - Abrir el navegador y navegar a `http://localhost/RIASEC/`.
   - El primer usuario se vuelve Administrador, para que pueda añadir pregutas y las carreras antes que nada.
   - La página principal (`index.php`) muestra el test. Si falta contenido o aparece un error de conexión, revisar `PHP/config.php` y los logs de Apache/MySQL.

7) Ajustes opcionales (seguridad básica y configuración)
   - Cambiar la contraseña del usuario MySQL por defecto y actualizar `PHP/config.php`.
   - Asegurar que el directorio `PHP` y los archivos de configuración no sean accesibles desde la web si expones el servidor públicamente.
   - Habilitar HTTPS si el servidor va a estar accesible desde Internet.

8) Restauración y mantenimiento
   - Para restaurar la base de datos, usa la opción "Importar" en phpMyAdmin con el archivo `DATABASE/Script.sql`.
   - Haz copias de seguridad periódicas de la base de datos y de la carpeta del proyecto.

Cómo ejecutar desde otra PC (resumen de pasos rápidos)
   - Instalar XAMPP (o equivalente) y arrancar Apache y MySQL.
   - Copiar la carpeta `RIASEC` a `htdocs`.
   - Importar `DATABASE/Script.sql` en MySQL (phpMyAdmin).
   - Crear `PHP/config.php` con las credenciales correctas.
   - Abrir `http://<ip_o_hostname>/RIASEC/` y configurar su prueba RIASEC

Cómo está organizado el proyecto
------------------------------
Estructura principal (ruta relativa a la raíz `RIASEC/`):

- `index.php` — Vista principal del test; orquesta la carga de preguntas y opciones.
- `DATABASE/Script.sql` — Script SQL que crea tablas y datos iniciales.
- `JAVASCRIPT/Recursos.js` — Código JavaScript para la UI (modo oscuro, modal de usuario, validaciones, lógica cliente).
- `PHP/` — Lógica del servidor y utilidades PHP:
  - `config.php` — Configuración de la base de datos (privado).
  - `crud.php` — Funciones reutilizables de acceso a datos (select, insert, update, delete).
  - `auth.php` — Punto de entrada para autenticación (login, registro, logout, status) que responde JSON.
  - `Funciones/` — Funciones específicas usadas por las vistas (PreguntasF, OpcionesF, CarrerasF, ResultadosF, UsuariosF, EstadisticasF, indexF).
- `STYLE/` — Hojas de estilo CSS usadas por las vistas.
- `VIEWS/` — Vistas organizadas por rol:
  - `ADMIN/` — Páginas para administración (gestión de usuarios, preguntas, opciones, carreras y estadísticas).
  - `USER/` — Páginas para usuarios (resultados, historial).

Cómo funciona (flujo general)
----------------------------
1. Usuario abre `index.php` y navega por las preguntas.
2. Las preguntas y opciones se obtienen en el servidor mediante las funciones en `PHP/Funciones/` y `PHP/crud.php`.
3. El usuario puede responder y avanzar; las respuestas se almacenan en la sesión y/o se persisten en la base de datos al finalizar, usando las funciones en `ResultadosF.php` y en `crud.php`.
4. Si el usuario está registrado y autenticado (gestión vía `auth.php` y `Recursos.js`), los resultados se asocian a su cuenta y quedan guardados como historial.
5. El área administrativa (`VIEWS/ADMIN/*`) utiliza las funciones en `PHP/Funciones/*` para listar, crear, editar y eliminar preguntas, opciones, carreras y usuarios.

Explicación de funciones clave
-----------------------------
Las funciones que siguen están implementadas en `PHP/Funciones/*.php` y usan `PHP/crud.php` para acceso a datos. Abajo se explica su propósito y comportamiento general.

Funciones de Carreras (archivos relevantes: `PHP/Funciones/CarrerasF.php`, tablas: `carreras`)
- Propósito: gestionar las carreras y su relación con los resultados. Cada carrera guarda porcentajes de afinidad para las dimensiones R/I/A/S/E/C.
- Operaciones típicas:
  - listarCarreras() — Devuelve el listado de carreras con sus porcentajes.
  - crearCarrera($nombre, $porcentajes) — Inserta una nueva carrera con valores de porcentaje para cada dimensión.
  - actualizarCarrera($id, $datos) — Actualiza el registro de una carrera existente.
  - eliminarCarrera($id) — Elimina la carrera por su identificador.
- Uso: en la vista `VIEWS/ADMIN/Carreras.php` se proporciona un formulario para crear/editar y una tabla para listar/Eliminar.

Funciones de Registro / Autenticación (archivos relevantes: `PHP/auth.php`, `PHP/crud.php`)
- Propósito: permitir que un usuario se registre, inicie sesión y cierre sesión. También ofrece un endpoint `status` para que la UI consulte si hay un usuario autenticado.
- Operaciones típicas:
  - login() — Valida credenciales, crea variables de sesión y devuelve JSON con estado y nombre.
  - register() — Valida datos, crea un usuario nuevo en la tabla `usuarios` (guardando la contraseña hasheada) y opcionalmente inicia sesión.
  - logout() — Cierra la sesión actual.
  - status() — Devuelve si el usuario está logueado y su información mínima (por ejemplo: nombre, id, rol).
- Uso: `JAVASCRIPT/Recursos.js` interactúa con `PHP/auth.php` vía fetch/AJAX para mostrar el menú de usuario, iniciar sesión desde un modal y registrar nuevos usuarios.

Explicación de Resultados (archivos relevantes: `PHP/Funciones/ResultadosF.php`, tabla: `resultados`)
- Propósito: calcular y almacenar los puntajes por dimensión (R, I, A, S, E, C) a partir de las respuestas del usuario y vincular el resultado a un usuario si está autenticado.
- Flujo general:
  1. Recibir respuestas (mapa pregunta->valor) y sumar puntajes por categoría basándose en la categoría de cada pregunta.
  2. Calcular porcentajes o normalizar según la escala definida (por ejemplo, convertir puntajes totales en porcentajes de afinidad por dimensión).
  3. Guardar un registro en la tabla `resultados` con fecha y puntajes/porcentajes por dimensión.
  4. Si existe un usuario autenticado, asociar el resultado a su `id_usuario`.
  5. Renderizar en `VIEWS/USER/Resultados.php` las barras, carrusel y detalles de afinidad, además del historial si aplica.

  Recomendación de carreras
  -------------------------
  La aplicación incluye mecanismos para relacionar los puntajes del test RIASEC con las carreras disponibles en la base de datos. El proceso general de recomendación de carreras es el siguiente:

  - Calcular puntajes por dimensión (R, I, A, S, E, C) a partir de las respuestas del usuario.
  - Normalizar o convertir esos puntajes a porcentajes de afinidad por dimensión.
  - Para cada carrera almacenada en la tabla `carreras` se guardan porcentajes de afinidad objetivo (por ejemplo: porcentaje_R, porcentaje_I, ...).
  - Se compara el perfil del usuario (los porcentajes calculados) con el perfil de cada carrera. Una forma común de hacerlo es calcular la distancia (por ejemplo, distancia Euclidiana o suma de diferencias absolutas) entre los vectores de porcentajes del usuario y de la carrera.
  - Ordenar las carreras por similitud (las de menor distancia o mayor coincidencia aparecen primero) y mostrar las principales recomendaciones al usuario.

  En el código actual, la lógica de cálculo y comparación está centralizada en `PHP/Funciones/ResultadosF.php` y en las funciones de `PHP/Funciones/CarrerasF.php` que recuperan los perfiles de carrera. El método exacto de comparación puede ajustarse en el archivo de funciones si se desea usar otro criterio (p. ej. ponderar más ciertas dimensiones, usar correlación en lugar de distancia, etc.).

  Administrador y configuración inicial
  -----------------------------------
  El proyecto está diseñado para ser configurado por un administrador. En la instalación inicial, el primer usuario que se registre en la aplicación debe actuar como administrador y realizar las siguientes tareas desde el panel de administración (`VIEWS/ADMIN/`):

  - Añadir las preguntas del test (a través de la gestión de `Preguntas`) si aún no están todas cargadas.
  - Configurar las opciones de respuesta (valores y descripciones) si es necesario.
  - Crear y definir las carreras que usarán para las recomendaciones, especificando los porcentajes objetivo por dimensión R/I/A/S/E/C.

  Nota: en muchos despliegues se importa primero `DATABASE/Script.sql` con datos iniciales; si el script no contiene preguntas o carreras, el primer registro de usuario deberá encargarse de crearlas mediante la interfaz administrativa. El primer usuario registrado puede considerarse el administrador por defecto y debe completar la configuración inicial antes de que otros usuarios realicen pruebas y reciban recomendaciones.

Dónde están los diagramas UML
----------------------------
Los diagramas UML se encuentran en la carpeta `UML/` dentro del proyecto. En esta carpeta hay archivos que documentan:
- Diagramas de clases (modelo de datos y relaciones entre `Usuario`, `Rol`, `Pregunta`, `Opcion`, `Resultado`, `Carrera`, `Crud`).
- Máquinas de estado (flujo de la sesión del test RIASEC).
- Diagramas de arquitectura y casos de uso.

Notas finales
-------------
- Este README describe la estructura, el flujo y los pasos de instalación para poner en marcha el proyecto en otra máquina. No contiene información personal ni recomendaciones fuera de lo técnico.
- Si necesitas un archivo `README` con más información técnica (por ejemplo: lista de endpoints de `auth.php` con ejemplos JSON, o una guía de contribución con convenciones de código), puedo generarlo en un segundo paso.

---

