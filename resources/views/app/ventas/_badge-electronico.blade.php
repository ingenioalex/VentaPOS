@php $badge = $comprobante->badgeElectronico(); @endphp
<span class="cpe-badge {{ $badge['clase'] }}" title="{{ $badge['titulo'] }}">{{ $badge['texto'] }}</span>
