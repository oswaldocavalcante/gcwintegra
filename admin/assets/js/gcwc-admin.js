jQuery(document).ready(function ($)
{
	$(document).on('click', '#gcwc-btn-import', function ()
	{
		var loaderContainer = $('#gcwc-import-area');
		var loaderProps =
		{
			message: null,
			overlayCSS:
			{
				background: '#fff',
				opacity: 0.6
			}
		};

		$.ajax
		({
			url: gcwc_admin_ajax_object.url, // URL do AJAX do WordPress
			method: 'POST',
			data: {
				action: 'gcwc_update'
			},
			beforeSend: function () 
			{
				$('#gcwc-btn-import').html('Importando...');
				loaderContainer.block(loaderProps);
			},
			complete: function () 
			{
				loaderContainer.unblock(); // Desbloqueia a interface do usuário quando a solicitação é concluída
				$('#gcwc-btn-import').html('Importar agora');
			},
			success: function (response) {
				$('#gcwc-last-import').html('Última importação: há 1 minuto');
				loaderContainer.prepend(response);
			},
			error: function (xhr, status, error) {
				console.error('Erro na requisição AJAX:', error);
			}
		});
	});

	$(document).on('click', '#gcwc-button-nfe:not(.disabled)', function () 
	{
		var $button = $(this);
		var $order_id = $button.data('order-id');

		var loaderContainer = $button;
		var loaderProperties =
		{
			message: null,
			overlayCSS:
			{
				background: '#fff',
				opacity: 0.6
			}
		};

		$.ajax
			({
				url: gcwc_admin_ajax_object.url,
				method: 'POST',
				data:
				{
					order_id: $order_id,
					action: 'gcwc_nfe',
					security: gcwc_admin_ajax_object.nonce,
				},
				beforeSend: function () {
					loaderContainer.addClass('disabled');
					loaderContainer.block(loaderProperties);
				},
				complete: function () {
					loaderContainer.unblock();
					loaderContainer.removeClass('disabled');
				},
				success: function (response) {
					if (response.success) {
						window.open(response.data, '_blank');
					} else {
						alert(response.message);
						console.error(response.data);
					}
				},
				error: function (xhr, status, error) {
					console.error(error);
				}
			});
	});
});