# Política de documentación

## Objetivo

Mantener la documentación en `docs/` alineada con el código en todo momento. La documentación es parte del entregable, no un apéndice opcional.

## Cuándo actualizar

Actualice documentación **en el mismo cambio de código** cuando modifique:

| Tipo de cambio | Documentos a revisar |
|----------------|---------------------|
| Nuevo módulo o ruta | `MODULOS_Y_FASES.md`, `CASOS_DE_USO.md`, `ARQUITECTURA.md`, `docs/README.md` |
| Cambio de permisos/roles | `MODULOS_Y_FASES.md`, `config/permisos.php` (comentarios) |
| Migración de BD | `NOMENCLATURA_BD.md`, `ARQUITECTURA.md` |
| SUNAT / facturación | `SUNAT.md`, `MODULOS_Y_FASES.md` |
| Despliegue / env | `PUESTA_EN_MARCHA.md`, `ARRANQUE_LOCAL.md` |
| Flujo de negocio visible al usuario | `CASOS_DE_USO.md`, guía in-app si aplica |
| Plan comercial | `MODELO_NEGOCIO.md`, `MODULOS_Y_FASES.md` |

## Qué actualizar en cada entrega

1. El documento técnico específico del área tocada.
2. La tabla **Registro de cambios** en `docs/README.md` (fecha, resumen, archivos).
3. `README.md` de la raíz si cambian URLs demo, requisitos o arranque.

## Convenciones

- Idioma: **español** en documentación de negocio y operación; términos técnicos en inglés cuando sea estándar (POS, SUNAT, OSE).
- Rutas y paths reales del repositorio, no genéricos.
- Diagramas en Mermaid cuando el flujo tenga más de 3 pasos.
- No duplicar: enlazar entre documentos en lugar de copiar párrafos largos.

## Responsabilidad

- **Desarrollador / agente IA:** actualizar docs junto con el código.
- **Revisión:** antes de merge o entrega, verificar que `docs/README.md` refleje el cambio.

## Para agentes de Cursor

Al terminar una tarea que toque comportamiento, API, BD, permisos o despliegue:

1. Identificar documentos afectados según la tabla anterior.
2. Actualizarlos en la misma sesión.
3. Añadir fila al registro de cambios en `docs/README.md`.
