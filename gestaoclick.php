<?php

/**
 * @link              https://oswaldocavalcante.com
 * @since             1.0.0
 * @package           GestaoClick
 *
 * @wordpress-plugin
 * Plugin Name:       GestãoClick para WooCommerce
 * Plugin URI:        https://github.com/oswaldocavalcante/gestaoclick
 * Description:       Integra o ERP GestãoClick ao WooCommerce para Wordpress.
 * Version:           3.5.4
 * Author:            Oswaldo Cavalcante
 * Author URI:        https://oswaldocavalcante.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gestaoclick
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce, woocommerce-extra-checkout-fields-for-brazil
 * Tested up to: 6.6.2
 * Requires PHP: 7.2
 * WC requires at least: 4.0
 * WC tested up to: 9.3.3
 */

// If this file is called directly, abort.
if (!defined( 'WPINC' )) { die; }
if (!defined('GCW_PLUGIN_FILE')) { define('GCW_PLUGIN_FILE', __FILE__); }
define('GCW_VERSION', '3.5.4');
define('GCW_ABSPATH', dirname(GCW_PLUGIN_FILE) . '/');
define('GCW_URL', plugins_url('/', __FILE__));

function activate_gestaoclick() 
{
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gestaoclick-activator.php';
	Gestaoclick_Activator::activate();
}

function deactivate_gestaoclick() 
{
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gestaoclick-deactivator.php';
	Gestaoclick_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gestaoclick' );
register_deactivation_hook( __FILE__, 'deactivate_gestaoclick' );

require plugin_dir_path( __FILE__ ) . 'includes/class-gestaoclick.php';

function run_gestaoclick() 
{
	$plugin = new Gestaoclick();
}

run_gestaoclick();
