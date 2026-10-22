# 🚀 VentaPOS - Guía Visual de Inicio Rápido

## ¿No puedes usar el POS? Sigue estos pasos

### 🎯 Solución en 3 pasos

```
┌─────────────────────────────────────────────────────────┐
│  PASO 1: Descargar el Proyecto                          │
│  ├─ Clonar repositorio o descomprimir ZIP               │
│  └─ Ubicar en: D:\Proyectos\VentaPOS_build             │
└─────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────┐
│  PASO 2: Ejecutar Script de Verificación                │
│  ├─ Abrir PowerShell en la carpeta del proyecto        │
│  ├─ Ejecutar: .\iniciar-con-verificacion.ps1           │
│  └─ El script configurará TODO automáticamente          │
└─────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────┐
│  PASO 3: Abrir el Navegador                             │
│  ├─ Automático: http://127.0.0.1:8000                  │
│  ├─ Login: admin                                        │
│  └─ Password: admin123                                  │
└─────────────────────────────────────────────────────────┘
```

---

## 🔍 ¿Qué verifica el script automáticamente?

| # | Verificación | Si Falta | Acción Automática |
|---|--------------|----------|-------------------|
| 1️⃣ | PHP 8.3 instalado | ⚠️ Error | Indica ruta correcta |
| 2️⃣ | Archivo `.env` | ❌ No existe | ✅ Crea desde `.env.example` |
| 3️⃣ | `APP_KEY` configurada | ❌ Vacía | ✅ Genera automáticamente |
| 4️⃣ | Base de datos creada | ❌ No existe | ✅ Crea SQLite o indica MySQL |
| 5️⃣ | Tablas migradas | ❌ No existen | ✅ Ejecuta migraciones + datos |
| 6️⃣ | Permisos storage | ⚠️ Incorrectos | ✅ Configura permisos |
| 7️⃣ | Caché limpia | ⚠️ Corrupta | ✅ Limpia todo |

---

## 🎭 Flujo Visual: Antes vs Después

### ❌ ANTES (Manual - Complejo)

```
Usuario intenta acceder al POS
         ↓
    ¿Funciona?
         ↓ NO
    Error: "No tiene permiso"
         ↓
    Buscar en código/docs
         ↓
    Verificar permisos manualmente
         ↓
    Error: "Database not found"
         ↓
    Crear .env manualmente
         ↓
    Configurar base de datos
         ↓
    Ejecutar comandos uno por uno
         ↓
    Error: "APP_KEY missing"
         ↓
    Generar key manualmente
         ↓
    Más errores...
         ↓
    😫 Frustración (30+ minutos)
```

### ✅ DESPUÉS (Automatizado - Simple)

```
Usuario ejecuta:
.\iniciar-con-verificacion.ps1
         ↓
Script verifica TODO
         ↓
Autoconfigura TODO
         ↓
Navegador se abre automáticamente
         ↓
Login: admin / admin123
         ↓
😊 POS funcionando (1-2 minutos)
```

---

## 🎨 Mapa de Navegación del Sistema

```
Login (/)
    ↓
Dashboard (/app)
    ↓
    ├─→ 🛒 POS (/app/pos) ⭐ PRINCIPAL
    │   ├─ Versión Escritorio
    │   └─ Versión Móvil (/app/pos/movil)
    │
    ├─→ 📦 Productos (/app/productos)
    │   ├─ Listar
    │   ├─ Crear
    │   └─ Importar CSV
    │
    ├─→ 📊 Ventas (/app/ventas)
    │   ├─ Historial
    │   └─ Detalles
    │
    ├─→ 💰 Cierres (/app/cierres)
    │   ├─ Cierre X (parcial)
    │   └─ Cierre Z (final)
    │
    ├─→ 👥 Clientes (/app/clientes)
    ├─→ 📈 Reportes (/app/reportes)
    ├─→ 📦 Inventario (/app/inventario)
    │
    └─→ ⚙️ Administración (solo admin)
        ├─ Usuarios (/app/usuarios)
        ├─ Permisos (/app/permisos)
        ├─ Terminales (/app/terminales)
        └─ Configuración (/app/configuracion)
```

---

## 🚨 Problemas Comunes - Solución Visual

### Problema 1: "No tiene permiso para acceder a este módulo"

```
❌ Error mostrado en pantalla
         ↓
📋 Causa: Usuario sin módulo POS
         ↓
✅ Solución Express:
   Login como: admin / admin123
         ↓
🎉 Acceso completo al POS
```

**Solución Alternativa:**
```
Login como admin → /app/permisos
         ↓
Seleccionar rol del usuario
         ↓
☑️ Marcar "POS Venta"
         ↓
Guardar cambios
         ↓
Usuario puede acceder al POS
```

---

### Problema 2: Pantalla en blanco o "Sin terminal activa"

```
❌ Cajero sin terminal
         ↓
📋 Causa: Usuario cajero necesita terminal asignada
         ↓
✅ Solución Rápida:
   Usar admin (no necesita terminal)
         ↓
🎉 POS funciona
```

**Solución Definitiva:**
```
Admin → /app/terminales
         ↓
Crear Terminal: "Caja 1"
         ↓
Admin → /app/usuario-terminales
         ↓
Asignar Terminal a Cajero
         ↓
Cajero ya puede usar POS
```

---

### Problema 3: Base de datos no existe

```
❌ Error SQL / Database not found
         ↓
📋 Causa: Sin migraciones
         ↓
✅ Solución Automática:
   .\iniciar-con-verificacion.ps1
         ↓
🎉 Todo configurado
```

**Solución Manual:**
```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

# Con datos de ejemplo (recomendado)
& $php artisan migrate:fresh --seed

# Sin datos (base vacía)
& $php artisan migrate
```

---

## 📚 Jerarquía de Documentación

```
📖 Inicio Rápido (este archivo)
    ↓
    ├─→ 📄 README.md
    │   └─ Instrucciones básicas de arranque
    │
    ├─→ 📋 REFERENCIA_RAPIDA.md
    │   └─ Soluciones express (1-2 líneas)
    │
    ├─→ 📘 docs/SOLUCION_POS_LOCAL.md
    │   └─ Troubleshooting completo (técnico)
    │
    └─→ 📚 docs/ARRANQUE_LOCAL.md
        └─ Guía detallada de instalación
```

---

## 🎯 Decision Tree: ¿Qué documentación leer?

```
                    Inicio
                      ↓
              ¿Primera vez?
              ↙          ↘
            SÍ            NO
             ↓              ↓
    Esta guía visual    ¿Problema específico?
             ↓            ↙          ↘
    Ejecutar script    SÍ            NO
             ↓           ↓             ↓
        ¿Funciona?  REFERENCIA     README
          ↙    ↘        RAPIDA        ↓
        SÍ     NO         ↓       Continuar
         ↓      ↓    ¿Solucionó?
      ¡Listo! SOLUCION  ↙    ↘
               POS    SÍ     NO
              LOCAL    ↓      ↓
                    ¡Listo! Contactar
                            soporte
```

---

## 🎪 Usuarios de Demostración

| 👤 Usuario | 🔑 Contraseña | 👔 Rol | 🎭 Permisos | 💡 Usar para |
|-----------|--------------|-------|-------------|--------------|
| `admin` | `admin123` | Administrador | ✅ TODO | Pruebas generales, configuración |
| `cajero` | `cajero123` | Cajero | ⚠️ Solo POS | Probar flujo de cajero |

---

## 📞 Contacto y Ayuda

### ✅ Si el script funcionó
→ ¡Empezar a usar el POS!

### ⚠️ Si hay un error específico
→ Ver `REFERENCIA_RAPIDA.md`

### 🔍 Para problemas técnicos
→ Ver `docs/SOLUCION_POS_LOCAL.md`

### 🆘 Si nada funciona
→ Verificar logs: `storage/logs/laravel.log`

---

## 🎬 Video Tutorial (Conceptual)

```
[1. Descargar Proyecto] → 00:00-00:30
    ↓
[2. Abrir PowerShell] → 00:31-00:45
    ↓
[3. Ejecutar Script] → 00:46-01:30
    ↓
[4. Ver Verificaciones] → 01:31-02:00
    ↓
[5. Navegador se Abre] → 02:01-02:15
    ↓
[6. Login y POS] → 02:16-03:00
    ↓
[7. Primera Venta] → 03:01-04:00
```

---

**🎉 ¡Listo para vender!**

```
   _______
  /       \
 |  VentaPOS |
 |    💰    |
  \_______/
```

---

**Última actualización:** 2026-10-10  
**Versión:** 1.0
