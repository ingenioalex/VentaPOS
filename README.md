# VentaPOS

Sistema POS + inventario multi-empresa (SaaS) para bodegas, ferreterías y farmacias en Perú.

**Stack:** Laravel 13 · PHP 8.3 · MySQL 8 · Blade

## Arranque rápido

### Primera vez (con verificación automática)

**Windows (PowerShell):**
```powershell
cd D:\Proyectos\VentaPOS_build
.\iniciar-con-verificacion.ps1
```

**Linux/Mac:**
```bash
cd /ruta/a/VentaPOS_build
./iniciar-con-verificacion.sh
```

Este script verifica y configura automáticamente:
- Archivo `.env` y `APP_KEY`
- Base de datos SQLite o MySQL
- Migraciones y datos de ejemplo
- Permisos y caché

### Arranque normal (sistema ya configurado)

```powershell
.\iniciar.ps1
```

Abre **http://127.0.0.1:8000**

> ⚠️ **¿POS no funciona?** Ver guía completa: [docs/SOLUCION_POS_LOCAL.md](docs/SOLUCION_POS_LOCAL.md)

### Usuarios demo

| Usuario | Contraseña | Rol |
|---------|------------|-----|
| admin | admin123 | dueño |
| cajero | cajero123 | cajero |

Super admin: `/admin/login`

## Documentación

**Índice completo:** [docs/README.md](docs/README.md)

| Documento | Para qué |
|-----------|----------|
| [ARRANQUE_LOCAL.md](docs/ARRANQUE_LOCAL.md) | Instalación local |
| [SOLUCION_POS_LOCAL.md](docs/SOLUCION_POS_LOCAL.md) | **🔧 Solución: POS no funciona localmente** |
| [ARQUITECTURA.md](docs/ARQUITECTURA.md) | Diseño del sistema |
| [CASOS_DE_USO.md](docs/CASOS_DE_USO.md) | Funcionalidad por actor |
| [MODULOS_Y_FASES.md](docs/MODULOS_Y_FASES.md) | Módulos y roadmap |
| [SUNAT.md](docs/SUNAT.md) | Facturación electrónica |
| [PUESTA_EN_MARCHA.md](docs/PUESTA_EN_MARCHA.md) | Deploy producción |
| [POLITICA_DOCUMENTACION.md](docs/POLITICA_DOCUMENTACION.md) | Mantener docs al día |

## Estructura rápida

| Ruta | Descripción |
|------|-------------|
| `/` | Landing |
| `/app/pos` | Punto de venta |
| `/app/sunat` | Config facturación electrónica |
| `/admin` | Panel super admin |

## Proyecto original Python

Referencia legacy: `D:\Proyectos\rms_desktop\`
