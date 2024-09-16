<?php

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$user_id  = get_post_field('post_author', $quote_id);
$customer = new WC_Customer($user_id);

$site_icon_path = '';
if (get_site_icon_url())
{
    $site_icon_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], get_site_icon_url());
}

$pcw_layers         = get_post_meta($parent_id ? $parent_id : $product_id, 'pcw_layers', true);
$orcamento_codigo   = get_post_meta($quote_id, 'gc_codigo', true);
$orcamento_data     = get_the_date('d/m/Y', $quote_id);
$quote_items        = get_post_meta($quote_id, 'items', true);

$item = array_filter($quote_items, function ($item) use ($product_id)
{
    return $item['product_id'] == $product_id;
});
$item_number = array_keys($item)[0] + 1;
$item = reset($item);
$quantity         = $item['quantity'];
$product_name     = $product->get_name();
$customizations   = isset($item['customizations']) ? $item['customizations'] : [];

$printing_methods = get_post_meta($parent_id ? $parent_id : $product_id, 'pcw_printing_methods', true);
$printing_method_front = [];
$printing_method_back  = [];

if (is_array($printing_methods) && !empty($printing_methods))
{
    $printing_method_id_front = isset($customizations['printing_methods']['front']) ? $customizations['printing_methods']['front'] : '';
    $printing_method_id_back  = isset($customizations['printing_methods']['back']) ? $customizations['printing_methods']['back'] : '';

    $printing_method_front = array_filter($printing_methods, function ($printing_method) use ($printing_method_id_front)
    {
        return $printing_method['id'] == $printing_method_id_front;
    });
    $printing_method_front = reset($printing_method_front);

    $printing_method_back = array_filter($printing_methods, function ($printing_method) use ($printing_method_id_back)
    {
        return $printing_method['id'] == $printing_method_id_back;
    });
    $printing_method_back = reset($printing_method_back);
}

$printing_logo_url_front = isset($customizations['printing_logos']['front']) ? $customizations['printing_logos']['front'] : '';
$printing_logo_url_back  = isset($customizations['printing_logos']['back']) ? $customizations['printing_logos']['back'] : '';
$printing_logo_qr_code_front = create_qr_code($printing_logo_url_front);
$printing_logo_qr_code_back  = create_qr_code($printing_logo_url_back);

function create_qr_code($url, $size = 70, $margin = 5)
{
    if (!$url)  {
        return null;
    }

    $qr_code = QrCode::create($url)->setSize($size)->setMargin($margin);
    $writer = new PngWriter();
    $result = $writer->write($qr_code);

    return 'data:image/png;base64,' . base64_encode($result->getString());
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt" lang="pt">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>IMPRESSÃO</title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            text-indent: 0;
        }

        body {
            margin: 1cm;
        }

        h1 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 11pt;
        }

        p {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 8pt;
            margin: 0pt;
        }

        h2 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 8pt;
        }

        a {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
        }

        .textbox {
            background: #E8E8E8;
            border: 0.8pt solid #D3D3D3;
            width: 100%;
        }

        #header {
            border: 1px solid #ccc;
            width: 100%;
        }

        #title {
            font-size: 11pt;
            vertical-align: middle;
            text-align: center;
            font-weight: bold;
            padding: 3px;
        }

        #images {
            width: 100%;
        }

        #images .image-title {
            text-align: center;
            font-weight: bold;
            background: #E8E8E8;
            padding: 4px;
        }

        .image_wrapper {
            width: 50%;
        }

        .image_wrapper p {
            text-align: center;
        }

        .image_wrapper .product-image {
            width: 100%;
            height: auto;
        }

        .qr-code {
            width: 100%;
            height: auto;
        }

        table {
            width: 100%;
            vertical-align: top;
            overflow: visible;
            padding: 0;
        }

        .table-border td {
            border: 1px solid #ccc;
        }

        .table-data td {
            padding: 3px;
        }

        .zero-border td {
            border: 0;
        }

        .table-header {
            padding: 3px;
            background: #E8E8E8;
            border: 1px solid #ccc;
        }

        .line-title {
            font-weight: bold;
        }

        .table-header p {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div id="header">
        <table style="width: 100%;">
            <tr>
                <td><img width="100px" src="<?php echo esc_url($site_icon_path); ?>" /></td>
                <td style="width: 40%; vertical-align: middle;">
                    <h1><?php echo get_bloginfo('name'); ?></h1>
                    <p>CNPJ: 08.596.720/0001-73</p>
                    <p><br></p>
                    <p>(82) 3235-5224</p>
                    <p style="font-weight: bold;">ryanne.com.br</p>
                </td>
                <td style="width: 60%; text-align: right;">
                    <a href="<?php echo get_the_permalink($quote_id); ?>"><img src="<?php echo create_qr_code(get_the_permalink($quote_id)); ?>" /></a>
                    <p style="text-align: right;"><small><a href="<?php echo get_the_permalink($quote_id); ?>">ORÇAMENTO <?php echo $orcamento_codigo; ?></a></small></p>
                    <p style="text-align: right;"><small><?php echo $orcamento_data; ?></small></p>
                </td>
            </tr>
        </table>
    </div>

    <p><br /></p>

    <div class="textbox">
        <p id="title">FICHA TÉCNICA <?php echo $orcamento_codigo . '.' . $item_number; ?></p>
    </div>

    <p><br /></p>

    <table cellspacing="0" class="table-border table-data">
        <tr>
            <td colspan="4" class="table-header">
                <p>DADOS DO CLIENTE</p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="line-title">Nome:</p>
            </td>
            <td colspan="3">
                <p><?php echo $customer->get_billing_company() ? $customer->get_billing_company() : $customer->get_first_name() . ' ' . $customer->get_last_name(); ?></p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="line-title">Telefone:</p>
            </td>
            <td>
                <p><?php echo $customer->get_billing_phone(); ?></p>
            </td>
            <td>
                <p class="line-title">E-mail:</p>
            </td>
            <td>
                <p><a href="mailto:<?php echo $customer->get_billing_email(); ?>"><?php echo $customer->get_billing_email(); ?></a></p>
            </td>
        </tr>
    </table>

    <p><br /></p>

    <table class="table-border table-data" cellspacing="0">
        <tr>
            <td colspan="3" class="table-header">
                <p>DADOS DO PRODUTO</p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="line-title">ITEM Nº</p>
            </td>
            <td>
                <p class="line-title">NOME</p>
            </td>
            <td>
                <p class="line-title">QUANTIDADE</p>
            </td>
        </tr>
        <tr>
            <td>
                <p><?php echo $item_number; ?></p>
            </td>
            <td>
                <p><?php echo $product_name ?></p>
            </td>
            <td>
                <p><?php echo $quantity; ?></p>
            </td>
        </tr>
    </table>

    <p><br /></p>

    <table id="images" class="table-border" cellspacing="0">
        <tr>
            <td>
                <p class="image-title">Frente</p>
            </td>
            <td>
                <p class="image-title">Costas</p>
            </td>
        </tr>
        <tr>
            <td class="image_wrapper">
                <img class="product-image" src="<?php echo $customizations['images']['front']; ?>" />
            </td>
            <td class="image_wrapper">
                <img class="product-image" src="<?php echo $customizations['images']['back']; ?>" />
            </td>
        </tr>
        <tr>
            <td style="padding: 4px;">
                <table cellspacing="0" class="zero-border">
                    <tr>
                        <?php if ($printing_logo_qr_code_front): ?>
                            <td>
                                <img class="qr-code" src="<?php echo $printing_logo_qr_code_front; ?>" />
                            </td>
                        <?php endif; ?>
                        <td>
                            <p><strong>Tipo de impressão:</strong> <?php echo $printing_method_front ? $printing_method_front['name'] : 'não informado'; ?></p>
                            <?php echo $printing_logo_qr_code_front ? '<p><a href="<?php echo $printing_logo_url_front; ?>">Leia o QR Code para obter a imagem.</a></p>' : ''; ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="padding: 4px;">
                <table cellspacing="0" class="zero-border">
                    <tr>
                        <?php if ($printing_logo_qr_code_back): ?>
                            <td>
                                <img class="qr-code" src="<?php echo $printing_logo_qr_code_back; ?>" />
                            </td>
                        <?php endif; ?>
                        <td>
                            <p><strong>Tipo de impressão:</strong> <?php echo $printing_method_back ? $printing_method_back['name'] : 'não informado'; ?></p>
                            <?php echo $printing_logo_qr_code_back ? '<p><a href="<?php echo $printing_logo_url_back; ?>">Leia o QR Code para obter a imagem.</a></p>' : ''; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p><br /></p>


    <?php
    $customization_number = 1;
    ?>
    <table cellspacing="0" class="table-border table-data">
        <tr>
            <td class="table-header" colspan="4">
                <p class="line-title">PERSONALIZAÇÕES</p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="line-title">Nº</p>
            </td>
            <td>
                <p class="line-title">PARTE</p>
            </td>
            <td>
                <p class="line-title">OPÇÃO</p>
            </td>
            <td>
                <p class="line-title">COR</p>
            </td>
        </tr>
        <tr>
            <td>
                <p><?php echo $customization_number++; ?></p>
            </td>
            <td collspan="2">
                <p>Peça base</p>
            </td>
            <td>
                <p>Cor</p>
            </td>
            <td>
                <p><?php echo $customizations['color']['name'] . ' ' . $customizations['color']['value']; ?></p>
            </td>
        </tr>
        <?php

        if (is_array($customizations['layers']))
        :
            foreach ($customizations['layers'] as $key => $layer)
            :
                $customization_layer = array_filter($pcw_layers, function ($pcw_layer) use ($key) {
                    return $pcw_layer['id'] == $key;
                });
                $customization_layer = reset($customization_layer);

                foreach ($layer['options'] as $option_key => $option)
                :
                    $customization_option = array_filter($customization_layer['options'], function ($pcw_layer_option) use ($option_key) {
                        return $pcw_layer_option['id'] == $option_key;
                    });
                    $customization_option = reset($customization_option);

                    $pcw_option_colors = $customization_option['colors'] ? $customization_option['colors'] : get_option('pcw-settings-colors'); //Se o item customizado não tiver opções de cor, obtenha as cores padrão
                    $customization_color = array_filter($pcw_option_colors, function ($pcw_option_color) use ($option) {
                        return $pcw_option_color['id'] == $option['color'];
                    });
                    $customization_color = reset($customization_color);

                    $color_name = $customization_color['name'];
                    $color_value = $customization_color['value'];

                    ?>
                    <tr>
                        <td>
                            <p><?php echo $customization_number++; ?></p>
                        </td>
                        <td>
                            <p><?php echo $customization_layer['layer'] ? $customization_layer['layer'] : ''; ?></p>
                        </td>
                        <td>
                            <p><?php echo $customization_option['name'] ? $customization_option['name'] : ''; ?></p>
                        </td>
                        <td>
                            <p><?php echo ($color_name ? $color_name : '') . ' ' . ($color_value ? '(' . $color_value . ')' : ''); ?></p>
                        </td>
                    </tr>
                    <?php
                endforeach;
            endforeach;
        endif;

        ?>
    </table>

    <p><br /></p>

    <?php if ($customizations['notes']): ?>
        <table cellspacing="0" class="table-border table-data">
            <tr>
                <td class="table-header">
                    <p class="line-title">OBSERVAÇÕES DO CLIENTE</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p><?php echo $customizations['notes']; ?></p>
                </td>
            </tr>
        </table>
    <?php endif; ?>

</body>

</html>