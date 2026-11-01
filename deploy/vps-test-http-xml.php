<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$id = (int) ($argv[1] ?? 3622);
$user = User::where('empresa_id', 1)->where('activo', true)->first();

if (! $user) {
    echo "No hay usuario empresa 1\n";
    exit(1);
}

Auth::login($user);
session(['tenant_empresa_id' => $user->empresa_id]);

$req = Request::create("/app/sunat/comprobantes/{$id}/xml", 'GET', [], [], [], [
    'HTTP_HOST' => 'ventasagil.com',
    'HTTPS' => 'on',
]);
$req->setLaravelSession(app('session.store'));

$res = $app->handle($req);

echo 'user='.$user->id.' modulo_sunat='.($user->tieneModulo('sunat') ? 'yes' : 'no')."\n";
echo 'status='.$res->getStatusCode()."\n";
echo 'content_type='.$res->headers->get('Content-Type')."\n";
echo 'bytes='.strlen($res->getContent())."\n";
if ($res->getStatusCode() !== 200) {
    echo substr($res->getContent(), 0, 500)."\n";
}
