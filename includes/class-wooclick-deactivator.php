<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    Wooclick
 * @subpackage Wooclick/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Wooclick
 * @subpackage Wooclick/includes
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */
class Wooclick_Deactivator {

	/**
	 * Deactivates the cron hook.
	 *
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$timestamp = wp_next_scheduled( 'wooclick_cron_hook' );
			wp_unschedule_event( $timestamp, 'wooclick_cron_hook' );
		}
	}
}
