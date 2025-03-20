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

require_once GCWC_ABSPATH . 'admin/list-tables/class-gcwc-list-table-attributes.php';

$attributes_table = new GCWC_List_Table_Attributes();
$attributes_table->prepare_items();

?>

<div class="wrap">
    <h2><?php echo esc_html(__('GestãoClick - Importar Atributos', 'gcwc')); ?></h2>
    <form id="events-filter" method="post">
        <?php 
        $attributes_table->display();
        wp_nonce_field('gcwc_form_attributes', 'gcwc_nonce_attributes');
        ?>
    </form>
</div>