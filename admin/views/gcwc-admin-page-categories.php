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

require_once GCWC_ABSPATH . 'admin/list-tables/class-gcwc-list-table-categories.php';

$categories_table = new GCWC_List_Table_Categories();
$categories_table->prepare_items();

?>

<div class="wrap">
    <h2><?php echo esc_html(__('GestãoClick - Importar Categorias', 'gcwc')); ?></h2>
    <form id="events-filter" method="post">
        <?php 
        $categories_table->display();
        wp_nonce_field('gcwc_form_categories', 'gcwc_nonce_categories');
        ?>
    </form>
</div>