<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    Wooclick
 * @subpackage Wooclick/admin/partials
 */

include WP_PLUGIN_DIR . '/wooclick/admin/woocommerce/list-tables/class-wck-list-table-products.php';

$products_table = new WCK_List_Table_Products();
$products_table->prepare_items();

?>

<div class="wrap">
    <h2>WooClick - Importar produtos</h2>
    <form id="events-filter" method="post">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <?php
		$products_table->display();
        ?>
    </form>
</div>