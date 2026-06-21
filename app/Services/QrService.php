<?php

namespace App\Services;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrService
{
    public function pngDataUri(string $texto, int $scale = 12): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => true,
            'scale' => $scale,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
            'bgColor' => [255, 255, 255],
            'imageTransparent' => false,
            'drawLightModules' => true,
            'moduleValues' => [
                0 => [255, 255, 255],
                1 => [0, 0, 0],
            ],
        ]);

        $data = (new QRCode($options))->render($texto);

        if (str_starts_with($data, 'data:')) {
            return $data;
        }

        return 'data:image/png;base64,'.$data;
    }

    public function pngBinary(string $texto, int $scale = 12): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => false,
            'scale' => $scale,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
            'bgColor' => [255, 255, 255],
            'imageTransparent' => false,
            'moduleValues' => [
                0 => [255, 255, 255],
                1 => [0, 0, 0],
            ],
        ]);

        return (new QRCode($options))->render($texto);
    }
}
