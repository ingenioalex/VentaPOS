<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductoImportService
{
    /** @var array<string, string> */
    private const ALIAS_COLUMNAS = [
        'nombre' => 'nombre',
        'producto' => 'nombre',
        'codigo_barra' => 'codigo_barra',
        'codigo barra' => 'codigo_barra',
        'barra' => 'codigo_barra',
        'sku' => 'sku',
        'categoria' => 'categoria',
        'categoría' => 'categoria',
        'precio_venta' => 'precio_venta',
        'precio' => 'precio_venta',
        'precio venta' => 'precio_venta',
        'costo_compra' => 'costo_compra',
        'costo' => 'costo_compra',
        'costo compra' => 'costo_compra',
        'stock_disponible' => 'stock_disponible',
        'stock' => 'stock_disponible',
        'stock_minimo' => 'stock_minimo',
        'stock minimo' => 'stock_minimo',
        'minimo' => 'stock_minimo',
        'mínimo' => 'stock_minimo',
        'unidad_medida' => 'unidad_medida',
        'unidad' => 'unidad_medida',
        'um' => 'unidad_medida',
        'afecto_igv' => 'afecto_igv',
        'igv' => 'afecto_igv',
        'campo1' => 'campo1',
        'campo2' => 'campo2',
        'campo3' => 'campo3',
    ];

    /**
     * @return array{creados: int, actualizados: int, errores: array<int, string>}
     */
    public function importar(int $empresaId, UploadedFile $archivo): array
    {
        $ruta = $archivo->getRealPath();
        if (! $ruta || ! is_readable($ruta)) {
            throw new RuntimeException('No se pudo leer el archivo CSV.');
        }

        $handle = fopen($ruta, 'r');
        if ($handle === false) {
            throw new RuntimeException('No se pudo abrir el archivo CSV.');
        }

        $primera = fgets($handle);
        if ($primera === false) {
            fclose($handle);
            throw new RuntimeException('El archivo CSV está vacío.');
        }

        $delimitador = $this->detectarDelimitador($primera);
        rewind($handle);

        $encabezados = fgetcsv($handle, 0, $delimitador);
        if ($encabezados === false || $encabezados === [null]) {
            fclose($handle);
            throw new RuntimeException('El CSV no tiene encabezados válidos.');
        }

        $mapa = $this->mapearColumnas($encabezados);
        if (! isset($mapa['nombre'], $mapa['precio_venta'])) {
            fclose($handle);
            throw new RuntimeException('El CSV debe incluir las columnas nombre y precio_venta (o precio).');
        }

        $creados = 0;
        $actualizados = 0;
        $errores = [];
        $fila = 1;
        $skuBase = Producto::where('empresa_id', $empresaId)->count();

        while (($filaDatos = fgetcsv($handle, 0, $delimitador)) !== false) {
            $fila++;
            if ($this->filaVacia($filaDatos)) {
                continue;
            }

            try {
                $datos = $this->extraerFila($filaDatos, $mapa);
                $resultado = DB::transaction(function () use ($empresaId, $datos, &$skuBase) {
                    return $this->guardarFila($empresaId, $datos, $skuBase);
                });

                if ($resultado === 'creado') {
                    $creados++;
                } else {
                    $actualizados++;
                }
            } catch (\Throwable $e) {
                $errores[$fila] = $e->getMessage();
            }
        }

        fclose($handle);

        return compact('creados', 'actualizados', 'errores');
    }

    public function contenidoPlantilla(): string
    {
        $columnas = [
            'nombre', 'codigo_barra', 'sku', 'categoria', 'precio_venta', 'costo_compra',
            'stock', 'stock_minimo', 'unidad_medida', 'afecto_igv',
        ];

        $ejemplo = [
            'ARROZ COSTEÑO 1KG', '7751234567890', 'SKU00001', 'Abarrotes', '4.50', '3.20',
            '50', '10', 'UND', 'si',
        ];

        return implode(';', $columnas)."\n".implode(';', $ejemplo)."\n";
    }

    private function detectarDelimitador(string $linea): string
    {
        $puntosComa = substr_count($linea, ';');
        $comas = substr_count($linea, ',');

        return $puntosComa >= $comas ? ';' : ',';
    }

    /**
     * @param  array<int, string|null>  $encabezados
     * @return array<string, int>
     */
    private function mapearColumnas(array $encabezados): array
    {
        $mapa = [];
        foreach ($encabezados as $indice => $titulo) {
            $clave = $this->normalizarEncabezado((string) $titulo);
            if ($clave === '') {
                continue;
            }
            $campo = self::ALIAS_COLUMNAS[$clave] ?? null;
            if ($campo && ! isset($mapa[$campo])) {
                $mapa[$campo] = $indice;
            }
        }

        return $mapa;
    }

    private function normalizarEncabezado(string $titulo): string
    {
        $titulo = trim(mb_strtolower($titulo));
        $titulo = preg_replace('/^\x{FEFF}/u', '', $titulo) ?? $titulo;
        $titulo = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $titulo);

        return str_replace(' ', '_', $titulo);
    }

    /**
     * @param  array<int, string|null>  $fila
     * @param  array<string, int>  $mapa
     * @return array<string, mixed>
     */
    private function extraerFila(array $fila, array $mapa): array
    {
        $valor = function (string $campo) use ($fila, $mapa): string {
            if (! isset($mapa[$campo])) {
                return '';
            }

            return trim((string) ($fila[$mapa[$campo]] ?? ''));
        };

        $nombre = strtoupper($valor('nombre'));
        if ($nombre === '') {
            throw new RuntimeException('El nombre es obligatorio.');
        }

        $precio = $this->parseNumero($valor('precio_venta'));
        if ($precio === null || $precio < 0) {
            throw new RuntimeException('Precio de venta inválido.');
        }

        return [
            'nombre' => $nombre,
            'codigo_barra' => $valor('codigo_barra') ?: null,
            'sku' => $valor('sku') ?: null,
            'categoria' => $valor('categoria') ?: null,
            'precio_venta' => $precio,
            'costo_compra' => $this->parseNumero($valor('costo_compra')) ?? 0,
            'stock_disponible' => $this->parseNumero($valor('stock_disponible')) ?? 0,
            'stock_minimo' => $this->parseNumero($valor('stock_minimo')) ?? 0,
            'unidad_medida' => strtoupper($valor('unidad_medida') ?: 'UND'),
            'afecto_igv' => $this->parseBooleano($valor('afecto_igv'), true),
            'campo1' => $valor('campo1') ?: null,
            'campo2' => $valor('campo2') ?: null,
            'campo3' => $valor('campo3') ?: 'Mercadería',
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function guardarFila(int $empresaId, array $datos, int &$skuBase): string
    {
        $producto = null;
        if ($datos['codigo_barra']) {
            $producto = Producto::where('empresa_id', $empresaId)
                ->where('codigo_barra', $datos['codigo_barra'])
                ->first();
        }
        if (! $producto && $datos['sku']) {
            $producto = Producto::where('empresa_id', $empresaId)
                ->where('sku', $datos['sku'])
                ->first();
        }

        $categoriaId = $this->resolverCategoria($empresaId, $datos['categoria']);

        $payload = [
            'nombre' => $datos['nombre'],
            'codigo_barra' => $datos['codigo_barra'],
            'categoria_id' => $categoriaId,
            'precio_venta' => $datos['precio_venta'],
            'costo_compra' => $datos['costo_compra'],
            'stock_disponible' => $datos['stock_disponible'],
            'stock_minimo' => $datos['stock_minimo'],
            'unidad_medida' => $datos['unidad_medida'],
            'afecto_igv' => $datos['afecto_igv'],
            'campo1' => $datos['campo1'],
            'campo2' => $datos['campo2'],
            'campo3' => $datos['campo3'],
            'activo' => true,
        ];

        if ($producto) {
            if ($datos['sku']) {
                $payload['sku'] = $datos['sku'];
            }
            $producto->update($payload);

            return 'actualizado';
        }

        $skuBase++;
        Producto::create([
            ...$payload,
            'empresa_id' => $empresaId,
            'sku' => $datos['sku'] ?: ('SKU'.str_pad((string) $skuBase, 5, '0', STR_PAD_LEFT)),
        ]);

        return 'creado';
    }

    private function resolverCategoria(int $empresaId, ?string $nombre): ?int
    {
        if (! $nombre) {
            return null;
        }

        $nombre = strtoupper(trim($nombre));
        $categoria = Categoria::where('empresa_id', $empresaId)
            ->whereRaw('UPPER(nombre) = ?', [$nombre])
            ->first();

        if ($categoria) {
            return $categoria->id;
        }

        return Categoria::create([
            'empresa_id' => $empresaId,
            'nombre' => $nombre,
            'orden' => 0,
        ])->id;
    }

    private function parseNumero(string $valor): ?float
    {
        $valor = trim($valor);
        if ($valor === '') {
            return null;
        }

        $valor = str_replace([' ', 'S/', 's/'], '', $valor);
        if (str_contains($valor, ',') && str_contains($valor, '.')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } else {
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? (float) $valor : null;
    }

    private function parseBooleano(string $valor, bool $defecto): bool
    {
        $valor = strtolower(trim($valor));
        if ($valor === '') {
            return $defecto;
        }

        return in_array($valor, ['1', 'si', 'sí', 'true', 'yes', 'y', 's'], true);
    }

    /**
     * @param  array<int, string|null>  $fila
     */
    private function filaVacia(array $fila): bool
    {
        foreach ($fila as $celda) {
            if (trim((string) $celda) !== '') {
                return false;
            }
        }

        return true;
    }
}
