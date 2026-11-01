# Venta, prospectos y onboarding — VentasÁgil

Guía operativa para abordar leads, ofrecer planes y activar facturación electrónica SUNAT.

## Materiales listos para usar

| Recurso | Uso |
|---------|-----|
| **Admin → Guía de respuesta** (`/{admin_path}/guia`) | Panel super admin: plantillas con copiar + PDF |
| [plantillas/WHATSAPP_PROSPECTOS.md](plantillas/WHATSAPP_PROSPECTOS.md) | Misma fuente en Markdown (repo) |
| [plantillas/guia-prospecto-sunat.html](plantillas/guia-prospecto-sunat.html) | HTML estático en repo (backup) |

Plantillas editables en `config/guia_respuesta.php`.

## Embudo comercial

```
Landing / contacto → WhatsApp (calificar) → Demo 15–20 min → Cierre (arranque S/99)
    → Alta admin → Capacitación → Operación diaria
    → [Si Pro] Checklist SUNAT + Nubefact → Boleta prueba → Producción
```

## Planes (referencia)

| Plan | Arranque | Mensual | SUNAT |
|------|----------|---------|-------|
| Beta | Gratis | Gratis 1 mes | Simulación |
| Básico | S/99 | S/29 | No (add-on ~S/12/mes) |
| Negocio | S/99 | S/45 | No (add-on) |
| Pro | S/99 | S/59 | Incluido |

Detalle en `config/planes.php` y [MODELO_NEGOCIO.md](MODELO_NEGOCIO.md).

## Calificación del prospecto

Preguntar siempre: rubro, usuarios, herramienta actual, **¿tiene RUC?**, **¿boleta/factura?**

| Situación | Recomendación |
|-----------|---------------|
| Sin RUC | Beta o Básico; tramitar RUC en paralelo |
| RUC en trámite | Básico/Negocio; SUNAT en simulación hasta Nubefact |
| RUC + emisor electrónico + Nubefact | Plan Pro, producción |
| Solo control interno | Básico, sin módulo SUNAT |

## Qué debe hacer el cliente para SUNAT real

1. RUC activo (11 dígitos: persona natural `10…` o jurídica `20…`).
2. Habilitación como emisor electrónico en SUNAT (directo o vía PSE).
3. Series de boleta y/o factura vigentes.
4. Cuenta [Nubefact](https://www.nubefact.com) con su RUC.
5. Entregar **ruta** y **token** API a quien configura VentasÁgil.
6. Datos fiscales correctos en configuración de empresa en la app.
7. En `/app/sunat`: activo, modo **producción**, proveedor **Nubefact**.

Configuración técnica: [SUNAT.md](SUNAT.md).  
Alta de módulos: Admin → Contratación (`sunat` en plan Pro o add-on).

## Sin RUC

- **Puede:** POS, productos, inventario, cierres, reportes, simulación SUNAT (prueba).
- **No puede:** boleta/factura electrónica con validez tributaria.
- **Antes:** inscripción SUNAT persona natural con negocio → RUC → régimen → establecimiento → series → emisor electrónico → Nubefact.

## Con RUC — régimen económico (RET / RMT)

- Tienen RUC; el régimen define límites y tipo de comprobante.
- Confirmar con contador: boleta electrónica, factura a empresas, obligación de emisión.
- VentasÁgil emite boleta (`03`) y factura (`01`) si el cliente está registrado con RUC en el POS.

## Configuración en admin (tú)

1. Crear empresa.
2. Asignar plan y módulos (`sunat` si aplica).
3. Extras según rubro (`categorias_jerarquicas`, `lotes_vencimiento`, etc.).
4. Usuario dueño.
5. Cliente: onboarding en app.
6. Pro: checklist Nubefact → `/app/sunat` simulación → producción → 1 boleta prueba.

## Enlaces

- App: https://ventasagil.com
- Documentación SUNAT interna: [SUNAT.md](SUNAT.md)
- Puesta en marcha VPS: [PUESTA_EN_MARCHA.md](PUESTA_EN_MARCHA.md)
