jQuery(document).ready(function ($)
{
	$(document).on('click', '#gcw-btn-import', function ()
	{
		var loaderContainer = $('#gcw-import-area');
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
			url: gcw_admin_ajax_object.url, // URL do AJAX do WordPress
			method: 'POST',
			data: {
				action: 'gestaoclick_update'
			},
			beforeSend: function () 
			{
				$('#gcw-btn-import').html('Importando...');
				loaderContainer.block(loaderProps);
			},
			complete: function () 
			{
				loaderContainer.unblock(); // Desbloqueia a interface do usuário quando a solicitação é concluída
				$('#gcw-btn-import').html('Importar agora');
			},
			success: function (response) {
				$('#gcw-last-import').html('Última importação: há 1 minuto');
				loaderContainer.prepend(response);
			},
			error: function (xhr, status, error) {
				console.error('Erro na requisição AJAX:', error);
			}
		});
	});

	$(document).on('click', '#gcw-button-nfe:not(.disabled)', function () 
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
				url: gcw_admin_ajax_object.url,
				method: 'POST',
				data:
				{
					order_id: $order_id,
					action: 'gcw_nfe',
					security: gcw_admin_ajax_object.nonce,
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
						console.log(response.data);
						window.open(response.data, '_blank');
					} else {
						console.error(response.data);
					}
				},
				error: function (xhr, status, error) {
					console.error(error);
				}
			});
	});
});