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
			url: gcw_admin_ajax_object.ajaxurl, // URL do AJAX do WordPress
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
});