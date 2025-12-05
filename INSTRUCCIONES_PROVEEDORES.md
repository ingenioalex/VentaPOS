# 🏢 Módulo de Gestión de Proveedores

## 📋 Descripción
El módulo de **Gestión de Proveedores** permite administrar toda la información de los proveedores del sistema, incluyendo datos comerciales, bancarios, certificaciones y estadísticas.

## 🗄️ Instalación de la Base de Datos

### 1. Ejecutar el Script SQL
Para habilitar completamente el módulo de proveedores, debes ejecutar el archivo `sql_proveedores.sql` en tu base de datos PostgreSQL.

**Opción A: Desde la terminal**
```bash
psql -U rms_user -d rms_db -f sql_proveedores.sql
```

**Opción B: Desde pgAdmin o DBeaver**
1. Abre pgAdmin o DBeaver
2. Conecta a la base de datos `rms_db`
3. Abre el archivo `sql_proveedores.sql`
4. Ejecuta el script completo

### 2. Verificar la Instalación
Verifica que las tablas se crearon correctamente:
```sql
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'public' 
AND table_name LIKE '%proveedor%' 
OR table_name LIKE '%orden%';
```

Deberías ver las siguientes tablas:
- ✅ `proveedores`
- ✅ `proveedor_productos`
- ✅ `ordenes_compra`
- ✅ `orden_compra_detalle`
- ✅ `historial_compras_proveedor`

## 📊 Estructura de las Tablas

### Tabla: `proveedores`
Tabla principal que almacena la información de los proveedores.

**Campos principales:**
- **Información Básica**: razón_social, nombre_comercial, ruc, dirección
- **Contacto**: teléfono, celular, email, web
- **Persona de Contacto**: nombre, cargo, teléfono, email
- **Información Comercial**: tipo_proveedor, categoría, condición_pago, días_crédito
- **Información Bancaria**: banco, cuenta_bancaria, tipo_cuenta, cci
- **Certificaciones**: licencia, certificado_calidad, certificado_sanitario
- **Control**: estado (activo/inactivo/bloqueado), calificación (1-5)
- **Estadísticas**: total_compras, total_deuda, ultima_compra

### Tabla: `proveedor_productos`
Relación muchos a muchos entre proveedores y productos.

**Campos principales:**
- proveedor_id, producto_id
- precio_compra, precio_negociado, descuento_producto
- tiempo_entrega_dias, cantidad_minima
- es_principal (indica si es el proveedor principal)

### Tabla: `ordenes_compra`
Órdenes de compra realizadas a proveedores (opcional, para futuro desarrollo).

**Campos principales:**
- proveedor_id, usuario_id
- numero_orden, tipo_orden, fecha_orden
- subtotal, igv, descuento, total
- estado (pendiente/aprobada/recibida/cancelada)

### Tabla: `orden_compra_detalle`
Detalle de productos en cada orden de compra.

### Tabla: `historial_compras_proveedor`
Historial de compras realizadas a cada proveedor.

## 🚀 Funcionalidades del Módulo

### 1. CRUD de Proveedores
- ✅ **Crear** nuevos proveedores con información completa
- ✅ **Editar** datos de proveedores existentes
- ✅ **Eliminar** (desactivar) proveedores
- ✅ **Buscar** por razón social, nombre comercial o RUC
- ✅ **Filtrar** por estado (activo/inactivo/bloqueado)

### 2. Gestión de Información
- **Información Básica**: Razón social, RUC, dirección
- **Contacto**: Múltiples canales de comunicación
- **Persona de Contacto**: Responsable directo
- **Información Comercial**: Tipo, categoría, condiciones de pago
- **Información Bancaria**: Datos para pagos
- **Certificaciones**: Licencias y certificados

### 3. Estadísticas en Tiempo Real
- 📊 Total de proveedores
- ✅ Proveedores activos/inactivos
- 💰 Total de compras acumuladas
- 📈 Total de deuda
- ⭐ Calificación promedio

### 4. Sistema de Calificación
- Calificación de 1 a 5 estrellas (⭐)
- Ayuda a identificar los mejores proveedores

### 5. Control de Estado
- **Activo**: Proveedor operativo
- **Inactivo**: Proveedor deshabilitado temporalmente
- **Bloqueado**: Proveedor con problemas o sanciones

## 🔐 Permisos de Acceso

El módulo de proveedores está disponible solo para:
- ✅ **Administrador** (admin)
- ✅ **Jefe** (jefe)

Los demás roles (cajero, supervisor, barras) NO tienen acceso a este módulo.

## 💡 Uso del Módulo

### Acceso al Módulo
1. Inicia sesión como **admin** o **jefe**
2. En el menú principal, haz clic en el botón **🏢 Proveedores**
3. Se abrirá la ventana de gestión de proveedores

### Crear un Nuevo Proveedor
1. Haz clic en **➕ Nuevo Proveedor**
2. Completa los campos obligatorios:
   - Razón Social (*)
   - RUC (*) - Debe tener 11 dígitos
3. Completa los campos opcionales según sea necesario
4. Haz clic en **💾 Guardar**

### Editar un Proveedor
1. Selecciona un proveedor de la tabla
2. Haz clic en **✏️ Editar** o doble clic en la fila
3. Modifica los campos necesarios
4. Haz clic en **💾 Guardar**

### Ver Detalles
1. Selecciona un proveedor de la tabla
2. Haz clic en **👁️ Ver Detalles**
3. Se mostrará un resumen completo del proveedor

### Desactivar un Proveedor
1. Selecciona un proveedor de la tabla
2. Haz clic en **🗑️ Eliminar**
3. Confirma la acción
4. El proveedor se marcará como **inactivo**

### Buscar Proveedores
1. Escribe en el campo de búsqueda
2. Puedes buscar por:
   - Razón Social
   - Nombre Comercial
   - RUC
3. Los resultados se filtran automáticamente

### Filtrar por Estado
1. Usa el combo **Estado**
2. Selecciona: Todos, Activo, Inactivo o Bloqueado
3. La tabla se actualizará automáticamente

## 📝 Validaciones Implementadas

1. **Razón Social**: Obligatoria
2. **RUC**: 
   - Obligatorio
   - Debe tener exactamente 11 dígitos
   - Debe contener solo números
   - Debe ser único en el sistema
3. **Email**: Formato válido (si se proporciona)
4. **Calificación**: Entre 1 y 5 estrellas
5. **Estado**: Solo valores permitidos (activo/inactivo/bloqueado)

## 🎨 Características de la Interfaz

- **Colores indicadores en la tabla**:
  - 🟢 Verde: Proveedor activo
  - 🔴 Rojo: Proveedor inactivo
  - 🟡 Amarillo: Proveedor bloqueado

- **Calificación visual**: 
  - ⭐⭐⭐⭐⭐ (5 estrellas)
  - Visible tanto en tabla como en detalles

- **Búsqueda en tiempo real**: 
  - Los resultados se actualizan mientras escribes

- **Diseño responsivo**: 
  - Columnas ajustables automáticamente

## 🔮 Futuras Funcionalidades

Las tablas adicionales (`ordenes_compra`, `proveedor_productos`, etc.) están preparadas para futuros módulos:

1. **Módulo de Órdenes de Compra**
   - Crear órdenes de compra a proveedores
   - Seguimiento de entregas
   - Control de recepción de productos

2. **Relación Proveedor-Productos**
   - Asignar productos a proveedores
   - Gestionar precios por proveedor
   - Definir tiempos de entrega

3. **Historial de Compras**
   - Registro de todas las compras
   - Control de pagos y deudas
   - Análisis de compras por proveedor

## 📞 Soporte

Si tienes problemas con el módulo de proveedores:

1. Verifica que el script SQL se haya ejecutado correctamente
2. Revisa los permisos de usuario (debe ser admin o jefe)
3. Consulta los logs de errores en la consola
4. Verifica la conexión a la base de datos

## 🎯 Ejemplo de Uso Completo

```python
# 1. Ejecutar SQL
psql -U rms_user -d rms_db -f sql_proveedores.sql

# 2. Iniciar el sistema
python main.py

# 3. Login como admin o jefe

# 4. Hacer clic en "🏢 Proveedores"

# 5. Crear proveedor:
#    - Razón Social: GLORIA S.A.
#    - Nombre Comercial: Gloria
#    - RUC: 20100190797
#    - Tipo: nacional
#    - Categoría: bebidas
#    - Calificación: 5 ⭐

# 6. Guardar y listo!
```

---

**Nota**: Este módulo NO modifica la base de datos automáticamente. Debes ejecutar el script SQL manualmente antes de usar el módulo.

