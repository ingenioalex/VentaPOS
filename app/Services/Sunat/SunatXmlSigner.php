<?php

namespace App\Services\Sunat;

use RuntimeException;

class SunatXmlSigner
{
    /**
     * Firma XML UBL con certificado PEM (cert + private key).
     * Requiere extensión openssl. Si falla, devuelve el XML sin firmar.
     */
    public function firmar(string $xml, string $certificadoPath, string $claveCertificado = ''): string
    {
        if ($certificadoPath === '' || ! is_readable($certificadoPath)) {
            return $xml;
        }

        if (! extension_loaded('openssl')) {
            throw new RuntimeException('La extensión OpenSSL es necesaria para firmar XML.');
        }

        $contenido = file_get_contents($certificadoPath);
        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer el certificado digital.');
        }

        $key = $this->resolverPrivateKey($contenido, $claveCertificado);
        $cert = $this->resolverCertificado($contenido);

        if ($key === null || $cert === null) {
            throw new RuntimeException('Certificado inválido: se requiere PEM con certificado y clave privada, o archivo .pfx.');
        }

        $digest = base64_encode(hash('sha256', $xml, true));
        $signatureValue = '';
        $signed = openssl_sign($digest, $signatureValue, $key, OPENSSL_ALGO_SHA256);

        if (! $signed) {
            throw new RuntimeException('No se pudo firmar el XML con el certificado configurado.');
        }

        $signatureBlock = $this->bloqueFirmaSimplificado(base64_encode($signatureValue), $digest, $cert);

        return str_replace('</Invoice>', $signatureBlock.'</Invoice>', $xml);
    }

    private function resolverPrivateKey(string $contenido, string $clave): mixed
    {
        if (str_contains($contenido, 'BEGIN PRIVATE KEY') || str_contains($contenido, 'BEGIN RSA PRIVATE KEY')) {
            return openssl_pkey_get_private($contenido, $clave !== '' ? $clave : null);
        }

        if (str_contains($contenido, 'BEGIN CERTIFICATE') && ! str_contains($contenido, 'PRIVATE')) {
            return null;
        }

        $certs = [];
        if (openssl_pkcs12_read($contenido, $certs, $clave) && ! empty($certs['pkey'])) {
            return openssl_pkey_get_private($certs['pkey']);
        }

        return null;
    }

    private function resolverCertificado(string $contenido): ?string
    {
        if (preg_match('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', $contenido, $m)) {
            return $m[0];
        }

        $certs = [];
        if (openssl_pkcs12_read($contenido, $certs, '') && ! empty($certs['cert'])) {
            return $certs['cert'];
        }

        return null;
    }

    private function bloqueFirmaSimplificado(string $signatureValue, string $digest, string $cert): string
    {
        $certB64 = trim(str_replace(["\n", '-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'], '', $cert));

        return '<ext:UBLExtensions xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">'
            .'<ext:UBLExtension><ext:ExtensionContent>'
            .'<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">'
            .'<ds:SignedInfo><ds:Reference URI=""><ds:DigestValue>'.$digest.'</ds:DigestValue></ds:Reference></ds:SignedInfo>'
            .'<ds:SignatureValue>'.$signatureValue.'</ds:SignatureValue>'
            .'<ds:KeyInfo><ds:X509Data><ds:X509Certificate>'.$certB64.'</ds:X509Certificate></ds:X509Data></ds:KeyInfo>'
            .'</ds:Signature>'
            .'</ext:ExtensionContent></ext:UBLExtension></ext:UBLExtensions>';
    }
}
