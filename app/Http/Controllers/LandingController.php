<?php

namespace App\Http\Controllers;

class LandingController extends Controller
{
    public function inicio()
    {
        $razones = [
            [
                'icono' => '⚡',
                'titulo' => 'Vende en segundos',
                'resumen' => 'Cobro rápido desde celular o PC.',
                'detalle' => 'Busca por nombre, escanea código de barras o toca el producto en la grilla. El ticket sale al instante y el stock se descuenta solo.',
                'puntos' => ['POS escritorio y móvil', 'Pago efectivo, Yape, Plin o mixto', 'Boleta y factura electrónica'],
            ],
            [
                'icono' => '📊',
                'titulo' => 'Control total del negocio',
                'resumen' => 'Ventas, inventario y cierres en un solo lugar.',
                'detalle' => 'Dashboard con ventas del día, productos con stock bajo y reportes listos para tomar decisiones sin Excel.',
                'puntos' => ['KPI del día en tiempo real', 'Alertas de stock bajo', 'Reportes de ventas e inventario'],
            ],
            [
                'icono' => '🏪',
                'titulo' => 'Hecho para bodegas y comercio local',
                'resumen' => 'Plantillas para tu rubro desde el primer día.',
                'detalle' => 'Bodega, ferretería, farmacia o minimarket: categorías, productos pesables y multi-sucursal cuando crezcas.',
                'puntos' => ['Productos por KG o unidad', 'Varias empresas en la nube', 'Soporte de implementación en Chiclayo'],
            ],
            [
                'icono' => '☁️',
                'titulo' => 'Tu negocio en la nube',
                'resumen' => 'Accede desde cualquier dispositivo.',
                'detalle' => 'Sin instalar programas pesados. Tus datos respaldados y disponibles donde estés: mostrador, almacén o casa.',
                'puntos' => ['Funciona en celular y PC', 'Respaldos automáticos', 'Actualizaciones sin interrumpir ventas'],
            ],
            [
                'icono' => '🧾',
                'titulo' => 'Listo para SUNAT',
                'resumen' => 'Comprobantes electrónicos integrados.',
                'detalle' => 'Emite boletas y facturas con trazabilidad. Modo simulación para probar y ruta a producción cuando tu negocio esté listo.',
                'puntos' => ['Boleta y factura', 'Estados SUNAT visibles', 'QR en ticket de venta'],
            ],
            [
                'icono' => '💰',
                'titulo' => 'Arranque accesible',
                'resumen' => 'Empieza sin invertir una fortuna.',
                'detalle' => 'Plan de arranque con instalación y primer mes. Luego pagas solo tu plan mensual según el tamaño de tu negocio.',
                'puntos' => [
                    'Arranque S/'.config('planes.arranque.monto').' (instalación + mes 1)',
                    'Desde S/'.config('planes.basico.mensual').'/mes',
                    'Beta gratuita en Chiclayo (cupos limitados)',
                ],
            ],
        ];

        $baseUrl = rtrim(config('app.url'), '/');

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    'name' => 'VentasÁgil',
                    'url' => $baseUrl,
                    'logo' => asset('brand/logo.svg'),
                    'description' => config('seo.default_description'),
                    'areaServed' => [
                        ['@type' => 'City', 'name' => 'Chiclayo'],
                        ['@type' => 'Country', 'name' => 'Perú'],
                    ],
                ],
                [
                    '@type' => 'SoftwareApplication',
                    'name' => 'VentasÁgil',
                    'applicationCategory' => 'BusinessApplication',
                    'operatingSystem' => 'Web',
                    'url' => $baseUrl,
                    'description' => 'Punto de venta e inventario en la nube para bodegas y comercios en Perú.',
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => (string) config('planes.basico.mensual', 29),
                        'priceCurrency' => 'PEN',
                    ],
                ],
            ],
        ];

        return view('landing.inicio', compact('razones', 'jsonLd'));
    }

    public function precios()
    {
        return view('landing.precios');
    }

    public function contacto()
    {
        return view('landing.contacto');
    }

    public function faq()
    {
        $preguntas = [
            [
                'q' => '¿Qué es VentasÁgil?',
                'a' => 'Es un sistema punto de venta (POS) e inventario en la nube para bodegas, minimarkets, ferreterías y comercios en Perú. Vendes en caja, controlas stock y ves tus ventas del día desde celular o computadora.',
            ],
            [
                'q' => '¿Necesito instalar un programa en cada computadora?',
                'a' => 'No. VentasÁgil funciona en el navegador (Chrome, Edge, etc.). Entras con usuario y clave desde el local o tu celular. Solo necesitas internet.',
            ],
            [
                'q' => '¿Sirve para bodegas en Chiclayo?',
                'a' => 'Sí. Está pensado para comercio local: productos por unidad o kilo, código de barra, cierre de caja y reportes del día. Ofrecemos implementación y soporte en Chiclayo.',
            ],
            [
                'q' => '¿Cuánto cuesta VentasÁgil?',
                'a' => 'Hay beta gratis 1 mes. Los planes pagos tienen un arranque único (instalación + primer mes) y luego una cuota mensual según el plan Básico, Negocio o Pro. Ver precios actualizados en la página de planes.',
            ],
            [
                'q' => '¿Incluye facturación electrónica SUNAT?',
                'a' => 'El plan Pro incluye emisión de boleta y factura electrónica con código QR, integrado con Nubefact. Necesitas RUC activo y estar habilitado como emisor electrónico en SUNAT.',
            ],
            [
                'q' => '¿Puedo usar VentasÁgil sin RUC?',
                'a' => 'Sí para controlar ventas y stock. La boleta electrónica legal ante SUNAT requiere RUC y configuración tributaria. Puedes empezar sin RUC y activar SUNAT después.',
            ],
            [
                'q' => '¿Funciona en celular?',
                'a' => 'Sí. Hay POS de escritorio y POS móvil para cobrar desde el teléfono, ideal para negocios pequeños o segunda caja.',
            ],
            [
                'q' => '¿Sirve como software de inventario para minimarket?',
                'a' => 'Sí. Puedes registrar productos, controlar entradas y salidas, ver stock en tiempo real y recibir alertas de stock bajo. Ideal para minimarkets y bodegas de barrio.',
            ],
            [
                'q' => '¿VentasÁgil es un POS en Chiclayo con soporte local?',
                'a' => 'Sí. Ofrecemos implementación, capacitación y soporte para negocios en Chiclayo y Lambayeque. El sistema funciona en la nube en todo el Perú.',
            ],
            [
                'q' => '¿Puedo emitir factura electrónica a empresas con RUC?',
                'a' => 'Con el plan Pro puedes emitir factura electrónica registrando al cliente con RUC en el sistema. La boleta es para consumidor final. Ambas integran con SUNAT vía Nubefact.',
            ],
            [
                'q' => '¿Qué diferencia hay entre VentasÁgil y un cuaderno o Excel?',
                'a' => 'Con VentasÁgil el stock se descuenta al vender, tienes cierre de caja, reportes del día y varios cajeros con usuario propio. Evitas errores de suma y sabes cuánto vendiste sin revisar hojas manualmente.',
            ],
            [
                'q' => '¿Mis datos están seguros?',
                'a' => 'Cada negocio tiene sus datos aislados en la nube. Hacemos respaldos automáticos de la base de datos en el servidor.',
            ],
        ];

        $faqLd = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($preguntas)->map(fn ($p) => [
                '@type' => 'Question',
                'name' => $p['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $p['a'],
                ],
            ])->values()->all(),
        ];

        return view('landing.faq', [
            'preguntas' => $preguntas,
            'jsonLd' => $faqLd,
        ]);
    }
}
