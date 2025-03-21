<?php

/**
 * @link              https://oswaldocavalcante.com
 * @since             1.0.0
 * @package           GCWC
 *
 * @wordpress-plugin
 * Plugin Name:       GCWC - GestãoClick para WooCommerce
 * Plugin URI:        https://github.com/oswaldocavalcante/gcwc
 * Description:       Integra o ERP GestãoClick ao WooCommerce.
 * Version:           3.5.4
 * Author:            Oswaldo Cavalcante
 * Author URI:        https://oswaldocavalcante.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gcwc
 * Requires Plugins:  woocommerce, woocommerce-extra-checkout-fields-for-brazil
 * Tested up to: 6.6.2
 * Requires PHP: 7.2
 * WC requires at least: 4.0
 * WC tested up to: 9.3.3
 */

// If this file is called directly, abort.
if (!defined('WPINC')) die;
if (!defined('GCWC_PLUGIN_FILE')) define('GCWC_PLUGIN_FILE', __FILE__);
define('GCWC_ABSPATH', dirname(GCWC_PLUGIN_FILE) . '/');
define('GCWC_URL', plugins_url('/', __FILE__));
define('GCWC_VERSION', '3.5.4');

function deactivate_gcwc() 
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-gcwc-deactivator.php';
	GCWC_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_gcwc');

require plugin_dir_path(__FILE__) . 'includes/class-gcwc.php';

function run_gcwc() 
{
	$plugin = new GCWC();
}

run_gcwc();