jQuery(document).ready(function($) {
    var total_price_value = 0;

    // Remove quote item
    $('.gcw-button-remove').on('click', (event) => {
        const item_id = event.target.getAttribute('data-product_id');
        
        $.ajax({
            url: gcw_quote_ajax_object.url,
            type: 'POST',
            data: {
                action: 'gcw_remove_quote_item',
                nonce: gcw_quote_ajax_object.nonce,
                item_id: item_id,
            },
            success: function (response) {
                document.getElementById('gcw-quote-row-item-' + item_id).remove();
            },
            error: function (error) {
                console.error('Error: ', error);
            }
        });
    });

    // Update quote shipping
    $('#gcw-update-shipping-button').on('click', function () {
        var shipping_postcode = $('#shipping_postcode').val();

        if (shipping_postcode !== "") {

            $.ajax({
                url: 'https://viacep.com.br/ws/' + shipping_postcode + '/json/',
                type: 'GET',
                success: function (response) {
                    if (!response.erro) {
                        $('#gcw_quote_shipping_address').html(
                            '<p>' + response.logradouro + ', ' + response.bairro + ', ' + response.localidade + ' - ' + response.uf + '</p>'
                        );

                        var shipping_address = $('#gcw_quote_shipping_address').html();

                        // Limpar total ao recalcular fretes
                        $('#gcw_quote_total_display').html('');


                        $.ajax({
                            url: gcw_quote_ajax_object.url,
                            type: 'POST',
                            data: {
                                action: 'gcw_update_shipping',
                                nonce: gcw_quote_ajax_object.nonce,
                                shipping_postcode: shipping_postcode,
                                shipping_address: shipping_address
                            },
                            success: function (response) {
                                $('#gcw_quote_shipping_options').html(response.data.html);
                            },
                            error: function (xhr, status, error) {
                                console.log('AJAX Error: ' + status + ' - ' + error);
                            }
                        });

                    } else {
                        alert("CEP não encontrado.");
                    }
                }
            });

        } else {
            alert("Informe um CEP.");
        }
    });

    // Calcula o total do orçamento com o método de envio escolhido
    $(document).on('change', 'input.gcw_shipping_method_radio', function () {
        var shippingCost = $(this).val();
        var selectedMethod = $(this).data('method-id');

        $.ajax({
            url: gcw_quote_ajax_object.url,
            type: 'POST',
            data: {
                action: 'gcw_selected_shipping_method',
                shipping_method: selectedMethod,
                shipping_cost: shippingCost
            },
            success: function (response) {
                $('#gcw_quote_total_display').html(response.data.total_price_html);
                total_price_value = response.data.total_price_value;
            },
            error: function (xhr, status, error) {
                console.log('AJAX Error: ' + status + ' - ' + error);
            }
        });
    });

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
                    $('#gcw_registration_form').slideDown();
                }
            },
            error: function (xhr, status, error) {
                console.log('AJAX Error: ' + status + ' - ' + error);
            }
        });
    });
});