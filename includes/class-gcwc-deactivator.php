<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    GCWC
 * @subpackage GCWC/includes
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 * @link       https://oswaldocavalcante.com
 */
class GCWC_Deactivator {

	/**
	 * Deactivates the cron hook.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() 
	{
		if (wp_next_scheduled('gcwc_update'))
		{
			$timestamp = wp_next_scheduled( 'gcwc_update' );
			wp_unschedule_event( $timestamp, 'gcwc_update' );
		}
	}
}
