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

        return view('landing.inicio', compact('razones'));
    }

    public function precios()
    {
        return view('landing.precios');
    }

    public function contacto()
    {
        return view('landing.contacto');
    }
}
