<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://oswaldocavalcante.com
 * @since             1.0.0
 * @package           Wooclick
 *
 * @wordpress-plugin
 * Plugin Name:       WooClick
 * Plugin URI:        https://github.com/oswaldocavalcante/wooclick
 * Description:       Integrates GestãoClick to WooCommerce.
 * Version:           1.0.0
 * Author:            Oswaldo Cavalcante
 * Author URI:        https://oswaldocavalcante.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wooclick
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOOCLICK_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wooclick-activator.php
 */
function activate_wooclick() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wooclick-activator.php';
	Wooclick_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wooclick-deactivator.php
 */
function deactivate_wooclick() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wooclick-deactivator.php';
	Wooclick_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wooclick' );
register_deactivation_hook( __FILE__, 'deactivate_wooclick' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wooclick.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wooclick() {

	$plugin = new Wooclick();
	$plugin->run();

}
run_wooclick();
