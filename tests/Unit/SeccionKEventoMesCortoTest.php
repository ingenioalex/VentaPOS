<?php

namespace Tests\Unit;

use App\Models\SeccionKEvento;
use Tests\TestCase;

class SeccionKEventoMesCortoTest extends TestCase
{
    public function test_abreviar_desde_fecha(): void
    {
        $cuota = new SeccionKEvento([
            'fecha' => '2026-10-01',
            'mes_cuota' => 'Octubre 2026',
        ]);

        $this->assertSame('OCT26', $cuota->mesCorto());
    }

    public function test_abreviar_meses_en_espanol(): void
    {
        $casos = [
            '2026-11-01' => 'NOV26',
            '2027-01-01' => 'ENE27',
            '2027-12-01' => 'DIC27',
        ];

        foreach ($casos as $fecha => $esperado) {
            $cuota = new SeccionKEvento(['fecha' => $fecha]);
            $this->assertSame($esperado, $cuota->mesCorto());
        }
    }

    public function test_abreviar_desde_texto_si_no_hay_fecha(): void
    {
        $cuota = new SeccionKEvento(['mes_cuota' => 'Noviembre 2026']);

        $this->assertSame('NOV26', $cuota->mesCorto());
    }

    public function test_el_texto_del_mes_gana_sobre_la_fecha(): void
    {
        $cuota = new SeccionKEvento([
            'fecha' => '2028-01-01',
            'mes_cuota' => 'Enero 2027',
        ]);

        $this->assertSame('ENE27', $cuota->mesCorto());
    }
}
