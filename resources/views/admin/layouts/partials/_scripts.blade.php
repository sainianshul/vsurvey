<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Tabler Core JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js" defer></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Toastify JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
function showToast(message, type = 'success') {
    Toastify({
        text: message,
        duration: 4000,
        close: true,
        gravity: "top",
        position: "right",
        style: {
            background: type === 'success' ? "#2fb344" : (type === 'error' ? "#d63939" : "#206bc4"),
        }
    }).showToast();
}

@if(session('success'))
    showToast("{{ session('success') }}", 'success');
@endif

@if(session('error'))
    showToast("{{ session('error') }}", 'error');
@endif
</script>

@stack('datatables_js')
@stack('scripts')