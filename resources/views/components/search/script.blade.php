{{-- <script>
    $(document).ready(function() {
        // Quando o usuário digita algo no campo de entrada
        $('#searchInput').on('input', function() {
            // Obtém o valor do campo de entrada
            var query = $(this).val();

            // Envia a consulta ao servidor via AJAX
            $.ajax({
                method: 'POST', // Pode ser GET ou POST, dependendo do seu backend
                url: 'http://127.0.0.1:8000/painel/cars/search', // Substitua pelo URL correto
                data: { query: query }, // Envie a consulta e o token CSRF
                success: function(response) {
                    // Manipule a resposta do servidor aqui e atualize a interface do usuário conforme necessário
                    console.log(response);
                },
                error: function(error) {
                    console.error(error);
                }
            });
        });
    });
</script> --}}
