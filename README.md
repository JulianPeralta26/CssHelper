# 🎨 CSS Helper

> Suite interactiva de herramientas CSS para desarrolladores frontend — diseña, ajusta y copia código CSS en segundos.

---

## 📋 Tabla de Contenidos

- [Descripción](#-descripción)
- [Características](#-características)
- [Novedades Recientes](#-novedades-recientes)
- [Stack Tecnológico](#-stack-tecnológico)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Base de Datos](#-base-de-datos)
- [Instalación y Configuración](#-instalación-y-configuración)
- [Uso](#-uso)
- [Contribuciones](#-contribuciones)
- [Licencia](#-licencia)

---

## 📖 Descripción

**CSS Helper** es una suite web interactiva pensada para acelerar el flujo de trabajo de cualquier desarrollador frontend. Ofrece herramientas visuales en tiempo real, paletas de colores curadas y un catálogo de snippets CSS listos para copiar e implementar al instante — construido con JavaScript puro y PHP, sin frameworks pesados ni dependencias externas de bundlers.

---

## ✨ Características

### 🛠️ Herramientas Interactivas
| Herramienta | Descripción |
|---|---|
| 🎨 **Selector de color** | Genera código HEX, RGB y HSL con vista previa en vivo (ratón, táctil y teclado). Incluye paleta de **colores favoritos**. |
| 📦 **Modelo de caja** | Arrastra los bordes interactivos para ajustar `margin`, `padding` y `border` con valores numéricos visibles. |
| 🧩 **Flexbox y Grid** | Construye layouts Flexbox o CSS Grid en tiempo real con controles visuales intuitivos. |
| 🔆 **Contraste y accesibilidad** | Calcula el ratio de contraste WCAG 2.1 entre texto y fondo (cumplimiento AA / AAA). |
| 🔤 **Fuentes de Google** | Catálogo interactivo de 10 fuentes seleccionables mediante chips con previsualización triple (normal, negrita, cursiva), texto de prueba editable en vivo, opción de **marcar fuentes favoritas** y generación instantánea de códigos `<link>`, `@import` y regla CSS. |
| ✨ **Efectos visuales** | Cuatro efectos listos para producción (neón glow, vidrio, degradado, elevación). |
| 🪟 **Glassmorphism** | Ajusta blur, opacidad, borde y sombra con previsualizaciones sobre diferentes fondos. |

### 🔐 Sistema de Autenticación Integrado
- Desplegable discreto de inicio de sesión en la esquina superior derecha del navbar.
- Usuario por defecto: **`admin`** | Contraseña: **`123`**.
- Gestión de sesiones PHP con contraseñas encriptadas mediante `password_hash` (`bcrypt`).

### ⭐ Favoritos por Usuario
- **Fuentes Favoritas:** Guarda tus fuentes preferidas con un clic en la estrella (★) y fíltralas con el botón de favoritas.
- **Colores Favoritos:** Guarda cualquier color creado desde el selector y reutilízalo o elimínalo de tu paleta personal en cualquier momento.
- Si no has iniciado sesión al hacer clic en un favorito, el sistema te abre automáticamente el desplegable de login.
- Al iniciar sesión, tus favoritos se cargan en tiempo real sin recargar la página.

### 🎨 Paletas de Colores Curadas
- Paletas Catppuccin, Dracula y escalas de grises (neutra, Slate y Stone).
- Copia de formatos **HEX**, **RGB** o **HSL** con un clic.
- Exportación en variables CSS, Tailwind, JSON o SCSS.

### 📝 Snippets CSS
- Biblioteca organizada por categorías con buscador instantáneo, vista previa y botón de copiado.

### 🌙 Modo Oscuro / Claro
- Detección automática de la preferencia del sistema y guardado de elección en `localStorage`.

---

## 🆕 Novedades Recientes

1. **Catálogo de 10 Google Fonts con Chips Dinámicos:** Selector visual mediante chips interactivos (`Fira Code`, `Inter`, `Lato`, `Merriweather`, `Montserrat`, `Open Sans`, `Oswald`, `Playfair Display`, `Poppins` y `Roboto`) sin necesidad de búsquedas manuales; al pulsar cualquier chip se cargan en tiempo real los códigos y previsualizaciones consumiendo `backend/get_font.php`.
2. **Auto-Provisionamiento Zero-Config (`db.php`):** Eliminado el archivo manual `schema.sql`; toda la estructura de la base de datos `css_helper`, sus tablas (`users`, `fonts`, `favorite_fonts`, `favorite_colors`) y los datos iniciales (usuario `admin` y catálogo de fuentes) se configuran automáticamente al iniciar.
3. **Compatibilidad Flexible de Conexión:** Soporte para variables de entorno de puerto (`CSSHELPER_DB_PORT`) y socket UNIX (`CSSHELPER_DB_SOCKET`), asegurando funcionamiento inmediato tanto en XAMPP estándar como en entornos avanzados o contenedores.
4. **Resiliencia Dual (MySQL + Fallback Offline):** Si MySQL está inactivo o apagado, la aplicación conmuta automáticamente a sus respaldos locales (`fonts_data.php` y `favorites_storage.json`), garantizando disponibilidad continua.
5. **Autenticación Integrada y Favoritos:** Desplegable de inicio de sesión en el navbar con contraseñas encriptadas (`bcrypt`) y guardado personal de fuentes y colores.

---

## 🧰 Stack Tecnológico

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-Custom-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-Compatible-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)

- **Backend:** PHP 8+ con PDO y manejo nativo de sesiones.
- **Frontend:** HTML5 semántico, CSS3 modular (con variables y temas) y JavaScript ES6+ Vanilla.
- **Base de datos:** MySQL / MariaDB (opcional, con respaldo automático offline).
- **Cero dependencias:** No requiere Node.js, npm, bundlers ni bibliotecas externas.

---

## 📁 Estructura del Proyecto

```
CssHelper/
├── index.php                 # Portada principal (Hero y accesos rápidos)
├── herramientas.php          # Suite completa de herramientas CSS interactivas
├── paletas.php               # Catálogo de paletas y colores
├── snipets.php               # Biblioteca de snippets reutilizables
├── README.md                 # Documentación del proyecto
│
├── backend/
│   ├── auth.php              # API: login, logout y verificación de sesión
│   ├── db.php                # Conexión PDO y auto-sincronización de base de datos
│   ├── favorites.php         # API: gestión de fuentes y colores favoritos
│   ├── favorites_storage.json# Almacenamiento JSON de favoritos (respaldo offline)
│   ├── fonts_data.php        # Catálogo predeterminado de fuentes (respaldo)
│   ├── get_all_fonts.php     # API: listar nombres de fuentes disponibles
│   └── get_font.php          # API: consultar detalles y códigos de una fuente
│
├── includes/
│   ├── header.php            # Cabecera HTML común y carga de estilos/scripts base
│   ├── navbar.php            # Barra de navegación con botón de tema y login dropdown
│   ├── footer.php            # Pie de página y contenedor de notificaciones (toast)
│   └── palettes_data.php     # Datos estructurados de paletas de colores
│
├── js/
│   ├── auth.js               # Lógica del desplegable y autenticación de usuario
│   ├── favorites.js          # Control de fuentes y colores favoritos (cliente)
│   ├── fonts.js              # Lógica de previsualización y chips de Google Fonts
│   ├── color-picker.js       # Selector de color 2D/1D y conversiones
│   ├── box-model.js          # Simulador interactivo de Box Model
│   ├── flexbox.js            # Constructor visual de Flexbox y Grid
│   ├── contrast.js           # Verificador de accesibilidad y contraste WCAG 2.1
│   ├── effects.js            # Galería de efectos interactivos
│   ├── glassmorphism.js      # Controles y escenario para Glassmorphism
│   ├── palettes.js           # Explorador y exportador de paletas
│   ├── snippets.js           # Filtro y copia rápida de snippets
│   ├── theme-toggle.js       # Alternador de tema claro/oscuro
│   └── toast.js              # Sistema de notificaciones toast y ayuda para copiar
│
└── styles/
    └── styles.css            # Hoja de estilos globales con variables CSS
```

---

## 🗄️ Base de Datos

El script [backend/db.php](file:///home/jues/htdocs/CssHelper/backend/db.php) gestiona la arquitectura completa de forma automática (Zero-Config):

1. **`users`**: Identificador, nombre de usuario y contraseña encriptada (`bcrypt`).
2. **`fonts`**: Catálogo de Google Fonts con enlaces `<link>`, directivas `@import` y reglas CSS `font-family`.
3. **`favorite_fonts`**: Relación N:M entre `users` y `fonts` con borrado en cascada (`ON DELETE CASCADE`).
4. **`favorite_colors`**: Colores guardados por cada usuario con valores HEX, RGB, HSL y nombre.

> **Auto-instalación:** Al abrir la aplicación en tu navegador con MySQL encendido, `backend/db.php` verifica y crea las tablas automáticamente, e inserta el usuario administrador (`admin` / `123`) y las 10 fuentes iniciales si no existían. No necesitas importar ningún archivo SQL manual.

---

## ⚙️ Instalación y Configuración

> **Requisitos:** PHP 8+ y un servidor local como XAMPP, WAMP, Laragon o el servidor integrado de PHP.

### 1. Clonar el repositorio

```bash
git clone https://github.com/JulianPeralta26/CssHelper.git
```

### 2. Mover la carpeta al directorio público

```bash
# XAMPP en Windows
mv CssHelper C:/xampp/htdocs/

# XAMPP / LAMP en Linux
mv CssHelper /opt/lampp/htdocs/ # o a tu ruta htdocs configurada
```

### 3. Iniciar el servidor

1. Inicia **Apache** y **MySQL** desde el panel de XAMPP.
2. Abre en tu navegador:
   ```
   http://localhost/CssHelper/
   ```

### 4. Variables de Entorno (Opcional)
Si tu MySQL utiliza configuraciones o credenciales distintas a las de XAMPP por defecto (`localhost:3306`, usuario `root` sin contraseña), puedes configurar variables de entorno:
- `CSSHELPER_DB_HOST` (por defecto: `localhost`)
- `CSSHELPER_DB_PORT` (por defecto: `3306`)
- `CSSHELPER_DB_SOCKET` (opcional: ruta a socket UNIX como `/tmp/mysql.sock` o `/run/mysqld/mysqld.sock`)
- `CSSHELPER_DB_USER` (por defecto: `root`)
- `CSSHELPER_DB_PASS` (por defecto: `""`)

> **Prueba rápida sin servidor Apache/MySQL:**  
> Puedes ejecutar directamente desde la carpeta del proyecto:
> ```bash
> php -S localhost:8000
> ```
> Y acceder a `http://localhost:8000`. Toda la aplicación funcionará utilizando el motor de respaldo automático en JSON y PHP.

---

## 🖥️ Uso y Credenciales

- **Acceso a herramientas:** Navega a `/herramientas.php` para usar los generadores.
- **Login de prueba:** 
  - **Usuario:** `admin`
  - **Contraseña:** `123`
- Al iniciar sesión podrás guardar tus fuentes y colores favoritos vinculados a tu cuenta.

---

## 🤝 Contribuciones

¡Las contribuciones son bienvenidas! Si deseas agregar nuevas herramientas, fuentes o paletas:

1. Haz un **fork** del repositorio.
2. Crea una rama con tu cambio: `git checkout -b feature/nueva-funcionalidad`.
3. Haz commit de tus cambios: `git commit -m 'feat: agrega nueva funcionalidad'`.
4. Envía un push a tu rama: `git push origin feature/nueva-funcionalidad`.
5. Abre un **Pull Request**.

---

## 📄 Licencia

Este proyecto está bajo la licencia **MIT**.

---

<p align="center">
  Hecho con ❤️ para la comunidad frontend
</p>
