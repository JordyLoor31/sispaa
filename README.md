# SISPAA — Sistema de Planificación Académica y Administrativa

Sistema web para la gestión académica y administrativa de una unidad académica universitaria: matrículas, sílabos, informes docentes, faltas y justificaciones, titulación, expedientes de documentos estudiantiles, plantillas de documentos, laboratorios/inventario, investigación, vinculación y reportes, con control de acceso basado en roles (RBAC).

## Stack tecnológico

- **Backend:** Laravel 12 (PHP 8.2+), Inertia.js v2, Spatie `laravel-permission` (roles y permisos), `barryvdh/laravel-dompdf` (PDF), `maatwebsite/excel` (Excel).
- **Frontend:** Vue 3 (Composition API) + TypeScript, Inertia.js, Tailwind CSS, shadcn-vue (estilo "new-york"), TanStack Table, ApexCharts, vee-validate + zod.
- **Base de datos:** PostgreSQL.
- **Build tool:** Vite.
- **Testing:** Pest (PHP).

## Instalaciones previas

Antes de clonar el proyecto, instala en tu equipo:

| Herramienta | Versión mínima | Verificar con |
|---|---|---|
| PHP | 8.2 | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 18+ (recomendado 20+) | `node -v` |
| npm | 9+ | `npm -v` |
| PostgreSQL | 13+ | `psql --version` |
| Git | cualquiera reciente | `git --version` |

Extensiones de PHP requeridas por Laravel: `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`. Si usas XAMPP en Windows, habilítalas en `php.ini` (descomentar `extension=pdo_pgsql` y `extension=pgsql`) y reinicia Apache.

## Instalación del proyecto

1. **Clonar el repositorio**

   ```bash
   git clone https://github.com/JordyLoor31/sispaa.git
   cd sispaa/sispaa-project
   ```

2. **Instalar dependencias PHP**

   ```bash
   composer update
   ```

3. **Instalar dependencias de Node**

   ```bash
   npm install
   ```

4. **Configurar el entorno**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Edita `.env` y configura la conexión a PostgreSQL:

   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=sispaa
   DB_USERNAME=postgres
   DB_PASSWORD=tu_password
   ```

   Crea la base de datos vacía en PostgreSQL antes de continuar (por ejemplo con `psql` o pgAdmin):

   ```sql
   CREATE DATABASE sispaa;
   ```

5. **Ejecutar migraciones y poblar datos de prueba**

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

   El seeder principal (`DatabaseSeeder`) crea carreras, materias, roles/permisos y usuarios de prueba (uno por rol), además de datos de ejemplo para docentes, estudiantes, faltas, documentos, titulación e informes.

   > `php artisan migrate:fresh --seed` reconstruye la base de datos desde cero si necesitas empezar limpio.

6. **Enlazar el storage público**

   El proyecto sirve archivos subidos (sílabos, informes, plantillas de documentos, etc.) mediante rutas autenticadas de Laravel (`Storage::disk('public')->response(...)`), por lo que el symlink de `storage:link` **no es estrictamente necesario** para esas descargas. Aun así, es buena práctica crearlo:

   ```bash
   php artisan storage:link
   ```

   > En entornos XAMPP/Windows, `storage:link` puede fallar o crear un symlink roto (403 al acceder por `/storage/...`). Si eso ocurre, no afecta a los módulos que usan las rutas autenticadas de descarga descritas arriba.

7. **Compilar los assets del frontend**

   Para desarrollo:

   ```bash
   npm run dev
   ```

   Para producción:

   ```bash
   npm run build
   ```

8. **Levantar el servidor**

   ```bash
   php artisan serve
   ```

   La aplicación quedará disponible en `http://localhost:8000`.

### Levantar todo con un solo comando

Composer incluye un script que corre en paralelo el servidor PHP, el worker de colas y Vite:

```bash
composer dev
```

## Usuarios de prueba (creados por el seeder)

| Rol | Email | Contraseña |
|---|---|---|
| SystemAdministrador | test@example.com | password |
| Secretaría | secretaria@example.com | Secretaria2026! |
| Coordinador | coordinador@example.com | Coordinador2026! |

Los usuarios de docentes y estudiantes adicionales se generan vía `DocentesSeeder` y `EstudiantesSeeder` (revisa esos archivos en `database/seeders/` para ver el patrón de credenciales generadas).

> Estas credenciales son solo para entorno local/desarrollo. No uses estos valores en producción.

## Roles del sistema

El control de acceso usa `spatie/laravel-permission`. Roles definidos:

- **SystemAdministrador** — acceso total (bypass vía `Gate::before`), ve una vista consolidada en cascada de todos los módulos de todos los roles.
- **secretaria** — matrículas, revisión de sílabos e informes, justificaciones, convocatorias, plantillas de documentos, expedientes.
- **coordinador** — titulación, aprobación curricular, vinculación.
- **docente** — sílabos, informes, laboratorio/inventario, investigación.
- **estudiante** — indicadores propios, matrículas, faltas, justificaciones, documentos y plantillas descargables.

## Estructura del proyecto

```
app/
  Http/Controllers/     # Controladores agrupados por módulo (Admin, Docencia, Secretaria, Estudiantes, Titulacion, Investigacion, Laboratorio, Vinculacion, Reportes, KPIs, Documentos...)
  Models/                # Modelos agrupados por el mismo criterio de módulos
database/
  migrations/            # Esquema de base de datos
  seeders/                # Datos de prueba
resources/
  js/
    pages/               # Vistas Inertia (Vue), organizadas por módulo/rol
    components/          # Componentes reutilizables (incl. shadcn-vue)
    layouts/             # Layouts (AppSidebarLayout, etc.)
    config/navigation.ts # Configuración del menú lateral por rol
routes/
  web.php                # Rutas web, agrupadas por rol/middleware
```

## Comandos útiles

```bash
# Backend
php artisan migrate            # Ejecutar migraciones pendientes
php artisan migrate:fresh --seed  # Reconstruir BD desde cero con datos de prueba
php artisan route:list         # Listar rutas registradas
php artisan tinker             # Consola interactiva
composer test                  # (si está configurado) correr tests con Pest
./vendor/bin/pest               # Correr tests directamente

# Frontend
npm run dev                    # Vite en modo desarrollo (hot reload)
npm run build                  # Build de producción
npm run lint                   # ESLint con --fix
npm run format                 # Prettier sobre resources/
npm run format:check           # Verificar formato sin modificar
npx vue-tsc --noEmit           # Verificación de tipos TypeScript
```

## Notas de arquitectura

- **Inertia + Vue**: no hay API REST separada; los controladores devuelven vistas Inertia (`Inertia::render(...)`) que hidratan componentes Vue en `resources/js/pages`.
- **Tablas de datos**: los listados usan TanStack Table + paginación real del backend (`paginate()->withQueryString()`), con filtros y búsqueda por texto vía `ilike` (PostgreSQL).
- **Formularios**: los formularios sin archivos usan `vee-validate` + `zod`; los formularios con carga de archivos usan `useForm` de Inertia (multipart automático), y en edición con archivo se usa el patrón de spoof `_method: 'put'` sobre POST.
- **Auditoría**: varios modelos usan el trait `HasAuditFields` para completar automáticamente `created_by`/`updated_by`.
- **Sidebar dinámico**: `resources/js/config/navigation.ts` define el menú por rol; SystemAdministrador ve una vista consolidada en cascada de todos los roles, con lógica de deduplicación para evitar mostrar el mismo módulo dos veces.

## Solución de problemas comunes

- **Error de conexión a PostgreSQL**: verifica que el servicio esté corriendo y que las credenciales en `.env` coincidan con tu instalación local.
- **`php artisan key:generate` no genera clave**: confirma que el archivo `.env` existe (copiado desde `.env.example`).
- **Assets no cargan en desarrollo**: asegúrate de tener `npm run dev` corriendo en paralelo al servidor PHP (o usa `composer dev`).
- **403 al descargar archivos vía `/storage/...`**: es el symlink roto típico de XAMPP/Windows; los módulos nuevos (Informes, Plantillas, Sílabos recientes) ya no dependen de él porque sirven los archivos mediante rutas autenticadas de Laravel.

## Licencia

MIT.
