jQuery(document).ready(function ($)
{
    $(document).on('click', '#gcw_spec_sheet', function (e) 
    {
        e.preventDefault();
        
        var product_id = $(this).data('product-id');
        var quote_id   = $(this).data('quote-id');

        // Construir a URL
        var url = gcw_ajax.url + '?action=gcw_spec_sheet&product_id=' + product_id + '&quote_id=' + quote_id + '&nonce=' + gcw_ajax.nonce;

        // Abrir em uma nova aba
        window.open(url, '_blank');
    });
});