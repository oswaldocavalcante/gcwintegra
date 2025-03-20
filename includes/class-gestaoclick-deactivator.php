<?php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Gestaoclick
 * @subpackage Gestaoclick/includes
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 * @link       https://oswaldocavalcante.com
 */
class Gestaoclick_Deactivator {

	/**
	 * Deactivates the cron hook.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		if ( wp_next_scheduled( 'gestaoclick_update' ) ) {
			$timestamp = wp_next_scheduled( 'gestaoclick_update' );
			wp_unschedule_event( $timestamp, 'gestaoclick_update' );
		}
	}
}
