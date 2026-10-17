@extends('seccionk.admin.layout')

@section('title', $titulo)
@section('page-title', $titulo)

@section('content')
<div class="mb-6 flex flex-wrap gap-3">
    <button type="button" onclick="copiarTexto()" class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700">
        <i class="fab fa-whatsapp mr-2"></i>Copiar texto
    </button>
    <a href="{{ $descargar }}" class="bg-gray-800 text-white px-5 py-2 rounded-lg hover:bg-gray-900">
        <i class="fas fa-download mr-2"></i>Descargar .txt
    </a>
    <a href="{{ $volver }}" class="px-5 py-2 rounded-lg border text-gray-700 hover:bg-gray-50">Volver</a>
</div>

<p id="aviso" class="hidden mb-4 text-green-700 font-semibold">Texto copiado. Pégalo en WhatsApp.</p>

<textarea id="texto-whatsapp" readonly rows="18" class="w-full bg-white rounded-xl shadow-lg p-4 sm:p-6 text-sm leading-6">{{ $texto }}</textarea>
@endsection

@section('scripts')
<script>
function copiarTexto() {
    const caja = document.getElementById('texto-whatsapp');
    caja.select();
    navigator.clipboard.writeText(caja.value).then(function () {
        document.getElementById('aviso').classList.remove('hidden');
    });
}
</script>
@endsection
