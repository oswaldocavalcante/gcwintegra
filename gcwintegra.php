<?php

/**
 * @link              https://oswaldocavalcante.com
 * @since             1.0.0
 * @package           GCWI
 *
 * @wordpress-plugin
 * Plugin Name:       GCW Integra - GestãoClick for WooCommerce
 * Plugin URI:        https://github.com/oswaldocavalcante/gcwintegra
 * Description:       Integra o ERP GestãoClick ao WooCommerce.
 * Version:           3.5.6
 * Author:            Oswaldo Cavalcante
 * Author URI:        https://oswaldocavalcante.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gcwintegra
 * Requires Plugins:  woocommerce, woocommerce-extra-checkout-fields-for-brazil
 * Tested up to: 6.6.2
 * Requires PHP: 7.2
 * WC requires at least: 4.0
 * WC tested up to: 9.3.3
 */

// If this file is called directly, abort.
if(!defined('WPINC')) die;
if(!defined('GCWI_PLUGIN_FILE')) define('GCWI_PLUGIN_FILE', __FILE__);
define('GCWI_ABSPATH', dirname(GCWI_PLUGIN_FILE) . '/');
define('GCWI_URL', plugins_url('/', __FILE__));
define('GCWI_VERSION', '3.5.6');

register_deactivation_hook(__FILE__, 'deactivate_gcwi');
function deactivate_gcwi() 
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-gcwi-deactivator.php';
	GCWI_Deactivator::deactivate();
}

require plugin_dir_path(__FILE__) . 'includes/class-gcwi.php';
function gcwi_run() 
{
	$plugin = new GCWI();
}

gcwi_run();