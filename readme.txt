=== GCW Integra - GestãoClick for WooCommerce ===
Contributors: oswaldocavalcante
Donate link: https://oswaldocavalcante.com/donation
Tags: woocommerce, gestaoclick, erp
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 3.5.5
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.4

Integrate the GestãoClick ERP with WooCommerce.

== Description ==

GCW Integra is a WordPress plugin that integrates the GestãoClick ERP with WooCommerce.

**Features**

***Integration with GestãoClick API***

Allows you to configure the access credentials for the GestãoClick API to synchronize data.

***Data Import***

- **Auto-import**: Enable auto-import to periodically sync WooCommerce with GestãoClick every 15 minutes.
- **Category Selection**: Select the categories to import products from GestãoClick.
- **Blocked Products**: List product codes that should not be imported from GestãoClick.

***Data Export***

- **Auto-export Sales**: Enable auto-export to send new paid sales and their respective customers to GestãoClick.
- **Default Status**: Set the default status for new sales exported to GestãoClick.
- **Default Carrier**: Select the default carrier for new sales exported to GestãoClick.

***Shipping Calculator***

Enable the shipping calculator to appear on individual product and quote pages.

***Quotations***

Additional quotation module for products without stock control in GestãoClick.

== Installation ==

1. Go to WooCommerce -> Settings -> Integration -> GestãoClick e your API Credentials obtained from [https://gestaoclick.com/integracao_api/configuracoes/gerar_token](https://gestaoclick.com/integracao_api/configuracoes/gerar_token).
2. Select the categories you wish to import, and enable the auto-import or faça uma importação manual. until the client inserts his address for delivery.

== External services ==

This plugin connects to GestãoClick API to obtain information. It's needed to sync clients, products, categories and attributes for WooCommerce.

It sends your GestãoClick API Credentials and every time that a request is made. To exports orders, this plugin also sends customers and its order informations.

This external service is provided by "CLICK DIGITAL SOLUTIONS LTDA": [terms of use](https://gestaoclick.com.br/termos/), [privacy policy](https://gestaoclick.com.br/politicas-de-privacidade/).

== Frequently Asked Questions ==

= What is GestãoClick? =

GestãoClick is an online ERP system that manages all management tasks simply and quickly.

= Where do I get my API credentials? =

You can obtain your access credentials [here](https://gestaoclick.com/integracao_api/configuracoes/gerar_token).