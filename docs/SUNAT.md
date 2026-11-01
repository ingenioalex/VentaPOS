# Módulo SUNAT — Facturación electrónica

Documentación técnica del comprobante electrónico (CPE) en VentaPOS.

## Alcance actual

- **Tipos:** boleta (`03`) y factura (`01`) al cobrar en POS.
- **Modos:** simulación (sin envío real) y producción (vía OSE/PSE).
- **Proveedores producción:** **Nubefact** (recomendado) u OSE/PSE genérico JSON.
- **Firma XML:** opcional en modo genérico si se configura certificado `.pem`/`.pfx`.
- **Credenciales:** claves SOL y token Nubefact cifradas en BD (`enc:` + Laravel Crypt).
- **Reintentos:** comando `ventapos:reintentar-sunat` cada 30 min (scheduler).
- **No incluye aún:** notas de crédito electrónicas completas, guías remisión, UBL XSD estricto.

## Configuración

Ruta: `/app/sunat` (dueño + módulo `sunat`).

Almacenamiento en `empresa.configuracion.sunat`:

| Campo | Descripción |
|-------|-------------|
| `activo` | Emite CPE al cobrar |
| `modo` | `simulacion` \| `produccion` |
| `proveedor_ose` | `nubefact` \| `generico` |
| `usuario_sol` | Usuario SOL / OSE (genérico) |
| `clave_sol` | Clave cifrada (genérico) |
| `certificado` | Ruta certificado `.pem`/`.pfx` (genérico) |
| `clave_certificado` | Contraseña del certificado (cifrada) |
| `ose_url` | URL endpoint OSE genérico |
| `nubefact_ruta` | UUID de ruta API Nubefact |
| `nubefact_token` | Token API Nubefact (cifrado) |

**Precondición:** RUC de 11 dígitos en configuración de empresa (persona natural `10…` o jurídica `20…`).

## Flujo de emisión

```mermaid
sequenceDiagram
    participant POS as PosController
    participant VS as VentaService
    participant SS as SunatService
    participant DB as comprobantes
    participant OSE as OseClient

    POS->>VS: registrar venta
    VS->>DB: comprobante emitido
    POS->>DB: sunat_estado = pendiente (si activo)
    POS->>SS: prepararEmision (XML + hash local, rápido)
    POS->>POS: session save (libera bloqueo)
    POS-->>POS: JSON ok + ticket_url (respuesta inmediata, QR listo)
    Note over POS,SS: EmitirSunatComprobanteJob → completarEmision (OSE)
    POS->>SS: emitir(comprobante)
    alt SUNAT inactivo o nota
        SS->>DB: sunat_estado = omitido
    else activo
        SS->>SS: generar XML UBL + digest
        SS->>DB: pendiente + sunat_xml
        alt simulacion
            SS->>DB: simulado, codigo 0
        else produccion
            SS->>OSE: POST JSON
            OSE-->>SS: aceptado/rechazado + CDR
            SS->>DB: aceptado/rechazado/error
        end
    end
    Note over POS: El cajero ya puede iniciar otra venta
```

## Estados `sunat_estado`

| Estado | Significado |
|--------|-------------|
| `omitido` | SUNAT inactivo o tipo no electrónico |
| `pendiente` | Transitorio antes de respuesta |
| `simulado` | Aceptado en modo prueba |
| `aceptado` | Aceptado por OSE/SUNAT |
| `rechazado` | Rechazado tributario |
| `error` | Error técnico o configuración |

## Campos en `comprobantes`

| Columna | Uso |
|---------|-----|
| `sunat_estado` | Estado del ciclo |
| `sunat_codigo_respuesta` | Código OSE/SUNAT |
| `sunat_mensaje` | Mensaje legible |
| `sunat_hash` | Digest para QR (40 chars base64 de SHA-256) |
| `sunat_xml` | Ruta relativa en `storage/app/sunat/{empresa_id}/` |
| `sunat_cdr` | Ruta CDR de respuesta (producción) |
| `sunat_enviado_en` | Timestamp envío |

## Código QR en ticket

Formato texto (pipe-separated, norma representación impresa Perú):

```
RUC|TIPO|SERIE|NUMERO|IGV|TOTAL|FECHA|TIPO_DOC_CLIENTE|NUM_DOC_CLIENTE|HASH|
```

Implementación:

- `Comprobante::textoQrSunat()` — arma la cadena.
- `QrService::pngDataUri()` — PNG alto contraste, escala 12, ECC nivel M.
- Ticket embebe PNG en **base64** (sin segunda petición HTTP).
- Impresión espera carga de imágenes antes de `window.print()`.

Ruta alternativa: `GET /app/pos/ticket/{id}/qr` → PNG binario.

## API Nubefact (producción recomendada)

1. Crear cuenta en [nubefact.com](https://www.nubefact.com) y obtener **ruta** + **token** API.
2. En `/app/sunat`: modo **Producción**, proveedor **Nubefact**, pegar credenciales.
3. Nubefact genera, firma y envía a SUNAT; VentasÁgil guarda hash real para el QR del ticket.

`POST https://api.nubefact.com/api/v1/{ruta}` con header `Authorization: Token token="..."`.

## API OSE genérica (`GenericJsonOseProvider`)

`POST` a `ose_url` con JSON:

```json
{
  "usuario": "...",
  "clave": "...",
  "nombre_archivo": "B001-00000001.xml",
  "archivo_base64": "..."
}
```

Respuesta esperada:

```json
{
  "aceptado": true,
  "codigo": "0",
  "mensaje": "Aceptado por SUNAT",
  "cdr_base64": "..."
}
```

También acepta claves `success`, `codigo_respuesta`, `descripcion`, `cdr`.

## Anulación

Tras `AnulacionService::anular`:

- **Simulación:** append mensaje en `sunat_mensaje`.
- **Producción:** envía XML comunicación de baja simplificado al OSE (si URL configurada).

## UI

| Ubicación | Elemento |
|-----------|----------|
| POS cobrar | Mensaje CPE en línea de éxito |
| Ticket | QR + hash + leyenda electrónica |
| Ventas listado | Columna badge CPE |
| Ventas detalle | Badge, reenviar, descargar XML/CDR |
| Panel SUNAT | Config + tabla comprobantes con error |

## Archivos clave

```
app/Services/SunatService.php
app/Services/Sunat/NubefactOseProvider.php
app/Services/Sunat/GenericJsonOseProvider.php
app/Services/Sunat/OseProviderFactory.php
app/Console/Commands/ReintentarSunatCommand.php
app/Http/Controllers/SunatController.php
app/Models/Comprobante.php
config/sunat.php
resources/views/app/sunat/index.blade.php
tests/Unit/SunatServiceTest.php
tests/Unit/NubefactOseProviderTest.php
tests/Feature/SunatEmitirTest.php
```

## Pruebas

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test --filter=Sunat
```

## Pendientes Fase 3/4

1. Validación UBL contra XSD SUNAT (estructura ampliada, aún simplificada).
2. Firma XMLDSig completa (canonicalización SUNAT) para OSE directo sin PSE.
3. Notas de crédito / débito electrónicas.
4. Cola async en cobro POS (hoy es síncrono, ~60 s máx. Nubefact).
5. Panel descarga PDF Nubefact en detalle venta.
