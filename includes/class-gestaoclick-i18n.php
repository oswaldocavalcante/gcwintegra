<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    Gestaoclick
 * @subpackage Gestaoclick/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Gestaoclick
 * @subpackage Gestaoclick/includes
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */
class Gestaoclick_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'gestaoclick',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
