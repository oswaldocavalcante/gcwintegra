jQuery(document).ready(function($) 
{
    var loaderProps = {
        message: null,
        overlayCSS:
        {
            background: '#fff',
            opacity: 0.6
        }
    };

    // Remove quote item
    $('.gcw-button-remove').on('click', (event) => 
    {
        const item_id = event.target.getAttribute('data-product_id');
        var loaderContainer = $('#gcw-quote-tbody');
        
        $.ajax
        ({
            url: gcw_quote_ajax_object.url,
            type: 'POST',
            data: 
            {
                action: 'gcw_remove_quote_item',
                nonce: gcw_quote_ajax_object.nonce,
                item_id: item_id,
            },
            beforeSend: function() {
                loaderContainer.block(loaderProps);
            },
            complete: function () {
                loaderContainer.unblock();
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
    $('#gcw-update-shipping-button').on('click', function ()
    {
        var loaderContainer = $('#gcw-quote-totals-container');
        var shipping_postcode = $('#shipping_postcode').val();

        if (shipping_postcode !== "") 
        {
            $(this).addClass('processing');
            
            $.ajax
            ({
                url: 'https://viacep.com.br/ws/' + shipping_postcode + '/json/',
                type: 'GET',
                success: function (response) 
                {
                    if (!response.erro) {
                        var shipping_address_1      = response.logradouro;
                        var shipping_neighborhood   = response.bairro;
                        var shipping_city           = response.localidade;
                        var shipping_state          = response.uf;

                        $('#gcw_quote_shipping_address').html(
                            '<p>' + response.logradouro + ', ' + response.bairro + ', ' + response.localidade + '/' + response.uf + '</p>'
                        );

                        var shipping_address_html = $('#gcw_quote_shipping_address').html();

                        $.ajax({
                            url: gcw_quote_ajax_object.url,
                            type: 'POST',
                            data: {
                                action: 'gcw_update_shipping',
                                nonce:  gcw_quote_ajax_object.nonce,
                                
                                shipping_postcode:      shipping_postcode,
                                shipping_address_1:     shipping_address_1,
                                shipping_neighborhood:  shipping_neighborhood,
                                shipping_city:          shipping_city,
                                shipping_state:         shipping_state,
                                shipping_address_html:  shipping_address_html
                            },
                            beforeSend: function () {
                                loaderContainer.block(loaderProps);
                            },
                            complete: function () {
                                loaderContainer.unblock(); // Desbloqueia a interface do usuário quando a solicitação é concluída
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
    $(document).on('change', 'input.gcw_shipping_method_radio', function ()
    {
        var selectedMethod_id = $(this).data('method-id');
        var loaderContainer = $('#gcw-quote-totals-container');

        $.ajax({
            url: gcw_quote_ajax_object.url,
            type: 'POST',
            data: {
                action: 'gcw_selected_shipping_method',
                shipping_method_id: selectedMethod_id,
            },
            beforeSend: function() {
                loaderContainer.block(loaderProps);
            },
            complete: function() {
                loaderContainer.unblock();
            },
            success: function (response) {
                $('#gcw_quote_total_display').html(response.data.total_price_html);
            },
            error: function (xhr, status, error) {
                console.log('AJAX Error: ' + status + ' - ' + error);
            }
        });
    });

    $('#gcw_save_quote_button').on('click', function () {

        if ($('input[name="shipping_method"]:checked').val()) {
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
        } else {
            alert('Você precisa selecionar um método de envio.');
        }
    });
});