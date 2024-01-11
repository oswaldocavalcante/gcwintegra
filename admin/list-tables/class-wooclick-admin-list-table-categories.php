<?php

// Tutorial: https://supporthost.com/wp-list-table-tutorial/

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Wooclick_Admin_List_Table_Categories extends WP_List_Table {

    private $table_data;

    function __construct() {
        global $status, $page;

        parent::__construct( array(
          'singular' => 'category',
          'plural' => 'categories',
          'ajax' => false
        ) ); 
    }

   function column_default($item, $column_name) {

        switch ($column_name) {
            case 'id':
            case 'nome':
            case 'meta_descricao':
            case 'grupo_pai_id':
                return $item[$column_name];
            default:
                return print_r($item,true);
        }
    }

    function column_cb($item) {
        return sprintf(
                '<input type="checkbox" name="bulk-action[]" value="%1$s" />',
                /*$1%s*/$item['id']
        );
    }    

    function get_columns() {

        $columns = array(
            'cb'                => '<input type="checkbox" />',
            'id'                => __('ID', 'wooclick-categories'),
            'nome'              => __('Nome', 'wooclick-categories'),
            'meta_descricao'    => __('Descrição', 'wooclick-categories'),
            'grupo_pai_id'      => __('Pai ID', 'wooclick-categories')
        );
        return $columns;
    }

    function get_sortable_columns() {

        $sortable_columns = array(
            'id'            => array('id', false),
            'nome'          => array('nome', false),
            'grupo_pai_id'  => array('grupo_pai_id', false)
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(
            'import' => 'Importar selecionados',
            'import_all' => 'Importar todos'
        );
        return $actions;
    }

    function process_bulk_action() {

        $selected_items = isset($_POST['bulk-action']) ? $_POST['bulk-action'] : array();
        
        //Detect when a bulk action is being triggered...
        if( 'import' === $this->current_action() ) {
            $selected_items = isset($_POST['bulk-action']) ? $_POST['bulk-action'] : array();
            apply_filters( 'wooclick_import_categories', $selected_items );
        }

        if( 'import_all' === $this->current_action() ) {
            apply_filters( 'wooclick_import_categories', 'all' );
        }
    }

    // Sorting function
    function usort_reorder($a, $b)
    {
        // If no sort, default to user_login
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'nome';

        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    public function prepare_items() {
        
        $categories = get_option( 'wooclick-categories' );
        $this->table_data = $categories;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $primary  = 'name';
        $this->_column_headers = array($columns, $hidden, $sortable, $primary);
        $this->process_bulk_action();
        
        /* pagination */
        $total_items = count($this->table_data);
        $per_page = 100;
        $current_page = $this->get_pagenum();
        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total number of items
            'per_page'    => $per_page, // items to show on a page
            'total_pages' => ceil( $total_items / $per_page ) // use ceil to round up
        ));

        usort($this->table_data, array(&$this, 'usort_reorder'));

        $this->items = $this->table_data;
    }
}