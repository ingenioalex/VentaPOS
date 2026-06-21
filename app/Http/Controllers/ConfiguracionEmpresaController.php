<?php



namespace App\Http\Controllers;



use App\Services\ExtrasEmpresaService;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;



class ConfiguracionEmpresaController extends Controller

{

    public function __construct(private ExtrasEmpresaService $extras) {}



    public function index()

    {

        $empresa = Auth::user()->empresa;

        $config = $empresa->configuracion ?? [];

        $extrasDisponibles = config('extras.disponibles', []);

        $extrasActivos = $this->extras->todos($empresa);

        $whatsappCfg = ($config['whatsapp_resumen'] ?? []);



        return view('app.configuracion.index', compact(

            'empresa', 'config', 'extrasDisponibles', 'extrasActivos', 'whatsappCfg'

        ));

    }



    public function actualizar(Request $request)

    {

        $empresa = Auth::user()->empresa;



        $datos = $request->validate([

            'nombre' => 'required|string|max:150',

            'ruc' => 'nullable|string|size:11',

            'telefono' => 'nullable|string|max:20',

            'direccion' => 'nullable|string|max:255',

            'tipo_negocio' => 'required|in:bodega,ferreteria,farmacia,otro',

            'serie_boleta' => 'required|string|max:10',

            'serie_factura' => 'required|string|max:10',

            'logo' => 'nullable|image|max:2048',

            'whatsapp_telefono' => 'nullable|string|max:20',

        ]);



        $config = $empresa->configuracion ?? [];

        $config['serie_boleta'] = strtoupper($datos['serie_boleta']);

        $config['serie_factura'] = strtoupper($datos['serie_factura']);



        if ($request->hasFile('logo')) {

            $ext = strtolower($request->file('logo')->getClientOriginalExtension());

            $nombre = 'empresa_'.$empresa->id.'.'.$ext;

            $request->file('logo')->storeAs('logos', $nombre, 'public');

            $config['logo'] = 'logos/'.$nombre;

        }



        if ($request->has('whatsapp_form') && $this->extras->activo($empresa, 'whatsapp_resumen')) {

            $config['whatsapp_resumen'] = array_merge(

                is_array($config['whatsapp_resumen'] ?? null) ? $config['whatsapp_resumen'] : [],

                ['telefono' => preg_replace('/\D/', '', $request->input('whatsapp_telefono', ''))]

            );

        }



        $empresa->update([

            'nombre' => $datos['nombre'],

            'ruc' => $datos['ruc'] ?? null,

            'telefono' => $datos['telefono'] ?? null,

            'direccion' => $datos['direccion'] ?? null,

            'tipo_negocio' => $datos['tipo_negocio'],

            'configuracion' => $config,

        ]);



        if ($request->has('whatsapp_form')) {

            return redirect()->route('configuracion.index')->with('ok', 'Teléfono WhatsApp actualizado.');

        }



        return redirect()->route('configuracion.index')

            ->with('ok', 'Configuración de empresa guardada.');

    }

}

