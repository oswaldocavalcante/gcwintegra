<?php

/**
 * Fired during plugin activation
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    Gestaoclick
 * @subpackage Gestaoclick/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Gestaoclick
 * @subpackage Gestaoclick/includes
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */

require_once GCW_ABSPATH . 'public/views/shortcodes/class-gcw-shortcode-quote-woocommerce.php';

class Gestaoclick_Activator {

	public static function activate() {
		self::create_quote_page();
	}

	private static function create_quote_page()
	{
		// Verificar se a página já existe
		$page_title = 'Orçamento';
		$page_content = '[gestaoclick_orcamento_woocommerce]';
		$page_check = get_page_by_title($page_title);

		// Se a página não existir, crie-a
		if (!isset($page_check->ID)) {
			$page_id = wp_insert_post(array(
				'post_title'     => $page_title,
				'post_content'   => $page_content,
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => 1,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			));

			update_option('gcw_quote_page_id', $page_id);
			update_option('gcw_quote_page_url', get_permalink($page_id));

		} else {
			// Atualizar a opção com o ID da página existente
			update_option('gcw_quote_page_id', $page_check->ID);
			update_option('gcw_quote_page_url', get_permalink($page_check->ID));
		}
	}
}
