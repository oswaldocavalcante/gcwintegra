<?php

$icon_url = get_site_icon_url();
$icon_path = '';

if ($icon_url)
{
    $upload_dir = wp_upload_dir();
    $icon_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $icon_url);
}

$orcamento_codigo = get_post_meta($quote_id, 'gc_codigo', true);
$orcamento_data = get_the_date('d/m/Y', $quote_id);
$product_name = $product->get_name();
$quantity = $item['quantity'];
$customizations = isset($item['customizations']) ? $item['customizations'] : [];
$user_id = get_post_field('post_author', $quote_id);
$customer = new WC_Customer($user_id);

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
            /* Define margens iguais em todos os lados */
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
            font-size: 8pt;
        }

        .s1 {
            color: black;
            font-family: Arial, sans-serif;
            font-weight: bold;
            font-size: 11pt;
            vertical-align: middle;
            text-align: center;
        }

        .s2 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 8pt;
        }

        .s3 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 8pt;
        }

        .s4 {
            color: black;
            font-family: "Times New Roman", serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
        }

        .s5 {
            color: black;
            font-family: "Times New Roman", serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 8pt;
        }

        .s6 
        {
            font-weight: bold;
            font-size: 9pt;
        }

        .s7 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 8pt;
        }

        .textbox {
            background: #E8E8E8;
            border: 0.8pt solid #D3D3D3;
            width: 100%;
        }


        #images_container {
            display: block;
            width: 100%;
        }

        .image_wrapp {
            width: 50%;
            float: left;
        }

        .image_wrapp p {
            text-align: center;
        }

        .image_wrapp img {
            width: 100%;
            height: auto;
        }

        table
        ,tbody {
            width: 100%;
            vertical-align: top;
            overflow: visible;
        }
    </style>
</head>

<body>

    <div style="border: 1px solid #ccc; width: 100%;">
        <table style="width: 100%;">
            <tr>
                <td><img width="100px" src="<?php echo esc_url($icon_path); ?>" /></td>
                <td style="width: 40%; vertical-align: middle;">
                    <h1><?php echo get_bloginfo('name'); ?></h1>
                    <p style="padding-top: 1pt;text-indent: 0pt;text-align: left;">CNPJ: 08.596.720/0001-73</p>
                </td>
                <td style="width: 60%; vertical-align: middle;">
                    <h2 style="text-align: right;">(82)3235-5224</h2>
                    <h2 style="text-align: right;">ryanne.com.br</h2>
                    <p style="text-align: right;">Vendedor: <b>Raphaella Kelly</b></p>
                </td>
            </tr>
        </table>
    </div>

    <p><br /></p>

    <div class="textbox" style="min-height:17.5pt;">
        <p class="s1">FICHA TÉCNICA (ORÇAMENTO Nº <?php echo $orcamento_codigo; ?>)</p>
        <p style="float: right;"><?php echo $orcamento_data; ?></p>
    </div>

    <p><br /></p>

    <table cellspacing="0">
        <tr>
            <td colspan="4" class="textbox">
                <p class="s6">DADOS DO CLIENTE</p>
            </td>
        </tr>
        <tr>
            <td style="width:82pt;border-top-style:solid;border-top-width:2pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s2" style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">Razão social:</p>
            </td>
            <td style="width:160pt;border-top-style:solid;border-top-width:2pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s3" style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">Global Radiocomunicação EIRELI</p>
            </td>
            <td style="width:82pt;border-top-style:solid;border-top-width:2pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s2" style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">Nome fantasia:</p>
            </td>
            <td style="width:223pt;border-top-style:solid;border-top-width:2pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s3" style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">Global Radiocomunicação</p>
            </td>
        </tr>
        <tr>
            <td style="width:82pt;border-top-style:solid;border-top-width:1pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s2" style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">Telefone:</p>
            </td>
            <td style="width:160pt;border-top-style:solid;border-top-width:1pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s3" style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">(82) 3337-4980 - (82) 99993-0389</p>
            </td>
            <td style="width:82pt;border-top-style:solid;border-top-width:1pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s2" style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">E-mail:</p>
            </td>
            <td style="width:223pt;border-top-style:solid;border-top-width:1pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;"><a href="mailto:michelle.santos@globalradio.com.br" class="s7">michelle.santos@globalradio.com.br</a></p>
            </td>
        </tr>
    </table>

    <p><br /></p>

    <table cellspacing="0">
        <tr style="height:14pt">
            <td style="width:547pt;border-top-style:solid;border-top-width:1pt;border-top-color:#D3D3D3;border-left-style:solid;border-left-width:1pt;border-left-color:#D3D3D3;border-bottom-style:solid;border-bottom-width:2pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#D3D3D3" colspan="5" bgcolor="#E8E8E8">
                <p class="s6" style="padding-top: 1pt;padding-left: 1pt;text-indent: 0pt;text-align: left;">PERSONALIZAÇÕES</p>
            </td>
        </tr>
        <tr style="height:18pt">
            <td style="width:28pt;border-top-style:solid;border-top-width:2pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s2" style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">ITEM</p>
            </td>
            <td style="width:324pt;border-top-style:solid;border-top-width:2pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s2" style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">NOME</p>
            </td>
            <td style="width:58pt;border-top-style:solid;border-top-width:2pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s2" style="padding-top: 4pt;padding-right: 3pt;text-indent: 0pt;text-align: right;">QTD.</p>
            </td>
            <td style="width:68pt;border-top-style:solid;border-top-width:2pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s2" style="padding-top: 4pt;padding-right: 3pt;text-indent: 0pt;text-align: right;">VR. UNIT.</p>
            </td>
            <td style="width:69pt;border-top-style:solid;border-top-width:2pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s2" style="padding-top: 4pt;padding-right: 3pt;text-indent: 0pt;text-align: right;">SUBTOTAL</p>
            </td>
        </tr>
        <tr style="height:18pt">
            <td style="width:28pt;border-top-style:solid;border-top-width:1pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s3" style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">1</p>
            </td>
            <td style="width:324pt;border-top-style:solid;border-top-width:1pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s3" style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">Camisa Polo - Masculino/Feminino - PV <i>(M (adulto))</i></p>
            </td>
            <td style="width:58pt;border-top-style:solid;border-top-width:1pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s3" style="padding-top: 4pt;padding-right: 3pt;text-indent: 0pt;text-align: right;">10,00</p>
            </td>
            <td style="width:68pt;border-top-style:solid;border-top-width:1pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s3" style="padding-top: 4pt;padding-right: 3pt;text-indent: 0pt;text-align: right;">66,00</p>
            </td>
            <td style="width:69pt;border-top-style:solid;border-top-width:1pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC">
                <p class="s3" style="padding-top: 4pt;padding-right: 3pt;text-indent: 0pt;text-align: right;">660,00</p>
            </td>
        </tr>
        <tr style="height:18pt">
            <td style="width:352pt;border-top-style:solid;border-top-width:1pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC" colspan="2" bgcolor="#E8E8E8">
                <p class="s2" style="padding-top: 4pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">TOTAL</p>
            </td>
            <td style="width:58pt;border-top-style:solid;border-top-width:1pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC" bgcolor="#E8E8E8">
                <p class="s2" style="padding-top: 4pt;padding-right: 3pt;text-indent: 0pt;text-align: right;">10,00</p>
            </td>
            <td style="width:137pt;border-top-style:solid;border-top-width:1pt;border-top-color:#CCCCCC;border-left-style:solid;border-left-width:1pt;border-left-color:#CCCCCC;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#CCCCCC;border-right-style:solid;border-right-width:1pt;border-right-color:#CCCCCC" colspan="2" bgcolor="#E8E8E8">
                <p class="s2" style="padding-top: 4pt;padding-right: 3pt;text-indent: 0pt;text-align: right;">660,00</p>
            </td>
        </tr>
    </table>

    <p><br /></p>

    <div id="images_container">
        <div id="image_front" class="image_wrapp">
            <p>Frente</p>
            <img src="<?php echo $customizations['images']['front']; ?>" />
        </div>
        <div id="image_back" class="image_wrapp">
            <p>Costas</p>
            <img src="<?php echo $customizations['images']['back']; ?>" />
        </div>
    </div>

</body>

</html>