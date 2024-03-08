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

require_once GCW_ABSPATH . 'admin/list-tables/class-gcw-list-table-attributes.php';

$attributes_table = new GCW_List_Table_Attributes();
$attributes_table->prepare_items();

?>

<div class="wrap">
    <h2><?php echo __('GestãoClick - Importar Atributos', 'gestaoclick'); ?></h2>
    <form id="events-filter" method="post">
        <?php $attributes_table->display(); ?>
    </form>
</div>