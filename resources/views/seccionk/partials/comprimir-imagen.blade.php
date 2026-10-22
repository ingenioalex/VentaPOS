<script>
(function () {
    const MAX_BYTES = 700 * 1024;
    const MAX_LADO = 1280;

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
            if (!canvas.toBlob) {
                resolve(null);
                return;
            }
            canvas.toBlob(resolve, 'image/jpeg', quality);
        });
    }

    function leerComoImagen(file) {
        return new Promise(function (resolve, reject) {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = function () {
                URL.revokeObjectURL(url);
                resolve(img);
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('no se pudo leer la foto'));
            };
            img.src = url;
        });
    }

    async function fuenteImagen(file) {
        if (typeof createImageBitmap === 'function') {
            try {
                return await createImageBitmap(file);
            } catch (error) {
                // HEIC u otros formatos del celular: intentar con Image.
            }
        }
        return leerComoImagen(file);
    }

    function asignarArchivo(input, file) {
        try {
            const data = new DataTransfer();
            data.items.add(file);
            input.files = data.files;
            return !!(input.files && input.files[0] && input.files[0].size === file.size);
        } catch (error) {
            return false;
        }
    }

    async function comprimir(file) {
        if (!file || !esImagen(file)) {
            return file;
        }
        if (file.size <= MAX_BYTES && !/heic|heif/i.test((file.type || '') + (file.name || ''))) {
            return file;
        }

        const fuente = await fuenteImagen(file);
        const ancho = fuente.width || fuente.naturalWidth || 1;
        const alto = fuente.height || fuente.naturalHeight || 1;
        const escala = Math.min(1, MAX_LADO / Math.max(ancho, alto));
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(ancho * escala));
        canvas.height = Math.max(1, Math.round(alto * escala));
        canvas.getContext('2d').drawImage(fuente, 0, 0, canvas.width, canvas.height);
        if (fuente.close) {
            fuente.close();
        }

        let quality = 0.72;
        let blob = await toBlob(canvas, quality);
        while (blob && blob.size > MAX_BYTES && quality > 0.4) {
            quality -= 0.12;
            blob = await toBlob(canvas, quality);
        }
        if (!blob || blob.size > MAX_BYTES) {
            throw new Error('sigue pesada');
        }
        const base = (file.name || 'comprobante').replace(/\.[^.]+$/, '');
        return new File([blob], base + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
    }

    async function alistarInput(input) {
        const original = input.files && input.files[0];
        if (!original) {
            return true;
        }
        etiqueta(input, 'Reduciendo foto…');
        try {
            const compacto = await comprimir(original);
            if (compacto.size <= MAX_BYTES && asignarArchivo(input, compacto)) {
                etiqueta(input, compacto.name + ' (' + Math.round(compacto.size / 1024) + ' KB)');
                return true;
            }
        } catch (error) {
            // Si no se puede reducir, el pago se envía sin foto.
        }
        input.value = '';
        etiqueta(input, 'Foto omitida: el celular la mandó muy pesada. El pago se guarda igual.');
        return true;
    }

    document.addEventListener('change', function (event) {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.files || !input.files[0]) {
            return;
        }
        if (!input.closest('form[enctype="multipart/form-data"]')) {
            return;
        }
        alistarInput(input);
    });

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.getAttribute('enctype') !== 'multipart/form-data') {
            return;
        }
        if (form.dataset.seccionkListo === '1') {
            return;
        }
        const inputs = Array.from(form.querySelectorAll('input[type="file"]')).filter(function (input) {
            return input.files && input.files[0];
        });
        if (!inputs.length) {
            return;
        }
        event.preventDefault();
        const botones = form.querySelectorAll('[type="submit"]');
        botones.forEach(function (boton) {
            boton.disabled = true;
        });
        Promise.all(inputs.map(alistarInput)).then(function () {
            form.querySelectorAll('input[type="file"]').forEach(function (input) {
                const file = input.files && input.files[0];
                if (file && file.size > MAX_BYTES) {
                    input.value = '';
                    etiqueta(input, 'Foto omitida. El pago se guarda igual.');
                }
            });
            form.dataset.seccionkListo = '1';
            HTMLFormElement.prototype.submit.call(form);
        }).catch(function () {
            form.querySelectorAll('input[type="file"]').forEach(function (input) {
                input.value = '';
            });
            form.dataset.seccionkListo = '1';
            HTMLFormElement.prototype.submit.call(form);
        }).finally(function () {
            botones.forEach(function (boton) {
                boton.disabled = false;
            });
        });
    });
})();
</script>
