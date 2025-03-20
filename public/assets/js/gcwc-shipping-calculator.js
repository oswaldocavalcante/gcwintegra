jQuery(document).ready(function ($) 
{
    var loaderProps = 
    {
        message: null,
        overlayCSS:
        {
            background: '#fff',
            opacity: 0.6
        }
    };

    $('#gcwc-update-shipping-button').on('click', function () 
    {
        var loaderContainer = $('#gcwc_quote_totals_shipping');
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
                    if (!response.erro) 
                    {
                        var product_id = $('input[name="product_id"]').val();
                        var quantity = $('input[name="quantity"]').val();

                        var shipping_address_1 = response.logradouro;
                        var shipping_neighborhood = response.bairro;
                        var shipping_city = response.localidade;
                        var shipping_state = response.uf;

                        $('#gcwc_quote_shipping_address').html(
                            '<p>' + response.logradouro + ', ' + response.bairro + ', ' + response.localidade + '/' + response.uf + '</p>'
                        );

                        var shipping_address_html = $('#gcwc_quote_shipping_address').html();

                        $.ajax
                        ({
                            url: gcwc_quote_ajax_object.url,
                            type: 'POST',
                            data: 
                            {
                                action: 'gcwc_calculate_shipping',
                                nonce: gcwc_quote_ajax_object.nonce,

                                product_id: product_id,
                                quantity: quantity,
                                shipping_postcode: shipping_postcode,
                                shipping_address_1: shipping_address_1,
                                shipping_neighborhood: shipping_neighborhood,
                                shipping_city: shipping_city,
                                shipping_state: shipping_state,
                                shipping_address_html: shipping_address_html
                            },
                            beforeSend: function () {
                                loaderContainer.block(loaderProps);
                            },
                            complete: function () {
                                loaderContainer.unblock(); // Desbloqueia a interface do usuário quando a solicitação é concluída
                            },
                            success: function (response) {
                                $('#gcwc_quote_shipping_options').html(response.data.html);
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
});