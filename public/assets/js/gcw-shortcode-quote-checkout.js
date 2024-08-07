jQuery(document).ready(function($) {

    $(document).on('submit', '#gcw-quote-container', function (event) {

        event.preventDefault(); // Previne o comportamento padrão do formulário
        var formData = $(this).serialize(); // Serializa os dados do formulário

        $.ajax({
            url: gcw_quote_ajax_object.url, // URL do AJAX
            type: 'POST',
            data: formData + '&action=gcw_finish_quote', // Adiciona o action para o handler
            success: function (response) {
                if (response.success) {
                    window.location.href = response.data.redirect_url; // Redireciona para a URL fornecida
                } else {
                    $('#gcw_quote_errors').html(response.data.message); // Exibe mensagem de erro
                }
            },
            error: function (xhr, status, error) {
                console.log('AJAX Error: ' + status + ' - ' + error);
            }
        });
    });

});