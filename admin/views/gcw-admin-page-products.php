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

require_once GCW_ABSPATH . 'admin/list-tables/class-gcw-list-table-products.php';

$products_table = new GCW_List_Table_Products();
$products_table->prepare_items();

?>

<div class="wrap">
    <h2><?php echo __('GestãoClick - Importar Produtos', 'gestaoclick'); ?></h2>
    <form id="events-filter" method="post">
        <?php 
        $products_table->display();
        wp_nonce_field('gcw_form_products', 'gcw_nonce_products');
        ?>
    </form>
</div>