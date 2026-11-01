<?php

/**
 * Guía de respuesta comercial (solo lectura / copiar). No envía WhatsApp ni modifica prospectos.
 * Placeholders: {nombre}, {plan}, {usuario}, {arranque}, {mensual}, {basico_mensual}, {negocio_mensual}, {pro_mensual}
 */
return [

    'titulo' => 'Guía de respuesta',

    'descripcion' => 'Manual interno para el super admin. Aquí lees qué responder, copias el texto y lo pegas tú en WhatsApp. No envía mensajes, no crea empresas ni cambia prospectos.',

    'grupos' => [
        'prospeccion' => 'Prospección inicial',
        'planes' => 'Planes y ofertas',
        'sunat' => 'RUC y facturación SUNAT',
        'cierre' => 'Cierre y alta',
        'postventa' => 'Post-venta y seguimiento',
        'objeciones' => 'Objeciones frecuentes',
        'soporte' => 'Soporte y otros',
    ],

    'plantillas' => [

        // —— Prospección ——
        [
            'grupo' => 'prospeccion',
            'id' => 'que-es',
            'titulo' => 'Qué es VentasÁgil (pitch corto)',
            'cuando' => 'No entiende qué vendes',
            'texto' => <<<'TXT'
VentasÁgil es un punto de venta en la nube para bodegas y negocios en Chiclayo:

• Cobras rápido en caja (PC o celular)
• Controlas stock y ves cuánto vendiste hoy
• Cierras caja al final del día
• Opcional: boleta electrónica con QR SUNAT (plan Pro)

No es un programa que instalas en cada PC: entras con usuario y clave desde el navegador.

¿Te muestro en 15 minutos cómo se vería en tu negocio?
TXT,
        ],
        [
            'grupo' => 'prospeccion',
            'id' => 'primer-contacto',
            'titulo' => 'Primer contacto',
            'cuando' => 'Lead nuevo desde web o referido',
            'texto' => <<<'TXT'
Hola {nombre}, soy Yuri de VentasÁgil 👋

Vi que te interesó el sistema. Es un punto de venta en la nube para bodegas y negocios: vendes rápido, controlas stock y ves cuánto vendiste hoy desde el celular o la PC.

¿Qué tipo de negocio tienes y cuántas personas atienden la caja?
TXT,
        ],
        [
            'grupo' => 'prospeccion',
            'id' => 'calificacion',
            'titulo' => 'Calificación rápida',
            'cuando' => 'Después del primer mensaje',
            'texto' => <<<'TXT'
Para recomendarte el plan correcto, ¿me confirmas esto?

1️⃣ ¿Cómo llevas las ventas hoy? (cuaderno / Excel / otro sistema)
2️⃣ ¿Tienes RUC activo?
3️⃣ ¿Emites boleta o factura a tus clientes?
4️⃣ ¿Cuántos usuarios necesitarías? (dueño + cajeros)

Con eso te digo si te conviene empezar con prueba gratis o con plan con facturación SUNAT.
TXT,
        ],
        [
            'grupo' => 'prospeccion',
            'id' => 'demo-agendada',
            'titulo' => 'Demo agendada',
            'cuando' => 'Confirmaste fecha/hora de demostración',
            'texto' => <<<'TXT'
Listo {nombre}, quedamos para la demo de VentasÁgil:

📅 [FECHA]
🕐 [HORA]
📍 [Presencial en tu local / videollamada WhatsApp]

Te mostraré: carga de productos, venta en caja, cierre del día y (si aplica) boleta SUNAT.

Si puedes, ten a mano 5 productos que vendes seguido (nombre y precio).

¿Confirmas?
TXT,
        ],
        [
            'grupo' => 'prospeccion',
            'id' => 'post-demo',
            'titulo' => 'Después de la demo',
            'cuando' => 'Terminaste la demostración',
            'texto' => <<<'TXT'
Gracias por tu tiempo {nombre} 🙌

Resumen de lo que vimos:
• POS y cobro en caja
• Control de stock
• Reporte de ventas del día

*Plan recomendado para ti:* {plan} — arranque S/{arranque}, luego S/{mensual}/mes.

¿Quieres arrancar con beta gratis 1 mes o ir directo con el plan pago?
TXT,
        ],
        [
            'grupo' => 'prospeccion',
            'id' => 'recontacto-48h',
            'titulo' => 'Recontacto (no respondió)',
            'cuando' => 'Pasaron 2–3 días sin respuesta',
            'texto' => <<<'TXT'
Hola {nombre}, te escribo de nuevo por VentasÁgil.

¿Sigues interesado en probar el sistema para tu negocio? Tengo cupo para beta gratis este mes o puedo agendarte una demo de 15 min.

Sin compromiso — solo para que veas si te sirve.

¿Te va bien hoy en la tarde o mañana en la mañana?
TXT,
        ],

        // —— Planes ——
        [
            'grupo' => 'planes',
            'id' => 'comparacion-planes',
            'titulo' => 'Comparación de planes',
            'cuando' => 'Pide ver diferencias entre planes',
            'texto' => <<<'TXT'
Estos son nuestros planes (arranque único S/{arranque} en todos, incluye instalación + 1er mes):

*Beta* — Gratis 1 mes · 3 usuarios · para probar

*Básico* — S/{basico_mensual}/mes · POS, ventas, cierre, reportes

*Negocio* — S/{negocio_mensual}/mes · + inventario, compras, más usuarios

*Pro* — S/{pro_mensual}/mes · todo lo anterior + *boleta/factura electrónica SUNAT*

Si no necesitas factura electrónica aún, Básico o Negocio te alcanza. Si ya tienes RUC y Nubefact, Pro.

¿Cuál se acerca más a lo que necesitas?
TXT,
        ],
        [
            'grupo' => 'planes',
            'id' => 'oferta-beta',
            'titulo' => 'Oferta Beta gratis',
            'cuando' => 'Sin RUC o solo quiere probar',
            'texto' => <<<'TXT'
Te propongo empezar con la *beta gratis 1 mes*:

✅ Punto de venta (POS)
✅ Productos y stock
✅ Ventas y cierre de caja
✅ Hasta 3 usuarios
✅ Sin pago de arranque

Ideal para probar en tu negocio real. Si te gusta, pasas a plan pago desde S/{basico_mensual}/mes.

¿Te agendo 20 min para mostrarte cómo se usa en tu rubro?
TXT,
        ],
        [
            'grupo' => 'planes',
            'id' => 'oferta-basico',
            'titulo' => 'Plan Básico / Negocio (sin SUNAT)',
            'cuando' => 'Quiere POS sin factura electrónica',
            'texto' => <<<'TXT'
Para tu negocio te recomiendo el plan *{plan}*:

💰 *S/{arranque} único de arranque* (instalación + capacitación + 1er mes incluido)
📅 Desde el mes 2: *S/{mensual}/mes*

Incluye POS, ventas, productos, cierre de caja y reportes.

*No incluye* boleta electrónica SUNAT (puedes activarla después con plan Pro).

¿Cuándo podemos hacer la puesta en marcha en tu local?
TXT,
        ],
        [
            'grupo' => 'planes',
            'id' => 'oferta-pro',
            'titulo' => 'Plan Pro + SUNAT',
            'cuando' => 'Necesita boleta/factura electrónica',
            'texto' => <<<'TXT'
Si necesitas *boleta y factura electrónica con QR SUNAT*, el plan es *Pro*:

💰 *S/{arranque} único de arranque* (instalación + capacitación + 1er mes)
📅 Desde el mes 2: *S/{pro_mensual}/mes*

Incluye todo el sistema + *facturación electrónica*:
• Boleta y factura al cobrar en caja
• QR en el ticket (válido ante SUNAT)
• Envío automático vía Nubefact

*Requisitos de tu parte:*
☐ RUC activo (11 dígitos)
☐ Series de boleta/factura en SUNAT
☐ Cuenta Nubefact vinculada a tu RUC

¿Ya tienes RUC y Nubefact o estás en trámite?
TXT,
        ],
        [
            'grupo' => 'planes',
            'id' => 'upgrade-pro',
            'titulo' => 'Subir a plan Pro',
            'cuando' => 'Cliente actual quiere SUNAT',
            'texto' => <<<'TXT'
Hola {nombre}, para activar facturación electrónica SUNAT en tu cuenta actual:

📌 Cambio a plan *Pro*: S/{pro_mensual}/mes (desde el próximo periodo)
📌 Necesitas RUC + cuenta Nubefact (te paso checklist)

Tus productos, usuarios y ventas anteriores *no se pierden* — solo activamos el módulo SUNAT.

¿Quieres que te envíe el checklist de Nubefact?
TXT,
        ],
        [
            'grupo' => 'planes',
            'id' => 'extras-addons',
            'titulo' => 'Extras opcionales',
            'cuando' => 'Pregunta por flejes, lotes, WhatsApp, etc.',
            'texto' => <<<'TXT'
Además del plan, tenemos *extras* opcionales (~S/9/mes c/u):

• *Categorías jerárquicas* — subcategorías en productos
• *Lotes y vencimientos* — alertas farmacia / abarrotes
• *Flejes / etiquetas* — imprimir precios con código de barra
• *Resumen WhatsApp* — ventas del día por mensaje

El dueño los ve en Configuración; la activación la hacemos nosotros según lo que contrates.

¿Cuál te interesa para tu rubro?
TXT,
        ],

        // —— SUNAT ——
        [
            'grupo' => 'sunat',
            'id' => 'boleta-vs-factura',
            'titulo' => 'Boleta vs factura',
            'cuando' => 'No sabe cuál comprobante usar',
            'texto' => <<<'TXT'
En VentasÁgil al cobrar eliges el tipo de comprobante:

*Boleta* → para consumidor final (vecino, cliente con DNI o sin datos). Es lo más común en bodega.

*Factura* → solo si el comprador tiene *RUC* y lo registras en Clientes antes de cobrar.

Ambas pueden salir electrónicas con QR SUNAT si tienes plan Pro + Nubefact.

¿Tus clientes suelen pedir factura o solo boleta?
TXT,
        ],
        [
            'grupo' => 'sunat',
            'id' => 'sin-ruc',
            'titulo' => 'Cliente sin RUC',
            'cuando' => 'Aún no está formalizado en SUNAT',
            'texto' => <<<'TXT'
Sin RUC aún *no podemos activar boleta electrónica legal*, pero sí puedes usar VentasÁgil para:

✅ Vender más rápido en caja
✅ Controlar stock
✅ Ver ventas y cierres del día

Te recomiendo:
1. Empezar con beta gratis o plan Básico
2. Tramitar RUC en SUNAT (persona natural con negocio)
3. Cuando tengas RUC + Nubefact, subimos a Pro

¿Quieres los pasos para sacar RUC?
TXT,
        ],
        [
            'grupo' => 'sunat',
            'id' => 'pasos-ruc',
            'titulo' => 'Pasos para sacar RUC',
            'cuando' => 'Cliente preguntó cómo formalizarse',
            'texto' => <<<'TXT'
Pasos generales (confirma con tu contador):

1. Inscripción SUNAT — persona natural con negocio
2. Obtener RUC (11 dígitos, empieza con 10…)
3. Elegir régimen tributario (RET, RMT, etc.)
4. Alta del local / establecimiento
5. Series de *boleta* (y factura si vendes a empresas)
6. Alta como emisor electrónico (o vía Nubefact)
7. Cuenta en nubefact.com con tu RUC

Cuando tengas RUC y Nubefact, activamos facturación en 1 sesión.
TXT,
        ],
        [
            'grupo' => 'sunat',
            'id' => 'regimen-economico',
            'titulo' => 'RUC + régimen económico (RET)',
            'cuando' => 'Ya tiene RUC y cuota fija',
            'texto' => <<<'TXT'
Si ya tienes RUC y estás en régimen económico (RET / cuota fija):

✅ Puedes usar facturación electrónica *si SUNAT te tiene habilitado como emisor*.

Confirma con tu contador:
• ¿Puedo emitir *boleta electrónica*?
• ¿Necesito *factura* a otras empresas?

Yo configuro VentasÁgil; tú me pasas credenciales Nubefact.
TXT,
        ],
        [
            'grupo' => 'sunat',
            'id' => 'checklist-sunat',
            'titulo' => 'Checklist SUNAT (cliente Pro)',
            'cuando' => 'Ya cerró venta con plan Pro',
            'texto' => <<<'TXT'
*Checklist — Activar facturación electrónica*

Envíame por WhatsApp:

☐ RUC: _____________
☐ Razón social (igual SUNAT): _____________
☐ Dirección fiscal: _____________
☐ ¿Boleta, factura o ambas? _____________
☐ Cuenta Nubefact con tu RUC
☐ Ruta API Nubefact: _____________
☐ Token API Nubefact: _____________
☐ Contador confirmó emisión electrónica: Sí / No

Agendamos 30–45 min para boleta de prueba + ticket con QR.
TXT,
        ],
        [
            'grupo' => 'sunat',
            'id' => 'recordatorio-nubefact',
            'titulo' => 'Falta Nubefact',
            'cuando' => 'Cliente Pro sin SUNAT activo',
            'texto' => <<<'TXT'
Hola {nombre}, tu plan Pro incluye SUNAT, pero falta:

⏳ Cuenta en nubefact.com con tu RUC
⏳ Enviarme Ruta + Token API

Sin eso el POS funciona, pero las boletas no son electrónicas ante SUNAT.

¿Lo hace tu contador o necesitas guía?
TXT,
        ],
        [
            'grupo' => 'sunat',
            'id' => 'sunat-rechazado',
            'titulo' => 'Comprobante rechazado SUNAT',
            'cuando' => 'Cliente reporta error en boleta',
            'texto' => <<<'TXT'
Hola {nombre}, si una boleta salió *rechazada* en SUNAT:

1. Revisa en Ventas → detalle de la venta el mensaje de error
2. Verifica que RUC, razón social y dirección coincidan con SUNAT
3. Confirma series vigentes en Nubefact
4. Si el mensaje es tributario, consulta a tu contador

Envíame captura del error y el número de comprobante y lo revisamos juntos.
TXT,
        ],

        // —— Cierre ——
        [
            'grupo' => 'cierre',
            'id' => 'cierre',
            'titulo' => 'Cierre de venta',
            'cuando' => 'Prospecto listo para pagar arranque',
            'texto' => <<<'TXT'
Perfecto {nombre}. Para dejarte operativo:

📌 Plan: *{plan}*
📌 Arranque: *S/{arranque}* (instalación + capacitación + 1er mes)
📌 Desde mes 2: *S/{mensual}/mes*
📌 Pago: Yape / Plin / transferencia

Al confirmar pago:
1. Creo tu empresa en el sistema
2. Te envío usuario y clave
3. Agendamos capacitación
4. Cargamos productos

¿Arrancamos?
TXT,
        ],
        [
            'grupo' => 'cierre',
            'id' => 'datos-pago',
            'titulo' => 'Datos de pago',
            'cuando' => 'Pide número Yape/ cuenta',
            'texto' => <<<'TXT'
Datos para el pago del arranque (S/{arranque}):

💳 Yape / Plin: [TU NÚMERO]
🏦 Transferencia: [BANCO] · [CUENTA] · [TITULAR]

Concepto: VentasÁgil arranque — {nombre}
Plan: {plan}

Envíame captura del voucher y te activo el acceso el mismo día.
TXT,
        ],
        [
            'grupo' => 'cierre',
            'id' => 'agendar-capacitacion',
            'titulo' => 'Agendar capacitación',
            'cuando' => 'Ya pagó, falta fecha de instalación',
            'texto' => <<<'TXT'
{nombre}, para dejarte operativo agendemos la capacitación:

📅 Opciones: [día 1] o [día 2]
🕐 Duración: 1–2 horas
📍 En tu local o remoto por WhatsApp

Traeré lista para cargar tus primeros productos. Si tienes Excel o lista de precios, mejor.

¿Qué horario te queda mejor?
TXT,
        ],
        [
            'grupo' => 'cierre',
            'id' => 'bienvenida',
            'titulo' => 'Bienvenida post-alta',
            'cuando' => 'Empresa ya creada en admin',
            'texto' => <<<'TXT'
¡Listo {nombre}! Tu VentasÁgil ya está creado 🎉

🔗 https://ventasagil.com
👤 Usuario: {usuario}
🔑 Clave: [la que definiste]

Primeros pasos:
1. Cambiar contraseña
2. Revisar datos del negocio (RUC si aplica)
3. Cargar productos
4. Venta de prueba en POS

Cualquier duda, por este WhatsApp.
TXT,
        ],

        // —— Post-venta ——
        [
            'grupo' => 'postventa',
            'id' => 'seguimiento',
            'titulo' => 'Seguimiento 7 días',
            'cuando' => 'Una semana después del alta',
            'texto' => <<<'TXT'
Hola {nombre}, ¿cómo te va con VentasÁgil?

¿Ya pudiste vender en caja, ver el reporte del día y cerrar caja?

Si algo no cuadra (stock, precios, ticket), lo vemos en 10 min.

Tu feedback nos ayuda 👍
TXT,
        ],
        [
            'grupo' => 'postventa',
            'id' => 'renovacion-mes2',
            'titulo' => 'Renovación mes 2',
            'cuando' => 'Termina el mes incluido en arranque',
            'texto' => <<<'TXT'
Hola {nombre}, tu primer mes con VentasÁgil está por renovarse.

📌 Plan *{plan}*: S/{mensual}/mes
📌 Pago: Yape / Plin / transferencia [mismos datos]

Si todo va bien, solo envías el pago mensual y seguimos. Si necesitas ajustar plan o usuarios, me avisas antes.

¿Confirmamos la renovación?
TXT,
        ],
        [
            'grupo' => 'postventa',
            'id' => 'importar-productos',
            'titulo' => 'Importar productos CSV',
            'cuando' => 'Tiene muchos productos para cargar',
            'texto' => <<<'TXT'
{nombre}, para cargar muchos productos de una vez:

1. En VentasÁgil → Productos → *Importar CSV*
2. Descarga la plantilla
3. Llena: nombre, precio, código de barra, categoría, stock
4. Sube el archivo

Si quieres, en la capacitación lo hacemos juntos con tu lista actual.

¿Cuántos productos más o menos manejas?
TXT,
        ],
        [
            'grupo' => 'postventa',
            'id' => 'referido',
            'titulo' => 'Cliente referido',
            'cuando' => 'Alguien lo recomendó o viceversa',
            'texto' => <<<'TXT'
Hola {nombre}, [NOMBRE REFIERE] me pasó tu contacto — gracias por la confianza.

VentasÁgil es el POS en la nube que usan para controlar ventas y stock en [rubro].

¿Te cuento en 2 minutos cómo funciona o prefieres una demo corta?
TXT,
        ],

        // —— Objeciones ——
        [
            'grupo' => 'objeciones',
            'id' => 'objecion-caro',
            'titulo' => '«Es muy caro»',
            'cuando' => 'Objeción de precio',
            'texto' => <<<'TXT'
Entiendo {nombre}. Mira el arranque S/{arranque} incluye instalación, capacitación y el primer mes — no pagas aparte un técnico.

Desde el mes 2 son S/{mensual}/mes: menos que un día de ventas perdidas por desorden en stock o cuaderno.

También puedes empezar con *beta gratis 1 mes* sin arranque y decidir después.

¿Probamos la beta primero?
TXT,
        ],
        [
            'grupo' => 'objeciones',
            'id' => 'objecion-otro-sistema',
            'titulo' => '«Ya tengo otro sistema»',
            'cuando' => 'Usa competencia o Excel',
            'texto' => <<<'TXT'
Perfecto que ya uses algo. VentasÁgil no te obliga a cambiar el primer día:

• Puedes probar *1 mes gratis* en paralelo
• Importamos productos por CSV
• Soporte local en Chiclayo (no solo call center)

Si al final no mejora tu día a día, no sigues. ¿Qué es lo que más te falla hoy con lo que usas?
TXT,
        ],
        [
            'grupo' => 'objeciones',
            'id' => 'objecion-internet',
            'titulo' => '«Y si se cae el internet?»',
            'cuando' => 'Preocupación por conexión',
            'texto' => <<<'TXT'
Buena pregunta. VentasÁgil funciona en la nube — necesitas internet para vender y sincronizar.

En la práctica:
• WiFi del local + datos del celular como respaldo
• La venta guarda en el servidor al instante
• Si se corta unos minutos, retomas al volver la señal

Para cortes largos estamos mejorando opciones offline; hoy el 95% de bodegas opera bien con internet básico.

¿Tu local suele tener WiFi estable?
TXT,
        ],
        [
            'grupo' => 'objeciones',
            'id' => 'no-interesado',
            'titulo' => 'No interesado (cierre amable)',
            'cuando' => 'Descarta la oferta',
            'texto' => <<<'TXT'
Entendido {nombre}, gracias por tu tiempo 🙏

Si más adelante quieres ordenar ventas y stock, aquí estoy. También tenemos beta gratis 1 mes cuando quieras probar sin compromiso.

¡Éxitos con tu negocio!
TXT,
        ],

        // —— Soporte ——
        [
            'grupo' => 'soporte',
            'id' => 'soporte-horario',
            'titulo' => 'Horario de soporte',
            'cuando' => 'Pregunta cuándo atiendes',
            'texto' => <<<'TXT'
Soporte VentasÁgil:

🕐 Lunes a sábado, 9:00 a.m. – 7:00 p.m. (hora Chiclayo)
📱 Por este WhatsApp
🚨 Urgencias en horario de venta: respondemos lo antes posible

Para capacitación o configuración SUNAT, agendamos cita.

¿En qué te ayudo ahora?
TXT,
        ],
        [
            'grupo' => 'soporte',
            'id' => 'olvido-clave',
            'titulo' => 'Olvidó su contraseña',
            'cuando' => 'Cliente no entra a la app',
            'texto' => <<<'TXT'
Hola {nombre}, para recuperar acceso:

1. En https://ventasagil.com → *¿Olvidaste tu contraseña?*
2. Ingresa el correo o usuario que te dimos de alta
3. Sigue el enlace que llega al correo

Si no tienes correo configurado, escríbeme el nombre de tu negocio y usuario y lo reseteamos desde soporte (solo el dueño).

¿Cuál es el usuario con el que intentas entrar?
TXT,
        ],
    ],

];
