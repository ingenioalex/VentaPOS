<?php

namespace App\Support;

use Illuminate\Http\Response;

class SeccionKExport
{
    public static function excel(string $nombreHoja, array $encabezados, array $filas, string $archivo): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>'."\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        $xml .= '<Worksheet ss:Name="'.self::xml($nombreHoja).'"><Table>';
        $xml .= self::filaExcel($encabezados);
        foreach ($filas as $fila) {
            $xml .= self::filaExcel($fila);
        }
        $xml .= '</Table></Worksheet></Workbook>';

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$archivo.'.xls"',
        ]);
    }

    public static function texto(string $contenido, string $archivo): Response
    {
        return response($contenido, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$archivo.'.txt"',
        ]);
    }

    private static function filaExcel(array $celdas): string
    {
        $xml = '<Row>';
        foreach ($celdas as $celda) {
            $esNumero = is_numeric($celda) && $celda !== '';
            $tipo = $esNumero ? 'Number' : 'String';
            $xml .= '<Cell><Data ss:Type="'.$tipo.'">'.self::xml((string) $celda).'</Data></Cell>';
        }

        return $xml.'</Row>';
    }

    private static function xml(string $valor): string
    {
        return htmlspecialchars($valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
