<script>
(function () {
    var copias = Math.max(1, Math.min(3, {{ (int) ($copias ?? 1) }}));
    function imprimirCuandoListo() {
        var imgs = document.querySelectorAll('img');
        var pendientes = imgs.length;
        function lanzar() {
            var n = 0;
            function una() {
                if (n >= copias) return;
                n++;
                window.print();
                if (n < copias) setTimeout(una, 400);
            }
            setTimeout(una, 150);
        }
        if (!pendientes) return lanzar();
        imgs.forEach(function (img) {
            if (img.complete) {
                pendientes--;
                if (pendientes === 0) lanzar();
            } else {
                img.addEventListener('load', function () { pendientes--; if (pendientes === 0) lanzar(); });
                img.addEventListener('error', function () { pendientes--; if (pendientes === 0) lanzar(); });
            }
        });
    }
    if (document.readyState === 'complete') imprimirCuandoListo();
    else window.addEventListener('load', imprimirCuandoListo);
})();
</script>
