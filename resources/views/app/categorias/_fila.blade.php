<tr>
    <td>{{ $cat->orden }}</td>
    <td>
        <form method="post" action="{{ route('categorias.actualizar', $cat) }}" class="cat-edit">
            @csrf @method('PUT')
            @if($jerarquicas ?? false)
            <select name="parent_id">
                <option value="">— Raíz —</option>
                @foreach($todas->whereNull('parent_id')->where('id', '!=', $cat->id) as $padre)
                <option value="{{ $padre->id }}" @selected($cat->parent_id == $padre->id)>{{ $padre->nombre }}</option>
                @endforeach
            </select>
            @endif
            <span style="{{ ($indent ?? 0) ? 'padding-left:1rem;color:#64748b' : '' }}">
                @if($indent ?? 0)↳ @endif
            </span>
            <input name="nombre" value="{{ $cat->nombre }}" required>
            <input type="number" name="orden" value="{{ $cat->orden }}" min="0">
            <button type="submit" class="btn btn-primary" style="padding:.3rem .6rem;font-size:.8rem">✓</button>
        </form>
    </td>
    @if($jerarquicas ?? false)
    <td style="font-size:.85rem;color:#64748b">{{ $cat->parent?->nombre ?? '—' }}</td>
    @endif
    <td>{{ $cat->productos_count }}</td>
    <td>
        @if($cat->productos_count === 0)
        <form method="post" action="{{ route('categorias.eliminar', $cat) }}" onsubmit="return confirm('¿Eliminar categoría?')">
            @csrf @method('DELETE')
            <button type="submit" style="border:none;background:none;color:#dc2626;cursor:pointer">Eliminar</button>
        </form>
        @endif
    </td>
</tr>
