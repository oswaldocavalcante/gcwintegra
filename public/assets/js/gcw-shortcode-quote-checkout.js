jQuery(document).ready(function() {
    
    $('#gcw_registration_form').on('submit', function (event) {

        event.preventDefault(); // Previne o comportamento padrão do formulário
        var formData = $(this).serialize(); // Serializa os dados do formulário
        alert(formData);

        $.ajax({
            url: gcw_quote_ajax_object.url, // URL do AJAX
            type: 'POST',
            data: formData + '&action=gcw_register_user', // Adiciona o action para o handler
            success: function (response) {
                if (response.success) {
                    window.location.href = response.data.redirect_url; // Redireciona para a URL fornecida
                } else {
                    $('#gcw_register_errors').html(response.data.message); // Exibe mensagem de erro
                }
            }
        });
    });

    $('#gcw_save_quote_button').on('click', function () {

        $.ajax({
            url: gcw_quote_ajax_object.url,
            type: 'POST',
            data: {
                action: 'gcw_save_quote',
                nonce: gcw_quote_ajax_object.nonce,
            },
            success: function (response) {
                if (response.success) {
                    window.location.href = response.data.redirect_url;
                } else {
                    alert(response.data.message);
                }
            },
            error: function (xhr, status, error) {
                console.log('AJAX Error: ' + status + ' - ' + error);
            }
        });
    });

});