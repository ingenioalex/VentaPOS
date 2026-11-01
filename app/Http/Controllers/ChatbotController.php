<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $message = trim((string) $data['message']);
        $lower = mb_strtolower($message, 'UTF-8');

        [$reply, $quickReplies] = $this->answer($lower);

        return response()->json([
            'reply' => $reply,
            'quick_replies' => $quickReplies,
        ]);
    }

    private function answer(string $lower): array
    {
        $site = (string) config('seo.site_name', 'VentasÁgil');
        $whats = (string) config('ventapos.whatsapp');

        $quick = [
            'Ver planes',
            '¿Qué módulos necesito?',
            '¿Cómo empiezo?',
            'Hablar por WhatsApp',
        ];

        if ($this->hasAny($lower, ['hola', 'buenas', 'buenos', 'hey'])) {
            return [
                "Hola. Soy el asistente de {$site}.\n\nPuedo ayudarte con planes, módulos y cómo se usa el sistema. ¿Qué necesitas saber?",
                $quick,
            ];
        }

        if ($this->hasAny($lower, ['whatsapp', 'wsp', 'wa', 'asesor', 'hablar', 'llamar', 'contacto'])) {
            return [
                "Claro. Escríbenos por WhatsApp y te orientamos según tu negocio:\nhttps://wa.me/{$whats}",
                ['Ver planes', '¿Qué módulos necesito?', '¿Cómo empiezo?'],
            ];
        }

        if ($this->hasAny($lower, ['plan', 'planes', 'precio', 'precios', 'costo', 'cuesta', 'mensual', 'pago'])) {
            return [$this->planesReply(), ['¿Qué incluye cada plan?', '¿Qué módulos necesito?', '¿Cómo empiezo?', 'Hablar por WhatsApp']];
        }

        if ($this->hasAny($lower, ['modulo', 'módulo', 'modulos', 'módulos', 'necesito', 'recomienda', 'recomendacion', 'recomendación'])) {
            return [
                $this->modulosHelperReply(),
                [
                    'Solo vender en caja (POS)',
                    'Vender + controlar stock',
                    'Vender + stock + SUNAT',
                    'Hablar por WhatsApp',
                ],
            ];
        }

        if ($this->hasAny($lower, ['solo vender', 'solo venta', 'pos', 'caja'])) {
            return [
                $this->recomendarPaquete('basico', 'Si solo quieres vender en caja y tener cierre de caja.'),
                ['Ver planes', 'Vender + controlar stock', 'Hablar por WhatsApp'],
            ];
        }

        if ($this->hasAny($lower, ['stock', 'inventario', 'minimarket', 'bodega', 'ferreter', 'farmacia'])) {
            return [
                $this->recomendarPaquete('negocio', 'Si quieres vender y controlar stock, compras y proveedores.'),
                ['Ver planes', 'Vender + stock + SUNAT', 'Hablar por WhatsApp'],
            ];
        }

        if ($this->hasAny($lower, ['sunat', 'factura', 'boleta', 'electr', 'electrónica', 'ose', 'nubefact', 'ruc'])) {
            return [
                $this->recomendarPaquete('pro', 'Si necesitas boleta y factura electrónica (SUNAT).'),
                ['Ver planes', '¿Cómo empiezo?', 'Hablar por WhatsApp'],
            ];
        }

        if ($this->hasAny($lower, ['como', 'cómo', 'empezar', 'inicio', 'probar', 'beta', 'demo', 'usar'])) {
            return [
                "Para empezar:\n".
                "1) Pide la beta o una demo.\n".
                "2) Te ayudamos a cargar productos y configurar caja.\n".
                "3) Entras desde celular o PC (no se instala nada).\n\n".
                "Si me dices tu rubro (bodega/minimarket/ferretería/farmacia) te recomiendo plan y módulos.",
                ['¿Qué módulos necesito?', 'Ver planes', 'Hablar por WhatsApp'],
            ];
        }

        return [
            "Entiendo. Puedo responder sobre planes, módulos y cómo se usa {$site}.\n\nEscribe por ejemplo: \"precio\", \"módulos\" o \"SUNAT\".",
            $quick,
        ];
    }

    private function planesReply(): string
    {
        $arranque = config('planes.arranque');
        $arranqueMonto = (int) ($arranque['monto'] ?? 0);
        $arranqueEtiqueta = (string) ($arranque['etiqueta'] ?? 'Arranque');

        $beta = config('planes.beta');
        $basico = config('planes.basico');
        $negocio = config('planes.negocio');
        $pro = config('planes.pro');

        $betaNota = (string) ($beta['nota'] ?? 'Gratis 1 mes');
        $basicoMensual = (int) ($basico['mensual'] ?? 0);
        $negocioMensual = (int) ($negocio['mensual'] ?? 0);
        $proMensual = (int) ($pro['mensual'] ?? 0);

        return
            "Planes de VentasÁgil:\n".
            "- Beta: {$betaNota}\n".
            "- Básico: S/ {$basicoMensual}/mes\n".
            "- Negocio: S/ {$negocioMensual}/mes\n".
            "- Pro: S/ {$proMensual}/mes (incluye SUNAT)\n\n".
            "{$arranqueEtiqueta}: S/ {$arranqueMonto} (instalación + 1er mes).";
    }

    private function modulosHelperReply(): string
    {
        return
            "Te recomiendo módulos según tu necesidad.\n\n".
            "Elige una opción:\n".
            "- Solo vender en caja (POS)\n".
            "- Vender + controlar stock\n".
            "- Vender + stock + SUNAT";
    }

    private function recomendarPaquete(string $planKey, string $intro): string
    {
        $planes = config('planes');
        $plan = (array) ($planes[$planKey] ?? []);
        $planNombre = (string) ($plan['nombre'] ?? $planKey);
        $mensual = (int) ($plan['mensual'] ?? 0);

        $modulosKeys = (array) config("permisos.modulos_por_plan.{$planKey}", []);
        $modulos = (array) config('permisos.modulos');
        $modulosContr = (array) config('permisos.modulos_contratables');

        $labels = [];
        foreach ($modulosKeys as $k) {
            $nombre = $modulosContr[$k]['nombre'] ?? $modulos[$k] ?? $k;
            $labels[] = (string) $nombre;
        }
        $labels = array_values(array_unique(array_filter($labels)));

        $lista = $labels ? "- ".implode("\n- ", $labels) : "- (módulos por definir)";

        return
            "{$intro}\n\n".
            "Sugerencia: Plan {$planNombre} (S/ {$mensual}/mes) + módulos:\n{$lista}\n\n".
            "Si me dices cuántas cajas y cuántos usuarios, lo ajusto mejor.";
    }

    private function hasAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if ($n !== '' && str_contains($haystack, $n)) {
                return true;
            }
        }
        return false;
    }
}

