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

require_once GCW_ABSPATH . 'admin/list-tables/class-gcw-list-table-categories.php';

$categories_table = new GCW_List_Table_Categories();
$categories_table->prepare_items();

?>

<div class="wrap">
    <h2><?php echo esc_html(__('GestãoClick - Importar Categorias', 'gestaoclick')); ?></h2>
    <form id="events-filter" method="post">
        <?php 
        $categories_table->display();
        wp_nonce_field('gcw_form_categories', 'gcw_nonce_categories');
        ?>
    </form>
</div>