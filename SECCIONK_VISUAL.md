# 🎓 Módulo Sección K - Resumen Visual

```
┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│            MÓDULO SECCIÓN K - PROMOCIÓN 2001                    │
│         Subdominio: seccionk.ventasagil.com                     │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## 📊 Arquitectura del Sistema

```
┌──────────────────────────────────────────────────────────────────┐
│                        VentaPOS Principal                         │
│                     ventasagil.com                                │
│                                                                   │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ Base de Datos VentaPOS                                     │  │
│  │ - empresas, usuarios, productos, ventas, etc.              │  │
│  └────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────┘

                              ↕ INDEPENDIENTE ↕

┌──────────────────────────────────────────────────────────────────┐
│                    Módulo Sección K (NUEVO)                       │
│                 seccionk.ventasagil.com                           │
│                                                                   │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ Base de Datos SeccionK (Separada)                          │  │
│  │ - seccionk_promociones                                     │  │
│  │ - seccionk_eventos                                         │  │
│  │ - seccionk_participaciones                                 │  │
│  │ - seccionk_usuarios                                        │  │
│  └────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────┘
```

## 🌐 Estructura de Navegación

```
seccionk.ventasagil.com
│
├─ 🏠 PORTAL PÚBLICO
│  ├─ / (Consulta)
│  │  ├─ Búsqueda por DNI + Apellido
│  │  └─ Diseño Hero con gradiente purple
│  │
│  ├─ /perfil/{id}
│  │  ├─ 📊 Estadísticas (4 cards)
│  │  │  ├─ Cuotas pagadas: 5/15
│  │  │  ├─ Total aportado: S/ 250.00
│  │  │  ├─ Eventos: 12
│  │  │  └─ Participaciones: 8
│  │  │
│  │  ├─ 📅 Timeline Cuotas Mensuales
│  │  │  ├─ Oct 2026 ✅ S/ 50.00 (Pagado)
│  │  │  ├─ Nov 2026 ✅ S/ 50.00 (Pagado)
│  │  │  ├─ Dic 2026 ⏱️ Pendiente
│  │  │  ├─ ... (hasta Dic 2027)
│  │  │  └─ Total: 15 meses
│  │  │
│  │  └─ 🎉 Eventos
│  │     ├─ Partido de Fútbol ✅ Participó
│  │     ├─ Reunión Anual ❌ No participó
│  │     └─ ...
│  │
│  └─ /registro
│     └─ Formulario completo de registro
│
└─ 🔐 PANEL ADMIN
   ├─ /admin/login
   │  └─ Usuario: admin / seccionk2026
   │
   ├─ /admin (Dashboard)
   │  ├─ 📊 4 Métricas
   │  ├─ 📋 Eventos recientes
   │  └─ 🚀 Accesos rápidos
   │
   ├─ /admin/promociones
   │  ├─ Lista con filtros
   │  ├─ Crear nuevo
   │  ├─ Editar
   │  └─ Ocultar
   │
   └─ /admin/eventos
      ├─ Lista con filtros
      ├─ Crear nuevo
      ├─ Editar
      └─ /eventos/{id}/participantes
         ├─ Seleccionar miembros
         ├─ Marcar participación
         ├─ Registrar pagos
         ├─ Subir imagen sustento
         └─ Guardar
```

## 🗂️ Base de Datos - Diagrama ER

```
┌────────────────────────────┐
│  seccionk_promociones      │
├────────────────────────────┤
│ PK  id                     │
│ UQ  dni (8 dígitos)        │
│     nombre                 │
│ IDX apellido_paterno       │
│     apellido_materno       │
│     direccion (nullable)   │
│     celular (nullable)     │
│     participa_cuotas       │
│     referencias            │
│ IDX activo                 │
└────────────────────────────┘
          │
          │ hasMany
          │
          ↓
┌────────────────────────────┐
│ seccionk_participaciones   │
├────────────────────────────┤
│ PK  id                     │
│ FK  promocion_id           │◄────┐
│ FK  evento_id              │     │
│ IDX participo              │     │
│ IDX pago                   │     │
│     monto_aportado         │     │
│     numero_transaccion     │     │
│     imagen_sustento        │     │
│ IDX fecha_pago             │     │
│     observaciones          │     │
└────────────────────────────┘     │
          ↑                        │
          │ belongsTo              │
          │                        │
┌────────────────────────────┐     │
│  seccionk_eventos          │     │
├────────────────────────────┤     │
│ PK  id                     │─────┘
│     nombre                 │
│     descripcion            │
│ IDX fecha                  │
│     monto                  │
│ IDX tipo (enum)            │
│     ├─ evento              │
│     └─ cuota_mensual       │
│     mes_cuota              │
│ IDX activo                 │
└────────────────────────────┘

┌────────────────────────────┐
│  seccionk_usuarios         │
├────────────────────────────┤
│ PK  id                     │
│ UQ  usuario                │
│     password (hash)        │
│     nombre                 │
│     activo                 │
└────────────────────────────┘
```

## 🎨 Diseño Visual - Paleta de Colores

```
┌─────────────────────────────────────────────────────────────┐
│  GRADIENTE PRINCIPAL                                         │
│  ═══════════════════════════════════════════════════════    │
│  Purple #667eea ────────────► Indigo #764ba2               │
│                                                              │
│  Usado en: Navbar, botones principales, timeline            │
└─────────────────────────────────────────────────────────────┘

┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│   PAGADO     │  │  PENDIENTE   │  │   INACTIVO   │
│              │  │              │  │              │
│ ✅ Verde     │  │ ⏱️ Amarillo   │  │ ⚫ Gris       │
│ #10b981      │  │ #f59e0b      │  │ #6b7280      │
└──────────────┘  └──────────────┘  └──────────────┘
```

## 📱 Vistas Principales

### 1. Página de Consulta (/)
```
┌─────────────────────────────────────────────────┐
│  [NAVBAR: Sección K - Promoción 2001]          │
│  [Inicio] [Registrarse]                        │
└─────────────────────────────────────────────────┘

         ┌──────────────┐
         │   👥  Logo   │
         └──────────────┘
         
    Sección K - Promoción 2001
    Consulta tu participación y aportes
    
┌─────────────────────────────────────────────────┐
│  Buscar Mi Perfil                               │
│                                                 │
│  🆔 Número de DNI                               │
│  [12345678_____]                                │
│                                                 │
│  👤 Primer Apellido                             │
│  [García_______]                                │
│                                                 │
│  [🔍 Buscar Mi Información]                    │
│                                                 │
│  ¿Aún no estás registrado?                     │
│  [Regístrate aquí]                              │
└─────────────────────────────────────────────────┘

[📅 Seguimiento]  [💰 Cuotas Mensuales]
```

### 2. Perfil - Timeline (perfil/{id})
```
┌─────────────────────────────────────────────────┐
│  JG  Juan Carlos García López                   │
│  🆔 DNI: 12345678                               │
│  📱 987654321                                   │
│                    [✅ Participa en Cuotas]     │
└─────────────────────────────────────────────────┘

┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐
│ 5/15 │ │S/ 250│ │  12  │ │  8   │
│Cuotas│ │Total │ │Event.│ │Partic│
└──────┘ └──────┘ └──────┘ └──────┘

LÍNEA DE TIEMPO - CUOTAS MENSUALES
───────────────────────────────────
┌─● Octubre 2026          ✅ Pagado
│  💵 S/ 50.00 | 📅 15/10/2026
│
├─● Noviembre 2026        ✅ Pagado
│  💵 S/ 50.00 | 📅 17/11/2026
│
├─○ Diciembre 2026        ⏱️ Pendiente
│  Monto sugerido: S/ 50.00
│
├─○ Enero 2027            ⏱️ Pendiente
│  ...
│
└─○ Diciembre 2027        ⏱️ Pendiente
```

### 3. Panel Admin - Dashboard
```
┌─────────────┐ ┌────────────────────────────────┐
│ SIDEBAR     │ │  Dashboard                     │
│ ───────────│ │  ──────────────────────────── │
│ 📊 Dashboard│ │  ┌────┐ ┌────┐ ┌────┐ ┌────┐ │
│ 👥 Promocion│ │  │ 25 │ │ 20 │ │ 16 │ │ 15 │ │
│ 📅 Eventos  │ │  │Prom│ │Cuot│ │Evnt│ │Cuot│ │
│             │ │  └────┘ └────┘ └────┘ └────┘ │
│ ───────────│ │                                │
│ 🌐 Ver sitio│ │  Eventos Recientes             │
│ 🚪 Cerrar   │ │  ├─ Cuota Oct 2026            │
└─────────────┘ │  ├─ Partido Fútbol            │
                │  └─ Cuota Nov 2026            │
                │                                │
                │  Accesos Rápidos               │
                │  [➕ Nueva Promoción]          │
                │  [➕ Nuevo Evento]             │
                │  [📋 Listar Promociones]      │
                └────────────────────────────────┘
```

## 🔄 Flujo de Datos - Caso de Uso

### Usuario Consulta Sus Pagos
```
Usuario
   ↓
Ingresa DNI + Apellido
   ↓
seccionk.ventasagil.com
   ↓
SeccionKPublicoController::buscar()
   ↓
SELECT * FROM seccionk_promociones
WHERE dni = ? AND apellido_paterno LIKE ?
   ↓
¿Encontrado?
   ├─ SÍ → SeccionKPublicoController::perfil()
   │         ↓
   │      Carga: promoción + participaciones + eventos
   │         ↓
   │      Vista: seccionk.perfil
   │         ↓
   │      Renderiza Timeline + Eventos
   │         ↓
   │      Usuario ve su información
   │
   └─ NO → Mensaje de error
            "No se encontró..."
```

### Admin Registra Pago de Evento
```
Admin Login
   ↓
Eventos → Filtrar por tipo
   ↓
Selecciona "Partido de Fútbol"
   ↓
Click "Participantes"
   ↓
AdminEventoController::participantes()
   ↓
Carga: evento + promociones + participaciones existentes
   ↓
Admin ve formulario con checkboxes
   ↓
Admin marca:
├─ ☑️ Juan García - Participó
├─ ☑️ Pagó S/ 5.00
├─ 📄 Sube imagen de yape
└─ 📝 Número transacción: OP123456
   ↓
Click "Guardar"
   ↓
AdminEventoController::guardarParticipantes()
   ↓
DB::transaction {
  foreach participante {
    - Sube imagen a storage
    - UPDATE/INSERT seccionk_participaciones
  }
}
   ↓
Mensaje: "Participantes actualizados ✅"
```

## 🎯 Datos de Prueba

### Promociones Precargadas
```
┌──────────┬────────────────┬───────────┬────────────┐
│   DNI    │     Nombre     │  Celular  │  Participa │
├──────────┼────────────────┼───────────┼────────────┤
│ 12345678 │ Juan García    │ 987654321 │     ✅     │
│ 23456789 │ María Rodrígue │ 987654322 │     ✅     │
│ 34567890 │ Pedro Fernánde │ 987654323 │     ❌     │
└──────────┴────────────────┴───────────┴────────────┘
```

### Eventos Precargados
```
┌──────────────────────────┬────────────┬─────────┬─────────┐
│         Nombre           │   Fecha    │  Monto  │  Tipo   │
├──────────────────────────┼────────────┼─────────┼─────────┤
│ Cuota Oct 2026           │ 2026-10-01 │  50.00  │ Cuota   │
│ Cuota Nov 2026           │ 2026-11-01 │  50.00  │ Cuota   │
│ ... (hasta Dic 2027)     │    ...     │  50.00  │ Cuota   │
│ Partido de Fútbol        │ 2026-09-21 │   5.00  │ Evento  │
└──────────────────────────┴────────────┴─────────┴─────────┘
```

## 🚀 Deploy Checklist

```
□ Ejecutar migraciones:
  php artisan migrate
  
□ Ejecutar seeder:
  php artisan db:seed --class=SeccionKSeeder
  
□ Configurar subdominio DNS:
  Tipo: A
  Host: seccionk
  Valor: [IP servidor]
  
□ Configurar Nginx/Apache:
  ServerName: seccionk.ventasagil.com
  DocumentRoot: /var/www/ventasagil/public
  
□ Configurar storage:
  php artisan storage:link
  chmod -R 775 storage/app/public
  
□ Probar accesos:
  ✓ Portal público
  ✓ Login admin
  ✓ Dashboard
  ✓ CRUD promociones
  ✓ CRUD eventos
  ✓ Gestión participantes
```

## 📞 Información de Contacto

```
┌─────────────────────────────────────────────┐
│  Sección K - Promoción 2001                 │
│                                             │
│  📧 Email: seccionk2001@gmail.com          │
│  📱 WhatsApp: +51 987 654 321              │
│                                             │
│  🌐 Portal: seccionk.ventasagil.com        │
│  🔐 Admin: seccionk.ventasagil.com/admin   │
│                                             │
│  👤 Usuario Admin: admin                    │
│  🔑 Contraseña: seccionk2026               │
└─────────────────────────────────────────────┘
```

---

**Última actualización:** 2026-10-11  
**Versión:** 1.0  
**Estado:** ✅ Funcional y listo para producción
