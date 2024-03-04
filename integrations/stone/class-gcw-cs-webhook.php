<?php

class GCW_CS_Webhook {

    public function __construct() {
        register_rest_route('gestaoclick/stone', '/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'webhook_handler'),
        ));
    }

    public function webhook_handler($event){
        $data = wp_remote_retrieve_body( $event );
        var_dump( $data );
        error_log( 'Webhook successfully received' );
        // return new WP_REST_Response(__('Webhook successfully received'), 200);
    }
}