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

---

## CU-01 Autenticación multi-empresa

| Campo | Valor |
|-------|-------|
| Actor | Dueño, Supervisor, Cajero, Digitador |
| Precondición | Empresa activa, usuario activo |
| Flujo | 1. Ingresar código empresa → 2. Usuario y contraseña → 3. Redirección según rol/módulos |
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

---

## CU-05 Cierre de caja

| Campo | Valor |
|-------|-------|
| Actor | Cajero (X), Supervisor/Dueño (Z) |
| Módulo | `cierres` |
| Flujo | Arqueo efectivo → registro cierre → ticket cierre |
| Implementación | `CierreCajaService`, `CierreCajaController` |

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
