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
?>

<div class="wrap">
    <h2>WooClick - Settings</h2>
    <p class="description">GestãoClick API Access Settings</p>
    <form method="post" action="options.php">
        <?php
            settings_fields('wooclick_settings');
            do_settings_sections('wooclick_settings');
        ?>
        <div class="postbox">
            <div class="inside">
                <table class="form-table">
                    <tbody>
                        <tr class="mb-3">
                            <th>
                                <label class="form-label">Access Token</label>
                            </th>
                            <td>
                                <input type="text" name="access-token" value="<?php echo get_option('access-token') ?>" class="form-control" id="access-token">
                            </td>
                        </tr>
                        <tr class="mb-3">
                            <th>
                                <label class="form-label">Secret Access Token</label>
                            </th>
                            <td>
                                <input type="text" name="secret-access-token" value="<?php echo get_option('secret-access-token') ?>" class="form-control" id="secret-access-token">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php
                    if ( get_option('access-token') && get_option('secret-access-token') != '' ) {

                        $url = 'https://api.gestaoclick.com/produtos';
                        $args = array (
                            'headers' => array (
                                'Content-Type' => 'application/json',
                                'access-token' => get_option('access-token'),
                                'secret-access-token' => get_option('secret-access-token'),
                            ),
                        );

                        $response = wp_remote_get( $url, $args );
                        $http_code = wp_remote_retrieve_response_code( $response );
                    }

                    if ( $http_code == 200 ) $response = 'Conexão bem sucedida.';
                    elseif ( $http_code != 200 ) $response = 'Não foi possível estabelecer a conexão.';
                ?>
                <button type="submit" class="button button-primary">Salvar</button>
                <span><?php echo $response; ?></span>
            </div>
        </div>
    </form>
</div>