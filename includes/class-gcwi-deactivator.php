<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    GCWI
 * @subpackage GCWI/includes
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 * @link       https://oswaldocavalcante.com
 */
class GCWI_Deactivator {

	/**
	 * Deactivates the cron hook.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() 
	{
		if (wp_next_scheduled('gcwi_update'))
		{
			$timestamp = wp_next_scheduled( 'gcwi_update' );
			wp_unschedule_event( $timestamp, 'gcwi_update' );
		}
	}
}
