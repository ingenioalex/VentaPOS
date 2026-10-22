<script>
(function () {
    const MAX_BYTES = 900 * 1024;
    const MAX_LADO = 1600;

    function etiqueta(input, texto) {
        const nombre = input.closest('label')?.querySelector('.nombre-archivo')
            || input.parentElement?.querySelector('.nombre-archivo');
        if (nombre) {
            nombre.textContent = texto;
        }
    }

    function esImagen(file) {
        const tipo = (file.type || '').toLowerCase();
        const nombre = (file.name || '').toLowerCase();
        return tipo.indexOf('image/') === 0 || /\.(jpe?g|png|webp|gif|heic|heif)$/.test(nombre);
    }

    function toBlob(canvas, quality) {
        return new Promise(function (resolve) {
            canvas.toBlob(resolve, 'image/jpeg', quality);
        });
    }

    async function comprimir(file) {
        if (!file || !esImagen(file)) {
            return file;
        }
        if (file.size <= MAX_BYTES && !/heic|heif/i.test((file.type || '') + (file.name || ''))) {
            return file;
        }
        let bitmap;
        try {
            bitmap = await createImageBitmap(file);
        } catch (error) {
            return file;
        }
        const escala = Math.min(1, MAX_LADO / Math.max(bitmap.width, bitmap.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(bitmap.width * escala));
        canvas.height = Math.max(1, Math.round(bitmap.height * escala));
        canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
        if (bitmap.close) {
            bitmap.close();
        }
        let quality = 0.8;
        let blob = await toBlob(canvas, quality);
        while (blob && blob.size > MAX_BYTES && quality > 0.45) {
            quality -= 0.1;
            blob = await toBlob(canvas, quality);
        }
        if (!blob) {
            return file;
        }
        const base = (file.name || 'comprobante').replace(/\.[^.]+$/, '');
        return new File([blob], base + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
    }

    document.addEventListener('change', async function (event) {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.files || !input.files[0]) {
            return;
        }
        if (!input.closest('form[enctype="multipart/form-data"]')) {
            return;
        }
        const original = input.files[0];
        etiqueta(input, 'Reduciendo foto…');
        try {
            const compacto = await comprimir(original);
            const data = new DataTransfer();
            data.items.add(compacto);
            input.files = data.files;
            etiqueta(input, compacto.name + ' (' + Math.round(compacto.size / 1024) + ' KB)');
        } catch (error) {
            etiqueta(input, original.name);
        }
    });

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.getAttribute('enctype') !== 'multipart/form-data') {
            return;
        }
        const pesados = [];
        form.querySelectorAll('input[type="file"]').forEach(function (input) {
            const file = input.files && input.files[0];
            if (file && file.size > 1.5 * 1024 * 1024) {
                pesados.push(input);
            }
        });
        if (!pesados.length) {
            return;
        }
        event.preventDefault();
        pesados.forEach(function (input) {
            input.value = '';
            etiqueta(input, 'Cargar imagen (opcional)');
        });
        alert('La foto del celular es muy pesada y el servidor la rechaza. Elige otra o registra el pago sin imagen.');
    });
})();
</script>
