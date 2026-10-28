# Propuesta Técnica del Sistema — Control Electoral Ecuador

## 1. Resumen ejecutivo
Sistema web para registrar, verificar y consolidar resultados electorales por JRV, con trazabilidad total desde el recinto hasta el reporte nacional.

- **Objetivo:** control de JRV, registro de resultados con foto del acta, verificación por el administrador, consolidación por dignidad y territorio, reportes y auditoría.
- **Estado:** sistema funcional con 18 controladores web + API REST JWT.
- **Acceso actual:** `http://elecciones.test/` (baseURL dinámica; también funciona en `http://localhost:8080/`).

## 2. Arquitectura técnica

```
Navegador (Bootstrap 5 + DataTables + Chart.js + SweetAlert2)
        |
        v
CodeIgniter 4.7 — PHP 8.2
  |- Web por sesión: app/Controllers/Web/*.php + app/Views/*.php
  |- API JWT: app/Controllers/Api/*.php
        |
        v
MySQL `elecciones` — 20 tablas + migraciones + seeders
Archivos: public/uploads/minutes/{election_id}/{polling_station_id}/
```

### Stack verificado (`composer.json`)
- `codeigniter4/framework ^4.7`, PHP `^8.2`
- `firebase/php-jwt ^7.1` (API)
- `dompdf/dompdf ^3.1` (PDF), `phpoffice/phpspreadsheet ^5.8` (Excel)
- Frontend: jQuery 3.7.1, Bootstrap 5.3.3, Chart.js 4.4.0, DataTables 1.13.6, SweetAlert2

### Decisiones técnicas clave
- Vistas server-rendered CodeIgniter 4 + Bootstrap 5 (sin SPA).
- Sesión para la web (`/ajax/*` por sesión); JWT solo para `/api/*`.
- Actas versionadas (`version`, `is_current`).
- Verificación de resultados (`is_verified`, `verified_by`, `verified_at`).
- Auditoría con `old_values`/`new_values` en JSON.

## 3. Roles y accesos

| Rol | Acceso |
|---|---|
| Administrador General | Todo: configuración, JRV, asignaciones, actas, edición/verificación, reportes, auditoría |
| Operador Electoral | Solo JRV asignadas: Registrar Resultados, Actas, Mi Perfil |
| Visualizador / Supervisor | Lectura y monitoreo: Dashboard, Resultados, Reportes |

El operador es redirigido automáticamente a `/results/register`.

## 4. Módulos del sistema

### M1. Autenticación y Sesión — `Web\AuthController`
Login por usuario/clave, sesión (`isLoggedIn`, `user_id`, `role`), cierre de sesión y redirección por rol.

### M2. Dashboard Ejecutivo — `/dashboard` (`Web\DashboardController`)
KPIs: Total JRV, Reportadas, Pendientes, % Avance, electores, votantes y participación. Avance por provincia con barras, doughnut comparativo JRV con total centrado, resultados por dignidad con ganador y tabla de JRV pendientes.

### M3. Usuarios y Roles — `/users`, `/roles`
CRUD de usuarios con asignación de rol; gestión de roles y permisos (`roles`, `permissions`, `role_permissions`).

### M4. Elecciones y Dignidades — `/elections`, `/dignities`
Elecciones (activas/en curso) y dignidades activas. Todo el flujo filtra por `election_id`.

### M5. Partidos, Listas y Candidatos — `/parties`, `/candidates`
Partidos con sigla, número de lista y color; candidatos por elección, dignidad y partido.

### M6. Estructura Territorial — `/territorial`
Provincias (25 seed), cantones, parroquias y recintos. Selects en cascada vía `/ajax/*` con sesión.

### M7. JRV — `/polling-stations` (`Web\PollingStationController`)
- Import Excel que expande rangos femenino/masculino a JRV individuales (`F-n`/`M-n`, columna `genero`).
- Filtros Provincia/Cantón/Parroquia/Recinto, columna Votos, edición de electores y estado.
- Estados: `pendiente, asignada, en_proceso, reportada, revisada, aprobada`.

### M8. Asignaciones — `/assignments` (`Web\AssignmentController`)
Filtro jerárquico hasta JRV, selección múltiple y asignación por lotes. JRV ya asignadas se atenúan y bloquean. Edición de operador; al eliminar, la JRV vuelve a `pendiente` si no tiene otras asignaciones activas.

### M9. Registro de Resultados — `/results/register` (`Web\ResultController::save`)
Por dignidad: carga candidatos y votos guardados, captura del acta por cámara/archivo con geolocalización. Guarda en `election_results` + acta versionada en `uploaded_minutes` y marca la JRV como `reportada`.

### M10. Consulta de Resultados — `/results`
Filtros Elección → Dignidad → Territorio → JRV. Blancos/Nulos/Sufragantes una sola vez por JRV. Gráfico de barras con gradiente, bordes redondeados y animación.

### M11. Actas — `/minutes` (`Web\MinuteController`)
Lista con JRV, género, ubicación completa, tipo/tamaño, responsable y fecha. Acciones: ver, descargar, eliminar (solo admin) y **Editar/Verificar (solo admin)**:
- Modal `modal-xl`: foto a la izquierda, votos por candidato agrupados por dignidad a la derecha, más Blancos/Nulos/Sufragantes/Electores y switch de verificación.
- Datos vía `GET ajax/minute-data/(:num)`; guardado vía `POST minutes/update`.
- Auditoría: una entrada `update_minute` en `election_results` con JSON antes/después.

### M12. Reportes — `/reports` (`Web\ReportController`)
Exportación PDF (DomPDF) y Excel (PhpSpreadsheet), más reporte de avance.

### M13. Auditoría — `/audit`
Tabla `audits`: `action, table_name, record_id, old_values/new_values JSON, ip_address, user_agent, created_at`. Acciones clave: `register_results, upload_minute, delete_minute, update_minute`.

### M14. API REST — `/api/*` (JWT)
Auth/refresh, CRUD de usuarios, roles, elecciones, dignidades, partidos, listas, candidatos, territorio, JRV, asignaciones, resultados, actas, dashboard, reportes y auditoría. Para app móvil o integraciones.

## 5. Flujo operativo end-to-end (guion de demo)
1. Crear elección + dignidades + partidos + candidatos.
2. Importar JRV por Excel (rangos F/M).
3. Asignar JRV a operadores.
4. El operador registra votos + foto del acta con geolocalización.
5. El administrador abre el acta en `/minutes`, compara foto vs datos, corrige y marca **verificada**.
6. El Dashboard muestra el avance, `/results` consolida, `/reports` exporta y `/audit` demuestra la trazabilidad.

## 6. Seguridad y trazabilidad
- Sesión web + JWT para API; rutas de escritura con CSRF.
- Edición/verificación de actas restringida a Administrador General (vista y servidor).
- Logs con usuario, IP, user agent y valores antes/después.
- Archivos fuera del alcance de ejecución, servidos por `preview`/`download` con validación de existencia.

## 7. Estructura de presentación sugerida (12 láminas)
1. Portada + objetivo. 2. Arquitectura + stack. 3. Roles. 4. Configuración (elecciones, dignidades, partidos, candidatos). 5. Territorio + JRV + importación. 6. Asignaciones. 7. Registro + acta + geolocalización. 8. Verificación de actas (demo en vivo). 9. Dashboard + resultados. 10. Reportes PDF/Excel. 11. Seguridad + auditoría. 12. Roadmap y preguntas.

## 8. Roadmap propuesto
- Firma digital / segundo verificador por acta.
- Tablero público de resultados (solo lectura).
- App móvil offline-first consumiendo `/api/*`.
- Alertas de inconsistencias (sumas que no cuadran vs sufragantes).
- Respaldo automático de `uploads/minutes` y base de datos.
