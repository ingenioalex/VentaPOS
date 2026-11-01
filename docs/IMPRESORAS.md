# Impresoras térmicas — configuración y recomendaciones

Guía para el módulo **Config. impresora** en VentaPOS y para elegir hardware en Perú (pruebas y venta a clientes).

## Cómo funciona hoy en VentaPOS

| Capa | Descripción |
|------|-------------|
| Configuración empresa | `empresa.configuracion.impresora` — defaults del negocio |
| Configuración usuario | `usuarios.preferencias` — cada cajero puede usar la de empresa o la suya |
| Ticket | Vista HTML + `window.print()` del navegador |
| Ancho | 58 mm o 80 mm (ajusta CSS del ticket) |

Ruta: `/app/impresora` (módulo `impresora`).

Cada usuario puede marcar **«Usar la configuración por defecto de la empresa»** o definir la suya (útil si hay dos cajas con impresoras distintas).

**Administrador / supervisor** (`puedeGestionar`): en `/app/impresora` ve la lista de **todos los usuarios activos de la empresa** y puede guardar la impresora de cada uno (checkbox default + campos propios).

**Dueño**: además edita el **default de la empresa** que heredan quienes marquen el checkbox.

## Instalación en Windows (caja)

1. Conectar impresora térmica por **USB** (o Ethernet en modelos pro).
2. Instalar driver del fabricante.
3. En Windows: **Configuración → Impresoras → Establecer como predeterminada**.
4. En Chrome/Edge: al imprimir, elegir esa impresora y **guardar como predeterminada** en el diálogo.
5. En VentaPOS → **Impresora** → **Ticket de prueba** → verificar ancho y corte.

## Instalación Bluetooth (tablet / móvil / PC)

1. Encienda la impresora y active el modo emparejamiento (consulte manual del modelo).
2. En el equipo: **Ajustes → Bluetooth → Emparejar** (Windows, Android o iOS).
3. En VentaPOS → **Impresora** → método **Bluetooth** → guarde y use **Ticket de prueba**.
4. En el diálogo de impresión del navegador, seleccione la impresora emparejada.
5. Recomendado: **Epson TM-m30II**, **Xprinter XP-P810** o modelos POS con perfil Bluetooth estándar.

> Hoy VentaPOS imprime vía navegador; Bluetooth funciona cuando el sistema operativo ya expone la impresora emparejada al diálogo de impresión.

## Qué impresora comprar

### Para pruebas (bajo costo)

| Modelo | Ancho | Precio aprox. Perú | Notas |
|--------|-------|-------------------|--------|
| **Xprinter XP-80** | 80 mm | S/ 200–350 | Buena para empezar; USB; repuestos accesibles |
| **Xprinter XP-58IIH** | 58 mm | S/ 150–250 | Ticket más angosto; ideal minimarket pequeño |
| **Gainscha GP-58** | 58 mm | S/ 120–200 | Económica; calidad media |

Recomendación pruebas: **Xprinter XP-80** (80 mm, estándar en bodegas).

### Para vender a clientes (confiable)

| Modelo | Ancho | Precio aprox. | Por qué |
|--------|-------|---------------|---------|
| **Epson TM-T20III** | 80 mm | S/ 900–1,200 | Estándar retail; duradera; soporte y repuestos |
| **Epson TM-m30II** | 80 mm | S/ 1,100–1,400 | Bluetooth + USB; buena si usan tablet |
| **Star TSP143III** | 80 mm | S/ 850–1,100 | Alternativa a Epson; drivers estables |
| **Bixolon SRP-350III** | 80 mm | S/ 700–950 | Buena relación precio/calidad |

Recomendación venta: **Epson TM-T20III** (USB o Ethernet). Inclúyela en el paquete «Arranque» con instalación.

### Evitar para negocio serio

- Impresoras de **inyección** o láser A4 (no son ticket).
- Modelos sin driver Windows oficial.
- «POS printer» genérica sin marca en marketplace (sin repuestos).

## Negocio: vender impresora + VentaPOS

| Paquete sugerido | Contenido | Margen orientativo |
|------------------|-----------|-------------------|
| Solo software | VentaPOS + capacitación | — |
| Kit bodega | TM-T20III + USB + rollo térmico + VentaPOS | S/ 150–250 sobre costo hardware |
| Kit económico | Xprinter XP-80 + VentaPOS | S/ 80–120 sobre costo |

Insumos recurrentes: rollos térmicos 80×80 mm (caja de 50 rollos ~S/ 80–120).

## Roadmap técnico (futuro)

| Fase | Mejora |
|------|--------|
| Actual | Navegador + impresora predeterminada Windows |
| Siguiente | QZ Tray o agente local para impresión silenciosa sin diálogo |
| Avanzado | ESC/POS directo (corte de papel, cajón) |

## Archivos en código

| Archivo | Rol |
|---------|-----|
| `config/impresora.php` | Defaults y tamaños CSS |
| `app/Services/ImpresoraService.php` | Merge empresa + usuario |
| `app/Http/Controllers/ImpresoraController.php` | Pantalla y prueba |
| `resources/views/app/pos/ticket.blade.php` | Ticket de venta |

Ver también: [MODULOS_Y_FASES.md](MODULOS_Y_FASES.md), [CASOS_DE_USO.md](CASOS_DE_USO.md).
