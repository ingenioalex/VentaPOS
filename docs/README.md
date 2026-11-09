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
| [IDENTIDAD_MARCA.md](IDENTIDAD_MARCA.md) | Logo, icono, favicon, variantes y usos |
| [ventasagil/](ventasagil/README.md) | Chats de Cursor exportados (trabajo ventasagil.com) |
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
| [BREVO_CORREO.md](BREVO_CORREO.md) | SMTP Brevo para recuperación de clave y correos del sistema |
| [SEGURIDAD.md](SEGURIDAD.md) | Panel admin discreto, contraseñas, antispam beta |
| [SEO.md](SEO.md) | Posicionamiento Google: Search Console, keywords, plan 90 días |
| [IMPRESORAS.md](IMPRESORAS.md) | Configuración de impresora por empresa/usuario y hardware recomendado |
| [HOSTING_DOMINIO_Y_DESPLIEGUE.md](HOSTING_DOMINIO_Y_DESPLIEGUE.md) | Hosting, dominio, estrategia SaaS |

---

## Negocio y planificación

| Documento | Contenido |
|-----------|-----------|
| [MODELO_NEGOCIO.md](MODELO_NEGOCIO.md) | Planes, pricing, roadmap comercial |
| [VENTA_Y_ONBOARDING.md](VENTA_Y_ONBOARDING.md) | Abordaje prospectos, planes, SUNAT, checklist cliente |
| [plantillas/WHATSAPP_PROSPECTOS.md](plantillas/WHATSAPP_PROSPECTOS.md) | Mensajes WhatsApp copiar/pegar |
| [plantillas/guia-prospecto-sunat.html](plantillas/guia-prospecto-sunat.html) | PDF imprimible para entregar al cliente |
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
| 2026-11-09 | Tarjetas con color, zoom y lectura en celular en reportes y módulos que aún usaban cifras planas | Este índice |
| 2026-11-09 | Compra duplicada: si la factura ya existe, abre la registrada y no muestra el error SQL | CASOS_DE_USO.md, Este índice |
| 2026-11-09 | Reporte de inventario: tarjetas con color, zoom al pasar el mouse y lectura en celular | Este índice |
| 2026-11-01 | Código de producción del VPS incorporado a `main` (Sección K cierre y rutas). La base del VPS no se migró | NOMENCLATURA_BD.md, Este índice |
| 2026-11-01 | Continuación del chat y acuerdo de trabajo (local, GitHub, VPS sin cambios de BD) | ventasagil/CONTEXTO-TRABAJO.md, ventasagil/CHAT-CURSOR-2026-11-01.md, Este índice |
| 2026-11-01 | Exportación del chat Cursor a `docs/ventasagil/` y copia en GitHub | ventasagil/README.md, Este índice |
| 2026-10-10 | SSL producción: cert caducado (token Hostinger 401); renovado HTTP-01 + script de recuperación | PUESTA_EN_MARCHA.md, Este índice |
| 2026-10-10 | BAT `recuperar-local.bat`: mata php.exe colgados, restaura web.php, Laragon + iniciar.ps1 | ARRANQUE_LOCAL.md, Este índice |
| 2026-07-22 | Identidad de marca VentasÁgil: icono, wordmark, logos H/V, favicon, WhatsApp, OG | IDENTIDAD_MARCA.md, Este índice |
| 2026-07-08 | Impresora: administrador configura impresora por usuario o default empresa | IMPRESORAS.md, CASOS_DE_USO.md, Este índice |
| 2026-07-08 | Impresora: opción Bluetooth en método de conexión + ayuda contextual | IMPRESORAS.md, Este índice |
| 2026-07-07 | Inventario: botones Entrada/Salida/Ajuste por producto en stock y movimientos | Este índice |
| 2026-07-05 | Kardex: botones +/−/A, diseño responsive y producto preseleccionado en movimientos | Este índice |
| 2026-07-05 | Cierres: rectificación de arqueo en Cierre X antes del Cierre Z (error al digitar) | CASOS_DE_USO.md, Este índice |
| 2026-07-05 | POS: pestañas de categorías solo si tienen productos activos (escritorio y móvil) | Este índice |
| 2026-07-05 | Módulo impresora: config por empresa y usuario, ticket 58/80 mm, guía hardware | IMPRESORAS.md, MODULOS_Y_FASES.md, CASOS_DE_USO.md, NOMENCLATURA_BD.md, Este índice |
| 2026-07-05 | POS móvil: detalle venta compacto (barra·UM·precio en 1 línea); categorías colapsables con «ver más» | Este índice |
| 2026-07-05 | POS: detalle venta responsive en móvil (tarjetas por línea, sin tabla estrecha) | Este índice |
| 2026-07-01 | Landing: chatbox de ayuda (planes/módulos/SUNAT) con respuestas básicas sin IA externa | ARQUITECTURA.md, CASOS_DE_USO.md, MODULOS_Y_FASES.md, Este índice |
| 2026-07-01 | SEO desplegado en VPS; script `deploy/seo-deploy.sh`; robots.txt con bloqueo admin | SEO.md, Este índice |
| 2026-06-21 | SEO: rutas sitemap/robots/FAQ, meta por página, schema.org, FAQ ampliada | SEO.md, Este índice |
| 2026-06-21 | Admin guía: +plantillas agrupadas, aclaración «solo consulta/copiar» | VENTA_Y_ONBOARDING.md, Este índice |
| 2026-06-21 | Admin: guía de respuesta (plantillas WhatsApp + PDF) en panel super admin | VENTA_Y_ONBOARDING.md, Este índice |
| 2026-06-21 | Guía venta/onboarding: plantillas WhatsApp + HTML imprimible (PDF) para prospectos y SUNAT | VENTA_Y_ONBOARDING.md, plantillas/, Este índice |
| 2026-06-21 | Fix paginación: vista propia sin Tailwind (evita chevron SVG gigante en móvil) | Este índice |
| 2026-06-21 | Productos: UI rediseñada (KPIs, tabla con etiquetas, formulario e importación) | Este índice |
| 2026-06-21 | Productos: selector «Ordenar por» (SKU, nombre, barra, categoría, precio, stock, U.M., etc.) | Este índice |
| 2026-06-21 | Productos: columnas Categoría + Subcategoría y filtro cuando extra `categorias_jerarquicas` activo | MODULOS_Y_FASES.md, Este índice |
| 2026-06-29 | Recuperación admin sin correo: email enmascarado + enlace en pantalla | SEGURIDAD.md, Este índice |
| 2026-06-29 | Correo Brevo: guía SMTP, comando `ventapos:probar-correo`, script deploy | BREVO_CORREO.md, SEGURIDAD.md, Este índice |
| 2026-06-29 | Recuperación clave super admin: pregunta secreta + enlace a correo secundario | SEGURIDAD.md, NOMENCLATURA_BD.md, ARRANQUE_LOCAL.md, Este índice |
| 2026-06-28 | Prospectos: switch para ocultar/mostrar descartados (activo por defecto) | SEGURIDAD.md, Este índice |
| 2026-06-28 | Seguridad admin: ruta discreta, login con rate limit, contraseñas fuertes; antispam beta; vista prospecto completa | SEGURIDAD.md, PUESTA_EN_MARCHA.md, Este índice |
| 2026-06-28 | Cierre Z hoy independiente de días anteriores; X pendiente solo si hay ventas post-X | CASOS_DE_USO.md, Este índice |
| 2026-06-21 | Admin contratación: módulos reagrupados (Reportes visible; SUNAT e infra separados) | MODULOS_Y_FASES.md, Este índice |
| 2026-06-21 | POS cobro: SUNAT en segundo plano (`afterResponse`); UI no bloquea campo efectivo entre ventas | CASOS_DE_USO.md, SUNAT.md, Este índice |
| 2026-06-28 | Cierre Z: suma solo Cierres X (sin recalcular ventas ni X automáticos) | CASOS_DE_USO.md, Este índice |
| 2026-06-28 | Optimización Cierre Z: menos reparaciones históricas y validaciones más rápidas | CASOS_DE_USO.md, Este índice |
| 2026-06-28 | Menú lateral reorganizado (Gestión, Reportes, Configuración); reportes cierres y productos | MODULOS_Y_FASES.md, Este índice |
| 2026-06-28 | Panel inicio rediseñado (tarjetas KPI + acciones); Categorías/Clientes en menú Catálogo | Este índice |
| 2026-06-28 | Seguridad ventas: route binding por empresa (anti-IDOR); UI ventas rediseñada | ARQUITECTURA.md, CASOS_DE_USO.md |
| 2026-06-28 | Deploy VPS: SUNAT Fase 3 + RUC persona natural demo (10428676918) | SUNAT.md, PUESTA_EN_MARCHA.md |
| 2026-06-28 | SUNAT producción: integración Nubefact, OSE genérico, firma XML, credenciales cifradas, reintentos | SUNAT.md, MODULOS_Y_FASES.md |
| 2026-06-21 | Contacto: sin enlace CV; WhatsApp solo botón (sin número visible) | Este índice |
| 2026-06-21 | CV PDF público en `/cv/yuri-vargas-lazo-cv-2026.pdf`; enlace en contacto | Este índice |
| 2026-06-21 | Contacto: tarjeta fundador + enlace LinkedIn; deploy VPS | Este índice, PUESTA_EN_MARCHA.md |
| 2026-06-21 | CV mejorado y guía LinkedIn (`docs/personal/`) | docs/personal/ |
| 2026-06-26 | Login cajero: modo bloqueo — reingreso con contraseña si el turno sigue abierto | CASOS_DE_USO.md |
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
