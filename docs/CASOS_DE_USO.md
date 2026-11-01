# Casos de uso

Actores y casos de uso principales de VentaPOS. Cada caso indica módulo y rol mínimo.

## Actores

| Actor | Descripción | Acceso |
|-------|-------------|--------|
| **Super Admin** | Operador de la plataforma SaaS | `/admin` |
| **Dueño** | Administrador de la empresa cliente | Panel `/app`, todos los módulos contratados |
| **Supervisor** | Supervisión operativa, anulaciones | POS, ventas, cierres, reportes (según plan) |
| **Cajero** | Venta en mostrador | POS, POS móvil |
| **Digitador** | Carga de catálogo e inventario | Productos, inventario, compras |
| **Cliente final** | Comprador en bodega | Solo recibe ticket (sin login) |
| **Visitante (landing)** | Prospecto sin login | `/`, `/precios`, `/contacto`, `/preguntas-frecuentes` |

---

## CU-IMP-01 Configuración de impresora

| Campo | Valor |
|-------|-------|
| Actor | Dueño (defaults empresa + por usuario), Supervisor (por usuario), Cajero (solo la suya si no gestiona) |
| Módulo | `impresora` |
| Flujo | 1. Elegir ancho 58/80 mm → 2. Auto-imprimir, logo, notas → 3. Ticket de prueba |
| Postcondición | Ticket de venta usa CSS según config efectiva del usuario |
| Implementación | `ImpresoraService`, `ImpresoraController`, `config/impresora.php` |

Ver [IMPRESORAS.md](IMPRESORAS.md) para hardware recomendado.

---

## CU-LND-01 Chatbox de ayuda (planes y módulos)

| Campo | Valor |
|-------|-------|
| Actor | Visitante (landing) |
| Precondición | Acceso web a la landing |
| Flujo | 1. Abrir chat → 2. Preguntar por planes/módulos/SUNAT → 3. Respuesta inmediata (sin IA externa) |
| Postcondición | Prospecto entiende plan sugerido y módulos típicos; puede ir a WhatsApp para cierre |
| Implementación | `ChatbotController::ask` + widget en `layouts/guest.blade.php` |

---

## CU-01 Autenticación multi-empresa

| Campo | Valor |
|-------|-------|
| Actor | Dueño, Supervisor, Cajero, Digitador |
| Precondición | Empresa activa, usuario activo |
| Flujo | 1. Ingresar código empresa → 2. Usuario y contraseña → 3. Redirección según rol/módulos |
| Cajero — turno abierto | Si el cajero ya tiene turno activo (`en_sesion`), la pantalla muestra **modo bloqueo**: ingresa su contraseña para **desbloquear** y continuar (misma pestaña, navegador cerrado o inactividad). No requiere que el supervisor cierre la sesión. El turno y las ventas del período se mantienen. |
| Postcondición | Sesión con `empresa_id` en contexto |
| Implementación | `AuthController`, middleware `empresa` |

---

## CU-02 Venta en POS (boleta / factura)

| Campo | Valor |
|-------|-------|
| Actor | Cajero, Supervisor, Dueño |
| Módulo | `pos` |
| Precondición | Terminal asignada (si aplica), productos con stock |
| Flujo principal | 1. Buscar/escanear productos → 2. Carrito → 3. Medio de pago → 4. Cobrar (F9) → 5. Ticket + estado SUNAT |
| Flujo alterno | Factura requiere cliente con RUC |
| Postcondición | `comprobantes` emitido, stock descontado, CPE si SUNAT activo |
| Implementación | `PosController::cobrar`, `VentaService`, `SunatService` |

**Detalle venta:** panel derecho ampliado (~460px), montos alineados a la derecha, código de barra bajo el nombre del producto, columna **UM** (unidad de medida), precio unitario (P.U.). Productos con UM **KG** permiten cantidad decimal; el resto en unidades enteras. Al agregar productos solo se actualiza la línea afectada (mejor rendimiento).

**Top 5 productos:** ranking de los últimos 90 días con caché de 15 min; se precarga al abrir el POS y las peticiones por categoría no bloquean la venta (caché cliente + cancelación de solicitudes anteriores).

**Pago mixto:** Yape/Plin/Tarjeta no pueden superar el total (sin vuelto). Si la suma supera el total y hay efectivo, el excedente es vuelto (ej. total 93.21, pagos 100 → vuelto 6.79).

**Rendimiento al cobrar:** la venta se confirma al instante; la emisión SUNAT (XML, firma, OSE) corre en cola (`EmitirSunatComprobanteJob` con `ShouldQueue`). El controlador libera la sesión antes de responder. En producción debe estar activo `ventapos-queue` (ver [PUESTA_EN_MARCHA.md](PUESTA_EN_MARCHA.md)). El **QR** se genera al cobrar (`asegurarHashQr`) para **boleta y factura** con cualquier medio de pago; si falta, el ticket lo repara al abrirse.

---

## CU-03 Suspender y recuperar venta

| Campo | Valor |
|-------|-------|
| Actor | Cajero |
| Módulo | `pos` |
| Flujo | F10 suspender → código HOLD 13 dígitos → recuperar desde modal |
| Implementación | `VentaSuspendidaService` |

---

## CU-04 Anular venta

| Campo | Valor |
|-------|-------|
| Actor | Supervisor o Dueño (credenciales) |
| Módulo | `ventas` |
| Precondición | Comprobante emitido |
| Flujo | Detalle venta → usuario/contraseña supervisor → motivo → confirmar |
| Postcondición | Estado anulado, stock restaurado, anulación SUNAT si aplica |
| Implementación | `AnulacionService` |

**Consultar ventas (`/app/ventas/{id}`):** solo comprobantes de la empresa del usuario autenticado. Cambiar el ID en la URL no expone ventas de otras empresas (404). Aplica a detalle, ticket, XML/CDR SUNAT y anulación.

---

## CU-05 Cierre de caja

| Campo | Valor |
|-------|-------|
| Actor | Cajero (X), Supervisor/Dueño (Z) |
| Módulo | `cierres` |
| Flujo | Cierre X: ventas del turno del cajero (desde último X o inicio de jornada). Cierre Z: suma de todos los X de la jornada; debe coincidir con las ventas del período. Varios X por cajero en el día son válidos si cada uno cubre solo su turno. **Antes del Z**, el supervisor puede **rectificar el efectivo contado** de un X si hubo error al digitar (queda en observaciones del X). |
| Implementación | `CierreCajaService`, `CierreCajaController` |

**Jornadas sin cerrar:** cada **Cierre Z cubre un solo día calendario** (00:00–23:59) y no puede solaparse con otro Z del mismo día. El **Z de hoy** se habilita cuando hoy no tiene Z y los cajeros registraron su X; no depende de días viejos pendientes. Si olvidó cerrar días anteriores, use **«Cerrar este día»** en orden (el más antiguo primero); ese botón registra X (efectivo del sistema) + Z automáticamente.

---

## CU-06 Gestión de productos

| Campo | Valor |
|-------|-------|
| Actor | Dueño, Digitador |
| Módulo | `productos`, `categorias` |
| Flujos | CRUD producto, importación CSV, alertas stock bajo |
| Implementación | `ProductoController`, `ProductoImportService` |

---

## CU-07 Inventario

| Campo | Valor |
|-------|-------|
| Actor | Supervisor, Digitador, Dueño |
| Módulo | `inventario` |
| Flujos | Entrada, salida, ajuste, kardex por producto |
| Implementación | `InventarioService`, `InventarioController` |

---

## CU-08 Compras a proveedor

| Campo | Valor |
|-------|-------|
| Actor | Digitador, Supervisor, Dueño |
| Módulo | `proveedores`, `compras` |
| Flujo | 1. Registrar proveedor → 2. Nueva compra (búsqueda productos) → 3. Recibir stock |
| Postcondición | Stock y costo actualizados, movimiento inventario |
| Implementación | `CompraService`, `CompraController` |

---

## CU-09 Reportes

| Campo | Valor |
|-------|-------|
| Actor | Supervisor, Dueño |
| Módulo | `reportes` |
| Flujos | Ventas por período, inventario valorizado, compras por proveedor; export CSV/Excel |
| Implementación | `ReporteService`, `ReporteController` |

---

## CU-10 Facturación electrónica SUNAT

| Campo | Valor |
|-------|-------|
| Actor | Dueño (config), Cajero (emisión automática al cobrar) |
| Módulo | `sunat` |
| Flujo configuración | RUC en empresa → activar SUNAT → modo simulación o producción |
| Flujo emisión | Al cobrar boleta/factura → XML UBL → estado CPE → QR en ticket |
| Flujo error | Ventas detalle → reenviar; panel SUNAT lista pendientes |
| Detalle técnico | [SUNAT.md](SUNAT.md) |

---

## CU-11 Onboarding nueva empresa

| Campo | Valor |
|-------|-------|
| Actor | Dueño (primera vez) |
| Flujo | Datos empresa → terminal → productos iniciales → cajero opcional |
| Implementación | `OnboardingController`, `OnboardingService` |

---

## CU-12 Administración SaaS (Super Admin)

| Campo | Valor |
|-------|-------|
| Actor | Super Admin |
| Ruta | `/admin` |
| Flujos | Crear/editar empresas, usuarios por empresa, planes y módulos |
| Implementación | `AdminEmpresaController`, `AdminEmpresaUsuarioController` |

---

## Matriz actor × módulo (resumen)

| Módulo | Dueño | Supervisor | Cajero | Digitador |
|--------|-------|------------|--------|-----------|
| POS | ✓ | ✓ | ✓ | — |
| Ventas / anular | ✓ | ✓ | — | — |
| Productos | ✓ | — | — | ✓ |
| Inventario | ✓ | ✓ | — | ✓ |
| Compras | ✓ | ✓ | — | ✓ |
| SUNAT config | ✓ | — | — | — |
| Reportes | ✓ | ✓ | — | — |
| Usuarios | ✓ | — | — | — |

*Sujeto a módulos contratados en el plan de la empresa.*
