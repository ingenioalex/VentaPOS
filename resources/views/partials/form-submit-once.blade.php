@push('scripts')
<script>
document.querySelectorAll('main form[method="post"]').forEach(function (form) {
    form.addEventListener('submit', function () {
        var btn = form.querySelector('button[type="submit"]');
        if (!btn || btn.disabled) {
            return;
        }
        btn.disabled = true;
        btn.textContent = 'Guardando…';
    });
});
</script>
@endpush
