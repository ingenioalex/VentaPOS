# Documentación VentaPOS

Índice maestro del proyecto. **Toda modificación relevante al código debe reflejarse aquí** según [POLITICA_DOCUMENTACION.md](POLITICA_DOCUMENTACION.md).

## Estado del producto (jun 2026)

| Fase | Alcance | Estado |
|------|---------|--------|
| Fase 1 | POS, productos, ventas, multi-empresa | Completado |
| Fase 2 | Proveedores, compras, reportes compras | Completado |
| Fase 3 | Facturación electrónica SUNAT (simulación + base producción) | En curso |
| Fase 4 | Integración OSE real, firma XML, multi-sucursal avanzado | Pendiente |

**Stack:** Laravel 13 · PHP 8.3 · MySQL 8 · Blade · JavaScript (POS)

---

## Documentos técnicos y de ingeniería

| Documento | Contenido |
|-----------|-----------|
| [ARQUITECTURA.md](ARQUITECTURA.md) | Capas, módulos, multi-tenancy, flujos principales |
| [METODOLOGIA.md](METODOLOGIA.md) | Cómo se desarrolla, ramas, pruebas, convenciones |
| [CASOS_DE_USO.md](CASOS_DE_USO.md) | Actores, casos de uso por módulo |
| [MODULOS_Y_FASES.md](MODULOS_Y_FASES.md) | Mapa de módulos, permisos, planes comerciales |
| [SUNAT.md](SUNAT.md) | Facturación electrónica: flujo, QR, OSE, estados |
| [NOMENCLATURA_BD.md](NOMENCLATURA_BD.md) | Convenciones de tablas y columnas en español |
| [POLITICA_DOCUMENTACION.md](POLITICA_DOCUMENTACION.md) | Cuándo y cómo actualizar esta documentación |

---

## Operación y despliegue

| Documento | Contenido |
|-----------|-----------|
| [ARRANQUE_LOCAL.md](ARRANQUE_LOCAL.md) | Laragon, migraciones, usuarios demo |
| [SETUP_ENTORNO.md](SETUP_ENTORNO.md) | Requisitos de entorno |
| [PUESTA_EN_MARCHA.md](PUESTA_EN_MARCHA.md) | Checklist producción: VPS, SSL, cron, backups |
| [HOSTING_DOMINIO_Y_DESPLIEGUE.md](HOSTING_DOMINIO_Y_DESPLIEGUE.md) | Hosting, dominio, estrategia SaaS |

---

## Negocio y planificación

| Documento | Contenido |
|-----------|-----------|
| [MODELO_NEGOCIO.md](MODELO_NEGOCIO.md) | Planes, pricing, roadmap comercial |
| [ITINERARIO.md](ITINERARIO.md) | Hitos del proyecto |
| [TAREAS_ALEX.md](TAREAS_ALEX.md) | Tareas operativas del responsable |

---

## Legado y referencia

| Documento | Contenido |
|-----------|-----------|
| [MIGRACION_PYTHON.md](MIGRACION_PYTHON.md) | Migración desde RMS desktop (Python) |
| [SCHEMA_RMS_ORIGINAL.md](SCHEMA_RMS_ORIGINAL.md) | Esquema legacy |
| `legacy/rms_db_original.sql` | Dump SQL original |

---

## Registro de cambios recientes

| Fecha | Cambio | Documentos actualizados |
|-------|--------|------------------------|
| 2026-06-26 | Cierre Z de hoy: completa X automáticos; ya no exige X manuales por vendedor | CASOS_DE_USO.md |
| 2026-06-21 | Cierres: Z por día calendario sin solapamiento; UI «Cerrar este día» en orden | CASOS_DE_USO.md |
| 2026-06-21 | Fix cierre directo: X excepcional para supervisor/dueño con ventas; feedback en panel | CASOS_DE_USO.md |
| 2026-06-21 | Cierres excepcionales: sección tras historial, paginación 5, orden reciente→antiguo, botón cierre directo (X+Z) | CASOS_DE_USO.md |
| 2026-06-26 | Cierres: panel jornadas sin Z siempre visible + cierre excepcional por fecha manual | CASOS_DE_USO.md |
| 2026-06-26 | POS: columna UM, cantidad decimal en KG y carrito incremental (sin recargar tabla entera) | CASOS_DE_USO.md |
| 2026-06-26 | POS: panel detalle más ancho; montos alineados; código de barra bajo producto | CASOS_DE_USO.md |
| 2026-06-26 | POS: columna P.U. en detalle; descuento % y efectivo recibido en enteros; sin bloqueo al escribir | CASOS_DE_USO.md |
| 2026-06-26 | Cierres: sincronización automática de datos, historial cronológico y tabla X por jornada | CASOS_DE_USO.md |
| 2026-06-25 | Cierres X/Z: totales por período acotado; Z = suma de X; recálculo histórico | CASOS_DE_USO.md |
| 2026-06-25 | Cierres: fechas en formato local (d/m/Y H:i), período visible y reparación de fechas inconsistentes | CASOS_DE_USO.md |
| 2026-06-21 | Cierres: panel dual ventas por medio + arqueo efectivo; historial sin columna «Vendido» | CASOS_DE_USO.md |
| 2026-06-21 | Limpieza cierres duplicados en BD (`ventapos:limpiar-cierres-duplicados`) y UI sin KPIs repetidos | CASOS_DE_USO.md |
| 2026-06-21 | Cierre X: registra arqueo, cierra sesión cajero e impresión opcional (sin auto-print) | CASOS_DE_USO.md |
| 2026-06-22 | VPS: cron scheduler + backup MySQL 03:00 Lima (`deploy/vps-cron-backup.sh`) | PUESTA_EN_MARCHA.md |
| 2026-06-22 | Fix 500: import ProspectoController; Blade form admin empresas | Este índice |
| 2026-06-21 | Página precios: diseño dinámico, tarjetas por color, animaciones scroll | Este índice |
| 2026-06-21 | Beta pública: 1 mes gratis (antes 2) en landing, contacto y config planes | Este índice |
| 2026-06-08 | Cupos usuarios flexibles: bonificados + add-on por empresa | MODULOS_Y_FASES.md, PlanLimiteService |
| 2026-06-08 | Admin tarifas: super admin edita precios planes/módulos/extras | MODULOS_Y_FASES.md, storage/app/tarifas.json |
| 2026-06-08 | Extras como add-ons S/9: gestión super admin (no dueño) | MODULOS_Y_FASES.md, config/extras.php |
| 2026-06-08 | Extras plan: WhatsApp, lotes, flejes, categorías jerárquicas, KPI ventas | MODULOS_Y_FASES.md, config/extras.php |
| 2026-06-08 | Reingeniería documental + mejora QR ticket PNG | Este índice, SUNAT, ARQUITECTURA, etc. |
| 2026-06-08 | Fase 3 SUNAT: OseClient, CDR, QR, tests | SUNAT.md, MODULOS_Y_FASES.md |
| 2026-06-08 | Fase 2 compras y proveedores | MODULOS_Y_FASES.md |

*Ampliar esta tabla en cada entrega significativa.*
