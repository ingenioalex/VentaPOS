<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportExcelService
{
    /** @param  list<string>  $cabeceras  @param  list<list<scalar|null>>  $filas */
    public function descargar(string $nombreArchivo, array $cabeceras, array $filas, string $tituloHoja = 'Datos'): StreamedResponse
    {
        $nombre = str_ends_with(strtolower($nombreArchivo), '.xls') ? $nombreArchivo : $nombreArchivo.'.xls';

        return response()->streamDownload(function () use ($cabeceras, $filas, $tituloHoja) {
            echo $this->generarXml($tituloHoja, $cabeceras, $filas);
        }, $nombre, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    /** @param  list<string>  $cabeceras  @param  list<list<scalar|null>>  $filas */
    private function generarXml(string $tituloHoja, array $cabeceras, array $filas): string
    {
        $tituloHoja = $this->escapar($tituloHoja);
        $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $out .= '<?mso-application progid="Excel.Sheet"?>'."\n";
        $out .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ';
        $out .= 'xmlns:o="urn:schemas-microsoft-com:office:office" ';
        $out .= 'xmlns:x="urn:schemas-microsoft-com:office:excel" ';
        $out .= 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'."\n";
        $out .= '<Worksheet ss:Name="'.$tituloHoja.'"><Table>'."\n";

        $out .= '<Row>';
        foreach ($cabeceras as $cab) {
            $out .= '<Cell><Data ss:Type="String">'.$this->escapar((string) $cab).'</Data></Cell>';
        }
        $out .= '</Row>'."\n";

        foreach ($filas as $fila) {
            $out .= '<Row>';
            foreach ($fila as $celda) {
                if (is_int($celda) || is_float($celda) || (is_string($celda) && is_numeric($celda) && $celda !== '')) {
                    $out .= '<Cell><Data ss:Type="Number">'.$this->escapar((string) $celda).'</Data></Cell>';
                } else {
                    $out .= '<Cell><Data ss:Type="String">'.$this->escapar($celda === null ? '' : (string) $celda).'</Data></Cell>';
                }
            }
            $out .= '</Row>'."\n";
        }

        $out .= '</Table></Worksheet></Workbook>';

        return $out;
    }

    private function escapar(string $valor): string
    {
        return htmlspecialchars($valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
