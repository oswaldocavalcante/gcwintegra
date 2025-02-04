<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    Gestaoclick
 * @subpackage Gestaoclick/admin
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */

require_once GCW_ABSPATH . 'integrations/woocommerce/class-gcw-wc-products.php';
require_once GCW_ABSPATH . 'integrations/woocommerce/class-gcw-wc-categories.php';
require_once GCW_ABSPATH . 'integrations/woocommerce/class-gcw-wc-attributes.php';
require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-venda.php';

class GCW_Admin 
{
	private $products;
	private $categories;
	private $attributes;

	public function add_woocommerce_integration($integrations)
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'integrations/woocommerce/class-gcw-wc-integration.php';
		$integrations[] = 'GCW_WC_Integration';

		return $integrations;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_style('gcw-admin', plugin_dir_url(__FILE__) . 'assets/css/gestaoclick-admin.css', array(), GCW_VERSION, 'all');
		wp_enqueue_script('gcw-admin', plugin_dir_url( __FILE__ ) . 'assets/js/gestaoclick-admin.js', array( 'jquery' ), GCW_VERSION, false );
		wp_localize_script('gcw-admin', 'gcw_admin_ajax_object', 
			array
			(
				'url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('gcw_nonce')
			)
		);
	}
	
	/**
	 * Register custom fields for settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings()
	{
		register_setting('gcw_credentials', 'gcw-api-access-token', 				'string');
		register_setting('gcw_credentials', 'gcw-api-secret-access-token', 			'string');

		register_setting('gcw_settings', 	'gcw-settings-auto-imports', 			'boolean');
		register_setting('gcw_settings', 	'gcw-settings-categories-selection', 	'array');
		register_setting('gcw_settings', 	'gcw-settings-attributes-selection', 	'array');
		register_setting('gcw_settings', 	'gcw-settings-products-blacklist', 		'array');
		register_setting('gcw_settings', 	'gcw-settings-export-orders', 			'boolean');
		register_setting('gcw_settings', 	'gcw-settings-export-transportadora', 	'string');
		register_setting('gcw_settings', 	'gcw-settings-export-situacao',		 	'string');
	}

	/**
	 * Add the custom menu.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu()
	{
		add_menu_page
		(
			'GestãoClick',
			'GestãoClick',
			'manage_options',
			'gestaoclick',
			array($this, 'display_products'),
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gQ3JlYXRlZCB3aXRoIElua3NjYXBlIChodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy8pIC0tPgoKPHN2ZwogICB3aWR0aD0iMTA0LjgwNTc5bW0iCiAgIGhlaWdodD0iMTM2LjY0MzM3bW0iCiAgIHZpZXdCb3g9IjAgMCAxMDQuODA1NzkgMTM2LjY0MzM3IgogICB2ZXJzaW9uPSIxLjEiCiAgIGlkPSJzdmcxIgogICB4bWw6c3BhY2U9InByZXNlcnZlIgogICBzb2RpcG9kaTpkb2NuYW1lPSJ3b29jbGljay1wbGFuZS5zdmciCiAgIGlua3NjYXBlOnZlcnNpb249IjEuMyAoMGUxNTBlZCwgMjAyMy0wNy0yMSkiCiAgIHhtbG5zOmlua3NjYXBlPSJodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy9uYW1lc3BhY2VzL2lua3NjYXBlIgogICB4bWxuczpzb2RpcG9kaT0iaHR0cDovL3NvZGlwb2RpLnNvdXJjZWZvcmdlLm5ldC9EVEQvc29kaXBvZGktMC5kdGQiCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHNvZGlwb2RpOm5hbWVkdmlldwogICAgIGlkPSJuYW1lZHZpZXcxIgogICAgIHBhZ2Vjb2xvcj0iI2ZmZmZmZiIKICAgICBib3JkZXJjb2xvcj0iIzAwMDAwMCIKICAgICBib3JkZXJvcGFjaXR5PSIwLjI1IgogICAgIGlua3NjYXBlOnNob3dwYWdlc2hhZG93PSIyIgogICAgIGlua3NjYXBlOnBhZ2VvcGFjaXR5PSIwLjAiCiAgICAgaW5rc2NhcGU6cGFnZWNoZWNrZXJib2FyZD0iMCIKICAgICBpbmtzY2FwZTpkZXNrY29sb3I9IiNkMWQxZDEiCiAgICAgaW5rc2NhcGU6ZG9jdW1lbnQtdW5pdHM9Im1tIgogICAgIGlua3NjYXBlOnpvb209IjAuNzc3MzcxMDMiCiAgICAgaW5rc2NhcGU6Y3g9IjI5MS4zNjY2NiIKICAgICBpbmtzY2FwZTpjeT0iMjg4Ljc5Mzg4IgogICAgIGlua3NjYXBlOndpbmRvdy13aWR0aD0iMTUwNCIKICAgICBpbmtzY2FwZTp3aW5kb3ctaGVpZ2h0PSIxMjEyIgogICAgIGlua3NjYXBlOndpbmRvdy14PSIwIgogICAgIGlua3NjYXBlOndpbmRvdy15PSIyNSIKICAgICBpbmtzY2FwZTp3aW5kb3ctbWF4aW1pemVkPSIwIgogICAgIGlua3NjYXBlOmN1cnJlbnQtbGF5ZXI9InN2ZzEiIC8+PGRlZnMKICAgICBpZD0iZGVmczEiIC8+PGcKICAgICBpZD0ibGF5ZXIxIgogICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0yNy44NjA0OTYsLTcyLjA0MTk1KSI+PHBhdGgKICAgICAgIGlkPSJwYXRoMSIKICAgICAgIHN0eWxlPSJmaWxsOiMwMDAwMDAiCiAgICAgICBkPSJtIDc4LjU0MDA4LDcyLjA0MTk1IHYgNC4zNjU2MjUgNC4zNjU2MjUgbCAyLjc1Njk0LDAuMDY2NjYgYyAxLjUxNjIxLDAuMDM2NzYgMi44MTM5NiwwLjEyNDE3MyAyLjg4NDA2LDAuMTk0MzAzIDAuMDcwMSwwLjA3MDE0IDEuNDE5NTQsMC4xMjE4MjIgMi45OTgyNywwLjExNDcyMiBsIDIuODcwMTEsLTAuMDEyOTIgViA3Ny42MzIzMDIgNzQuMTI5MTU3IEwgODUuMzUzMSw3My4xNDM2ODcgQyA4Mi43NzAxMSw3Mi42MDE4NDggODAuMTgwNSw3Mi4xMzI0NSA3OS41OTg0MSw3Mi4xMDAzNCBaIG0gLTAuNjQ4MDIsMC4zNzYyMDQgLTAuNzM0MzIsMC4zOTE3MDggYyAtMC40MDM4MSwwLjIxNTM5NiAtMS4zMjk2NCwwLjc4NzUwOCAtMi4wNTcyNCwxLjI3MTI0IGwgLTEuMzIyOTIsMC44Nzk1MzMgLTAuMDM4Nyw0LjI5NTM0NSBjIC0wLjAyMTQsMi4zNjI0MjMgMC4wMzgxLDQuMjg5OTU5IDAuMTMyMjksNC4yODM0NTkgMC4wOTQyLC0wLjAwNjUgMS4wMDQ0OCwtMC41ODA0MzYgMi4wMjMxMywtMS4yNzUzNzQgbCAxLjg1MjA4LC0xLjI2MzQ4OSAwLjA3MjksLTQuMjkxMjExIHogbSAwLjM4MzQ0LDguOTczNjEzIC0xLjgyNTIxLDEuMjEyMzI5IC0xLjgyNTIxLDEuMjExODEyIGggNS44NTIzNSA1Ljg1MjM2IGwgMS4xODc1MiwtMC44MzcxNTggYyAwLjY1MzAyLC0wLjQ2MDI5MSAxLjEzNTE3LC0wLjkyMDYxNyAxLjA3MTc3LC0xLjAyMzE5MyAtMC4wNjM0LC0wLjEwMjU3NSAtMC44MDIyNSwtMC4xOTczOCAtMS42NDIyOCwtMC4yMTA4NCAtMC44NDAwMywtMC4wMTM0NiAtMy4xMzQ5LC0wLjA5ODM5IC01LjA5OTQzLC0wLjE4ODYxOSB6IG0gLTI1LjQ1ODM5LDIuNjg4NzI1IC0wLjAwNiw1LjgyMDgzMyBjIC0wLjAwNCwzLjIwMTQ1MyAwLjA4NzIsNS44MjA4MzMgMC4yMDIwNSw1LjgyMDgzMyAwLjExNDksMCAxLjc5Nzc5LDAuMTcxNDggMy43Mzk4MiwwLjM4MTM3IDEuOTQyMDMsMC4yMDk5IDQuNDgzNTYsMC40NjQxOCA1LjY0NzcyLDAuNTY0ODMgMS4xNjQxNywwLjEwMDY0IDMuMDA5NjQsMC4yNjk1IDQuMTAxMDQsMC4zNzUxNyAxLjA5MTQxLDAuMTA1NjcgMi4xOTI3NCwwLjIwODUyIDIuNDQ3NCwwLjIyODkyIGwgMC40NjMwMiwwLjAzNzIxIHYgLTUuMjcwNDggLTUuMjcwOTk1IGwgLTAuNzI3NiwtMC4xNTA4OTUgYyAtMC40MDAxOSwtMC4wODMwOCAtMS4xNDQzMywtMC4yMDc2NTkgLTEuNjUzNjUsLTAuMjc2NDY5IC0wLjUwOTMyLC0wLjA2ODgxIC0yLjY1MjQ1LC0wLjQzNTU5OSAtNC43NjI1LC0wLjgxNTQ1NCAtNS42ODMxOSwtMS4wMjMwOTggLTguMjY3NDksLTEuNDQyNzQgLTguODkyNDgsLTEuNDQzODQgeiBtIC0wLjc2MzI2LDAuNTI5MTY2IGMgLTAuMjA1MjUsMCAtMi4yOTA3MDEsMS42MTg4NzggLTIuNTQ3NjUzLDEuOTc3NjU3IC0wLjEwNjkwNywwLjE0OTI4MSAtMC4xOTYyMzgsMi43NDkzNDMgLTAuMTk4NDM4LDUuNzc3OTQzIGwgLTAuMDA0MSw1LjUwNjEyIDEuMjU2NzcxLC0wLjg3NTkxIGMgMC42OTEyMjMsLTAuNDgxOTkgMS4zNDQxNjQsLTAuOTk1NSAxLjQ1MTA3NCwtMS4xNDEwMiAwLjIzMjMzLC0wLjMxNjIzIDAuMjczNTIsLTExLjI0NDc5IDAuMDQyNCwtMTEuMjQ0NzkgeiBtIDAuODY2NjEsMTEuNzk4NzYgYyAtMC4zMjUxOSwwLjAwNzggLTAuNzgyMDIsMC4yMzYwOSAtMS41NDU2NDMsMC43NTAzNSAtMC44NTgzNDIsMC41NzgwMyAtMS4yMzA5MzcsMC45NjczOSAtMC45OTQyNTUsMS4wMzg2OSAwLjIwODE1OSwwLjA2Mjc0IDMuNDE0MzY4LDAuNDAwMiA3LjEyNTE0OCwwLjc0OTgzIDMuNzEwNzgsMC4zNDk2MyA3LjEwNTE3LDAuNjYxNTkgNy41NDMyMSwwLjY5MzQ5IDAuNTMxNjYsMC4wMzg3MyAxLjI3NjUsLTAuMjUwOTIgMi4yNDAxNywtMC44NzE3OCBsIDEuNDQzODQsLTAuOTMwMTcgLTAuNzg0OTYsLTAuMDMzNTkgYyAtMC40MzE2NiwtMC4wMTg1NyAtMS4wMjMwOSwtMC4wNzI1OCAtMS4zMTQxNCwtMC4xMTkzOCAtMC4yOTEwNCwtMC4wNDY4MSAtMS44OTgzOCwtMC4yMDA5NCAtMy41NzE4NywtMC4zNDI2MSAtMS42NzM0OSwtMC4xNDE2NiAtNC4zMjExOCwtMC4zOTU5MSAtNS44ODM4OCwtMC41NjUzNCAtMS41NjI3LC0wLjE2OTQ2IC0yLjk5MTQ1LC0wLjI1NDQyIC0zLjE3NSwtMC4xODg2MiAtMC4xODM1NSwwLjA2NTc3IC0wLjU0MTE2LDAuMDEzMTIgLTAuNzk0NzgsLTAuMTE3MyAtMC4wODU1LC0wLjA0Mzk4IC0wLjE3OTQ0LC0wLjA2NjE4IC0wLjI4Nzg0LC0wLjA2MzU3IHogbSAyMy41NjkxLDQuMDc5ODYyIGMgLTAuMTA5MTQsLTAuMDAyIC0wLjE5ODQ0LDMuMjExMDcgLTAuMTk4NDQsNy4xNDAxMyAwLDQuNTcxNTQgMC4wOTUzLDcuMTQzNzUgMC4yNjQwNyw3LjE0Mzc1IDAuMjc4NDYsMCA4LjE5MDc1LDEuMTYwNzggMTEuMTEzMDIsMS42MzAzOSAwLjk0NTg4LDAuMTUyIDIuNDM0MTYsMC4zNjY5NiAzLjMwNzI5LDAuNDc3NDkgMC44NzMxMiwwLjExMDUzIDEuODU1MzksMC4yMTczIDIuMTgyODEsMC4yMzcyIGwgMC41OTUzMSwwLjAzNjIgdiAtNi45NzU4IC02Ljk3NTI5IGwgLTEuMTI0NDgsLTAuMTY2MzkgYyAtMS4yMzE3MSwtMC4xODI0NCAtMS42MjQyMiwtMC4yNDUxIC0xMC4xMjAzMSwtMS42MDkyMSAtMy4yMDE0NiwtMC41MTQwMSAtNS45MTAxMywtMC45MzY0NCAtNi4wMTkyNywtMC45Mzg0NCB6IG0gLTAuOTkyMTksMC4wMTcgLTEuNzg1OTMsMC44NTczMSBjIC0wLjk4MjI3LDAuNDcxNiAtMS45MzQ3NywwLjk1MDExIC0yLjExNjY3LDEuMDYzNSAtMC4yNTAzLDAuMTU2MDIgLTAuMzMwNzMsMS44MTg0NCAtMC4zMzA3Myw2LjgzNDIxIHYgNi42Mjc1IGwgMC43Mjc2MSwtMC4xNzc3NiBjIDAuNDAwMTgsLTAuMDk3OCAxLjM1MjY4LC0wLjM1NzM0IDIuMTE2NjYsLTAuNTc2NzEgbCAxLjM4OTA2LC0wLjM5ODk0IHYgLTcuMTE0MyB6IG0gLTQwLjc0NTgyOCwxMi4xMDc3OSB2IDcuNzAyMzcgYyAwLDUuOTI4ODMgMC4wNzYxNSw3LjcyNjcxIDAuMzMwNzI5LDcuODA4MzEgMC4xODE5MDEsMC4wNTgzIDEuNTI0MjM0LDAuMTY5MDggMi45ODI3NjQsMC4yNDU5OCAzLjExMTg2NywwLjE2NDEyIDkuMjA5NjM0LDAuNjM5NjkgMTIuMDMyMzQsMC45Mzg0NCAxLjA5MTQwNSwwLjExNTUxIDIuNjQ2MTc1LDAuMjI2MDcgMy40NTUwODUsMC4yNDU0NyBsIDEuNDcwNzEsMC4wMzUxIDAuMDcwMywtNy4yNzYwNCAwLjA2OTgsLTcuMjc2MDUgLTAuNDgyNjYsLTAuMDIxNyBjIC0wLjI2NTIzLC0wLjAxMiAtMC43Nzk4LC0wLjA2NTQgLTEuMTQzNiwtMC4xMTgzNCAtMC42MTg5LC0wLjA5MDEgLTUuOTAyNjA5LC0wLjY5Nzc1IC0xMy44OTA2MjYsLTEuNTk3ODMgLTEuODkxNzY4LC0wLjIxMzE2IC0zLjc2NzAwNiwtMC40NTQ1MSAtNC4xNjcxODgsLTAuNTM2NDEgeiBtIC0wLjU1ODYyMiwwLjU3NTY3IC0xLjAxMDc5MSwwLjUxNTc0IGMgLTAuNTU2MDMsMC4yODM2NiAtMS4wODU0NDYsMC43MDkyOSAtMS4xNzYxNTYsMC45NDU2NyAtMC4wOTA3LDAuMjM2MzggLTAuMTY0ODQ4LDMuNTE1NzggLTAuMTY0ODQ4LDcuMjg3NDEgMCw0LjUzNzU3IDAuMDkyNjcsNi44NTc0NyAwLjI3MzM2OSw2Ljg1NzQ3IDAuMTUwMTk1LDAgMC42ODg2NCwtMC4xNzM3IDEuMTk2ODI2LC0wLjM4NjAzIGwgMC45MjM5NzQsLTAuMzg2MDIgLTAuMDIxMTksLTcuNDE3MTIgeiBtIDQyLjM0NzgsMi4yMjUxOSBjIC0xLjA4ODc1LC0wLjA1NjYgLTEuNTk4MzksMC4wNzgxIC0yLjQ5NDk0LDAuMzU0NSBsIC0xLjU4NDM5LDAuNDg4MzQgMy44MzMzNSwwLjQ4NDczIGMgMi4xMDgyNSwwLjI2NjYxIDUuODU2OSwwLjc1MTg2IDguMzMwNzYsMS4wNzg0OSA1LjUzODgyLDAuNzMxMzEgNS45ODg1NiwwLjc1MDE0IDYuNjMwMDgsMC4yNzgwMiBsIDAuNTEzNjcsLTAuMzc3NzYgLTAuMzk2ODgsLTAuMTQwMDQgYyAtMC4yMTgyOCwtMC4wNzcxIC0xLjI4OTg0LC0wLjI2MyAtMi4zODEyNSwtMC40MTI5IC0xLjA5MTQxLC0wLjE0OTg5IC00LjAwODQ0LC0wLjU4MTE5IC02LjQ4MjI5LC0wLjk1ODU5IC0zLjIxMTgyLC0wLjQ4OTk5IC00Ljg3OTM2LC0wLjczODE4IC01Ljk2ODExLC0wLjc5NDc5IHogbSAzMy4zNTI0OSwwLjA4OTQgdiA3LjY4MzI1IDcuNjgyNzQgbCAxLjY1MzY0LDAuMTUzNDggYyAwLjkwOTUxLDAuMDg0NCA1LjI4NTA1LDAuNDk3NzkgOS43MjM0NCwwLjkxODI5IDQuNDM4MzcsMC40MjA0OSA4LjMzNzY4LDAuNzc3NzUgOC42NjUxLDAuNzkzNzUgbCAwLjU5NTMyLDAuMDI4OSB2IC03LjY3MjkyIC03LjY3MjkyIGggLTAuNDgyMTUgYyAtMC4yNjUxOCwwIC0zLjg2NjgzLC0wLjM0NzgxIC04LjAwMzY0LC0wLjc3MzA4IC00LjEzNjgyLC0wLjQyNTI3IC04LjU2MzMsLTAuODU2MjkgLTkuODM2NjEsLTAuOTU3NTYgeiBtIC0wLjgzNzE2LDAuMzI3MTEgYyAtMC4xNjkzNSwwIC0xLjMzODg4LDAuMTk3NjIgLTIuNTk5MzMsMC40MzkyNSBsIC0yLjI5MTg1LDAuNDM5MjUgLTAuMDQ2NSwxLjY4OTMxIGMgLTAuMDI1NiwwLjkyOTE1IC0wLjE2MzIyLDQuMDk1MDYgLTAuMzA1OTIsNy4wMzUyMyAtMC4xNDI3MSwyLjk0MDE3IC0wLjI2MTU4LDUuNjc1NzQgLTAuMjY0NTgsNi4wNzg2OSBsIC0wLjAwNSwwLjczMjI2IDAuNzI3NiwtMC4xNjIyNyBjIDIuMzA0MTksLTAuNTE0NTcgNC43MjIyMSwtMS4yNzIyNSA0Ljg5MDY2LC0xLjUzMjIgMC4xMDY5NCwtMC4xNjQ5OSAwLjE5NjQzLC0zLjU0NDE3IDAuMTk4NDMsLTcuNTA5NjIgMC4wMDMsLTUuMzA4ODEgLTAuMDc3MSwtNy4yMDk5IC0wLjMwMzg1LC03LjIwOTkgeiBNIDM0LjgxODcyMSwxMjguNzUyNyBjIC0wLjQ1NzYxOCwwLjAzMTUgLTAuODcwMzE5LDAuMTE0NDMgLTEuMjkxOTEsMC4yNTMyMiAtMC42NDY2MjIsMC4yMTI4OCAtMS4xNDEzMjUsMC40MjEyIC0xLjA5OTY3NSwwLjQ2MjUgMC4wNDE2NSwwLjA0MTMgMi41MTYyMzIsMC4xNTgwMyA1LjQ5OTQwNiwwLjI1OTkzIDIuOTgzMTc0LDAuMTAxOSA1LjQ3MzkxNiwwLjIzMTIyIDUuNTM0NTQ2LDAuMjg3MzIgMC4wNjA2MywwLjA1NjIgMS43Mjc1MTEsMC4wOTI2IDMuNzA0MTY3LDAuMDgxMSAxLjk3NjY1NiwtMC4wMTE1IDMuNDc0NTE2LC0wLjA2NDUgMy4zMjg5OTUsLTAuMTE3ODMgLTAuMjQ0NjE2LC0wLjA4OTYgLTQuMjg2NjU0LC0wLjQxNzA2IC0xMC4wNTQxNjYsLTAuODE0OTMgLTEuMzA5Njg2LC0wLjA5MDMgLTMuMTM2MTg4LC0wLjI0OTgyIC00LjA1OTE4NCwtMC4zNTQ1IC0wLjYwMTc4NCwtMC4wNjgzIC0xLjEwNDU2MSwtMC4wODg0IC0xLjU2MjE3OSwtMC4wNTY4IHogbSA3NS4zNTM1MzksMi44MDc1OCBjIC0wLjkyMjM5LDAuMDE1NyAtMS42MjkyOCwwLjE0MjM4IC0yLjUyODAxLDAuMzg3MDYgbCAtMS44NTIwOSwwLjUwNDM2IDEuOTg0MzgsMC4wNiBjIDEuMDkxNCwwLjAzMzIgNC40MjUxNiwwLjEzMzEgNy40MDgzMywwLjIyMjIgdiA1LjJlLTQgYyAyLjk4MzE4LDAuMDg5MSA2LjQ5NTU5LDAuMjAxMiA3LjgwNTIxLDAuMjQ5NiAxLjMwOTYzLDAuMDQ4NCAyLjMyMTEzLDAuMDI4MiAyLjI0NzkzLC0wLjA0NSAtMC4wNzMyLC0wLjA3MzIgLTIuMzk1MDIsLTAuMzIzNSAtNS4xNTkzOCwtMC41NTY1NiAtMi43NjQzNywtMC4yMzMwNSAtNS41MDIzLC0wLjQ3NzgzIC02LjA4NDM4LC0wLjU0MzYzIC0xLjc2MTcsLTAuMTk5MjUgLTIuODk5NiwtMC4yOTQyOSAtMy44MjE5OSwtMC4yNzg1NCB6IG0gLTQxLjU1NDA1LDAuODYwOTMgMC4wMDIsOS44OTQ0OSAwLjAwMSw5Ljg5NSAxLjI1NTIyLDAuMDU2MyBjIDEuNDUwMTMsMC4wNjUgOS4zMjIwNSwwLjUzMDIgMTYuNjAxNTcsMC45ODEzMyAyLjc2NDksMC4xNzEzNSA1LjUzNDg2LDAuMzMxMjIgNi4xNTUxOCwwLjM1NTAyIGwgMS4xMjc1OCwwLjA0MzQgLTAuMDY5MiwtOS4zMTEwNiAtMC4wNjk4LC05LjMxMTA2IC05LjM5MjcxLC0xLjEyNTUxIEMgNzkuMDYzMDcsMTMzLjI4IDczLjQzNzM2LDEzMi42OTQxOSA3MS43Mjc0OSwxMzIuNTk3MzkgWiBtIC0wLjc5Mzc1LDAuMDUzOCAtMS43ODU5NCwwLjA3ODUgLTEuNzg1OTQsMC4wNzggLTAuMDY5Miw5LjUwNzQzIGMgLTAuMDU0Myw3LjQzMDA0IDAuMDAzLDkuNTUzNTUgMC4yNjQ1OCw5LjcxOTMgMC4xODM3NywwLjExNjY1IDEuMDE4NDQsMC4yMTQwMSAxLjg1NTE5LDAuMjE2MDEgbCAxLjUyMTM1LDAuMDA0IHYgLTkuODAxNDcgeiBtIDMwLjIyODY0LDUuMjY3MzcgYyAtMS40MTg4MywtMC4wNDYgLTIuNjM5MTgsLTAuMDQ0MiAtMi43MTE5OCwwLjAwNCAtMC4wNzI4LDAuMDQ4NCAtMC4xOTE3OCw0LjY5MTggLTAuMjY0NTgsMTAuMzE4NzUgbCAtMC4xMzIyOSwxMC4yMzA5IC0xNS44NzUsMC4xMzIyOSAtMTUuODc1LDAuMTMyMjkgLTAuMDY5OCw4LjQwMDUyIGMgLTAuMDUzNyw2LjQ1MzEgLTAuMTQ1NjcsOC40MDExNCAtMC4zOTY4OCw4LjQwNDE0IC0wLjA2NTMsNS44ZS00IC0zLjQyMDI2LDAuMDAzIC00Ljc4MDA3LDAuMDA0IDAuMDczMSwwLjQzNTcgMC4xNDM0OCwwLjg1OTQ0IDAuMjIxMTgsMS4zMDI3NiBsIDcuMzYyODYsMC4wMDMgYyAwLjM3MTE5LC0wLjcwMzk4IDEuMTkxOTUsLTIuMjY4ODQgMS4yNDY0MywtMi4zNjc4MSBsIDAuMDUwNiwtNi4yOTI2NCAwLjA2OTgsLTguNjYwNDUgMTYuODY3MTgsLTAuMDY4MiAxNi44NjcxOSwtMC4wNjc3IHYgLTEwLjY5NjI0IC0xMC42OTU0NyB6IG0gMy4xMDg4NiwwLjMxMzE2IHYgMTAuOTgwMjEgMTAuOTgwMjEgSCA4NC4yOTQ3NyA2Ny40Mjc1OCB2IDguNTMyODEgNC44NzMwOSBjIDAuMjM5NjksLTAuMjQ3MTggMC40OTgxLC0wLjQ0NzA2IDAuNzQ1NywtMC41Njg5NiBsIDAuNDAxLC0wLjE5NzQgaCAwLjUxNTc0IDAuNTE1NzMgbCAwLjM0NTE5LDAuMTY5NSBjIDAuNDU1NTMsMC4yMjM3IDAuODM3NDEsMC42NTYyIDEuMDY3NjQsMS4yMDkyMyAwLjExNzcyLDAuMjgyNzcgMC4zMDM0OCwxLjAyNjgzIDAuNTIwOSwyLjA4MzU5IDAuMzk5MzQsMS45NDEwNCAwLjc0Nzc0LDMuMzcyNTkgMS4yMDQ1OCw0Ljk0OTA1IDAuMzYwNTcsMS4yNDQyNyAxLjA0MDcxLDMuMjc3MTkgMS4yNjkxNywzLjc5NDA5IGwgMC4xNDIxMSwwLjMyMDkxIDAuMDQyOSwtMC40MjMyMyBjIDAuNDU5LC00LjQ5MjQyIDEuMzMyNDcsLTkuMDg5MTUgMi4yNzY4NiwtMTEuOTg0MjkgMC43NTQyNywtMi4zMTIzIDEuNjM4MjUsLTQuMTU4NjQgMi4xOTMxNSwtNC41ODExMSAwLjU0NzM3LC0wLjQxNjc0IDEuMzIwMzMsLTAuNjA1NzkgMS45NTkwNSwtMC40NzkwNCAwLjgzNDM2LDAuMTY1NTUgMS41NzM5MywwLjcyMzQ0IDEuOTE2MTYsMS40NDU5IGwgMC4xOTM3OSwwLjQwOTI4IHYgMC42MDUxMyAwLjYwNTEzIGwgLTAuMzc0NjUsMC43ODY1MiBjIC0xLjI0OTg1LDIuNjI0MTQgLTIuMTE3Nyw2LjA0MDYzIC0yLjg5MDI3LDExLjM3NzA4IC0wLjU0NzEyLDMuNzc5MTcgLTAuNzE3Myw1Ljg4MTI1IC0wLjcyOTE1LDkuMDE2NTEgbCAtMC4wMDksMi40ODA5OCAtMC4xMjUwNSwwLjMzOTUyIGMgLTAuMzQ2MDIsMC45MzcyNCAtMC45NjAyLDEuNDM1NzYgLTEuODMxNDIsMS40ODcyNCAtMC4zMDAxLDAuMDE3NyAtMC40ODg2OCwtMC4wMTAyIC0wLjc4OTA5LC0wLjExNTIzIGggLTUuMmUtNCBjIC0wLjU5NDYsLTAuMjA4MzEgLTAuOTU3MTMsLTAuNDcyNjUgLTEuNjgzNjIsLTEuMjI4MzUgLTEuMzI5NTcsLTEuMzgzMDQgLTIuNTMzODksLTMuMTY0NDcgLTMuNjYxMjcsLTUuNDE1MTggLTAuOTMwOCwtMS44NTgyNCAtMS45MzkzOSwtNC40NTgzMiAtMi40OTQ0MiwtNi40Mjk1OCAtMC4wNjMzLC0wLjIyNDY2IC0wLjEzMDIzLC0wLjQwODI0IC0wLjE0ODMxLC0wLjQwODI0IC0wLjAxODEsMCAtMS4wNzY2OCwyLjA4MzUxIC0yLjM1MjgzLDQuNjMwMjEgLTIuODY1MTQsNS43MTc2NiAtMy40NzMzNSw2Ljc1NDI2IC00LjY2ODk3LDcuOTU5MiAtMC44NjQxMywwLjg3MDg2IC0xLjY1MTM3LDEuMTU5MzUgLTIuMzI2NDcsMC44NTI2NiAtMC4zNDg3NiwtMC4xNTg0NSAtMC44MTg2NCwtMC43MDM4OSAtMS4xMzU4NSwtMS4zMTk4MSAtMC43NzUyNiwtMS41MDUzNSAtMS44Nzg0NywtNS40NTcwNSAtMi45MTE0NSwtMTAuNDI3NzkgLTAuMzg4OTMsLTEuODcxNTggLTAuOTA3ODgsLTQuNjc1IC0xLjM1NDk2LC03LjIxNzEzIGwgLTAuMzA4NSwwLjAwMiAtMTQuNDg1OTQxLDAuMDY4NyAtMC4wNDc1NCwzLjgzMzg3IGMgLTAuMDI2MjcsMi4xMDg4MSAtMC4wNjkwMiw0LjU3ODU0IC0wLjA5NDU3LDUuNDg4MDQgLTAuMjI5MDQ4LDguMTUyNTUgLTAuMzg2NjQ1LDIxLjY4MDE3IC0wLjI1NTI4MSwyMS44OTI3MiB2IDUuMmUtNCBjIDAuMjA0MjQ1LDAuMzMwNDYgMS43MzI1NjksMC4zMzM4MiA3LjU3NzgzMiwwLjAxNjUgMi41MjYzNzQsLTAuMTM3MTMgNi42MTc1ODEsLTAuMzE5MzQgOS4wOTE0MzEsLTAuNDA1MTQgMi40NzM4NiwtMC4wODU4IDUuNTY5NDksLTAuMjA3MjUgNi44NzkxNywtMC4yNjk3NSAxLjMwOTY5LC0wLjA2MjUgNC4xMDc2NiwtMC4xODEyNyA2LjIxNzcxLC0wLjI2NDA3IDIuMTEwMDUsLTAuMDgyOCA2LjE1ODE3LC0wLjI2MTQ5IDguOTk1ODMsLTAuMzk2ODcgMi44Mzc2NSwtMC4xMzUzNyA3LjMwMjUxLC0wLjMxNTM5IDkuOTIxODcsLTAuNDAwNDkgMi42MTkzOCwtMC4wODUxIDUuNjU1NDcsLTAuMjA4NTEgNi43NDY4OCwtMC4yNzQ0MSAxLjA5MTQsLTAuMDY1OSA2LjIxMTEsLTAuMjk3MjYgMTEuMzc3MDc5LC0wLjUxNDE4IDUuMTY1OTksLTAuMjE2OTMgMTAuNzYxOTMsLTAuNDUzODggMTIuNDM1NDIsLTAuNTI2NTggMS42NzM0OSwtMC4wNzI3IDQuNTkwNTIsLTAuMTk0NyA2LjQ4MjI5LC0wLjI3MTMgMS44OTE3NywtMC4wNzY2IDQuNjY0NTYsLTAuMjA4OTQgNi4xNjEzOCwtMC4yOTQwNCBsIDIuNzIxMjgsLTAuMTU1MDMgLTAuMTQ2MjQsLTQuMDA4NTQgYyAtMC4xNTM3MSwtNC4yMTE4NSAtMC41NzQyMSwtMjAuNDAzNzYgLTAuNzk3ODksLTMwLjczMTk3IC0wLjQ1ODA0LC0yMS4xNTAxNSAtMC43MzE5OSwtMjkuNjYyOTkgLTAuOTY0MjgsLTI5Ljk0Mjg4IC0wLjM1MzYzLC0wLjQyNjEgLTQuNTc5NSwtMC43NjYzOSAtMTUuOTcwMDgsLTEuMjg1NzEgLTEuNzQ2MjUsLTAuMDc5NiAtNS4xMzk1NCwtMC4yNTc0MiAtNy41NDA2MywtMC4zOTUzMiAtMi40MDEwOSwtMC4xMzc5MSAtNC43NTI1OCwtMC4yNTIyNSAtNS4yMjU1MiwtMC4yNTQyNSB6IG0gLTczLjI2NzM2NCwxMC4yNTY3NCAtMC4wMTcwNSwxMC40MTU5IC0wLjAxNzA1LDEwLjQxNTkxIDAuODY2MDk3LDAuNzIwMzYgYyAwLjQ3NjIyMywwLjM5NjI2IDAuOTU0ODc4LDAuNzIzNjEgMS4wNjQwMTgsMC43Mjc2MSAwLjEwOTEzNywwLjAwNCAwLjE5ODQzNywtNC45MzM4NyAwLjE5ODQzNywtMTAuOTcyOTcgMCwtNi4wOTgwMiAtMC4xMDI5MzgsLTEwLjk4MDIxIC0wLjIzMTUxLC0xMC45ODAyMSAtMC4xMjczMywwIC0wLjU5ODM0LC0wLjA3MzYgLTEuMDQ2OTY1LC0wLjE2MzMgeiBtIDIuNjQ0MjgzLDAuMjc0OTIgLTAuMDE0NDcsMTEuMTQ1MDYgYyAtMC4wMTE2Myw4Ljc2OTkyIDAuMDU2NjEsMTEuMTY5MDIgMC4zMjAzOTQsMTEuMjU4MjIgMC4xODQzNjgsMC4wNjIzIDQuNTAyMDU2LDAuMjc5OTIgOS41OTUyOCwwLjQ4MzcgNC43MTU2MDcsMC4xODg2NyA5LjU5MTc4MSwwLjMzNzQzIDExLjk0NTAwNiwwLjM3ODc4IC0wLjAzMjcsLTAuMjU0MTggLTAuMDU2MywtMC40NjMwNiAtMC4wNTYzLC0wLjU0ODI4IDAsLTAuNDI5NDMgMC4yMDE1NSwtMS4wMjM2NiAwLjQ3ODAxLC0xLjQwOTc0IDAuNDQyNjQsLTAuNjE4MTQgMS4wMjMxOCwtMC44OTgyOSAxLjk2MDYxLC0wLjk0NjcxIDEuMTEzODMsLTAuMDU3NSAxLjgxODY2LDAuMjk3MjggMi4yMDM0OCwxLjEwNjM5IGwgLTAuMDU3NCwtOC43ODE4OSAtMC4wNjkyLC0xMC42NDk0OCAtMi4yNDg5NiwtMC4xODgxIGMgLTEuMjM2OTIsLTAuMTAzNDQgLTYuMzU2NjE3LC0wLjQ5MDQgLTExLjM3NzA4MSwtMC44NTk5IC01LjAyMDQ2MywtMC4zNjk1IC05LjkyNzEyNywtMC43NDI5MiAtMTAuOTAzNzI4LC0wLjgyOTkyIHogbSA3OS41MTU1NjEsMjIuMjk1OCBjIDAuMzE0OTQsMC4wMDcgMC42MzMyMiwwLjAzMzUgMC45NTM5NCwwLjA4MTEgMi4xNjAzNywwLjMyMDc5IDMuODM0OTIsMS4zNDI1OSA0Ljk1MjY3LDMuMDIyNTUgMS4yNTIwNCwxLjg4MTggMS43OTAzLDQuMDEyMjUgMS43MDQ4MSw2Ljc0NzQgLTAuMDc4OSwyLjUyNTUzIC0wLjU5MDE2LDQuNjEzNTYgLTEuNjQ1MzgsNi43MTc0MSAtMS40MTcyMywyLjgyNTYgLTMuMTA1MTUsNC41MTAyMiAtNS4yNDkyOSw1LjIzOTQ4IC0wLjg0OTEsMC4yODg4IC0yLjE1MzI2LDAuNDI5MDkgLTMuMDU1NjMsMC4zMjg2NiAtMC45NzUzOCwtMC4xMDg1NSAtMi4wNjkyOCwtMC40MzYyNyAtMi44NDk0NCwtMC44NTMxOCAtMi4xODA0NywtMS4xNjUyMiAtMy42MzUxNywtMy42MDU2OSAtNC4wMzc0OCwtNi43NzI3MSAtMC4xMjY2OSwtMC45OTczMyAtMC4wOTQyLC0zLjIzNjk5IDAuMDYxLC00LjIwNTQzIDAuNDY4NjYsLTIuOTI1MzIgMS42NDI2MiwtNS41NzQzMSAzLjM1ODQ1LC03LjU3NzgzIDEuNTYxNDMsLTEuODIzMjQgMy42MDE3MywtMi43NzM2NCA1LjgwNjM3LC0yLjcyNzQ4IHogbSAtMTguNjAzLDAuMDIyNyBjIDEuMDAxMjQsLTAuMDAyIDEuNTUxMSwwLjA4NTMgMi40ODA5OCwwLjM5MTIgMS4xMDA1NSwwLjM2MjAzIDEuODExOSwwLjgwNDE4IDIuNjY2NTEsMS42NTc3OCAxLjA1MDc4LDEuMDQ5NTUgMS43NTExMSwyLjMxMjgxIDIuMTk5MzUsMy45NjcyIDAuNDM5MDgsMS42MjA1NyAwLjUyMDk2LDMuODE5MTkgMC4yMTg1OSw1Ljg3NDU3IC0wLjI5MDA0LDEuOTcxNTQgLTEuMDU5MjQsNC4wOTI3IC0yLjExMDQ3LDUuODIwMzIgLTEuNjY5MzMsMi43NDM0MiAtMy42Nzk0LDQuMTU2NDEgLTYuMjYxNjMsNC40MDEyOCAtMC4zNTUyMiwwLjAzMzcgLTAuNzEzNjQsMC4wNTYyIC0wLjc5Njg1LDAuMDUwNiAtMC4wODMyLC0wLjAwNiAtMC4yNjA2NSwtMC4wMTg5IC0wLjM5Mzc4LC0wLjAzIC0wLjk1MzcyLC0wLjA3OTYgLTIuMTEyMzcsLTAuMzkzMTkgLTIuODgzNTQsLTAuNzgwMzEgLTEuMDA0NjksLTAuNTA0MzYgLTEuOTU1MzIsLTEuMzQxOTkgLTIuNTgwMjEsLTIuMjczNzcgLTEuMDEyMjQsLTEuNTA5MzggLTEuNTM3NjgsLTMuMTU1NzUgLTEuNzExLC01LjM2MTQzIC0wLjIxNjQ3LC0yLjc1NDc5IDAuMzgxNTcsLTUuNzEzNDIgMS42NjI5NCwtOC4yMjk5OCAxLjM5NzIxLC0yLjc0NDAzIDMuMTcwOTYsLTQuNDc5MTYgNS4yNjk5NywtNS4xNTUyNSAwLjgzMjQ4LC0wLjI2ODE1IDEuMjUxMzIsLTAuMzMwNTggMi4yMzkxNCwtMC4zMzIyOCB6IG0gLTM4LjUwNTE0LDQuNDY3OSBjIC0xLjU4NDEyNSwxMGUtNCAtMS41NjYzMjksMC4wMDIgLTMuMzc4MDkxLDAuMDAzIGwgLTEyLjgzMjI5MiwwLjAwOCAwLjc4NjUxNSwwLjY0MDI3IDAuNzg1OTk5LDAuNjM5NzUgMTQuMjI1NDg5LDAuMDA1IGggMC42Mjk5NCBjIC0wLjA4NTUsLTAuNDkwMzUgLTAuMTQwNSwtMC44NDE5MiAtMC4yMTc1NiwtMS4yOTY1NiB6IG0gMzkuMDA2OTIsMC40NDQ0MiAtMC40OTMsMC4wMjY5IGMgLTAuMzg1MzksMC4wMjExIC0wLjU3MjE2LDAuMDY1OCAtMC44NTU3NiwwLjIwNDEyIC0wLjk1MjQ0LDAuNDY0NjMgLTIuMTgzNTEsMi4wODA2OCAtMi44MTQ4MSwzLjY5NTM4IC0wLjg1MjY4LDIuMTgwODggLTAuODY1MTksNC41ODQ3NSAtMC4wMzQxLDYuNTAyNDUgMC42Mjc4NywxLjQ0ODg0IDEuNDk5MjgsMS45ODU0MSAyLjY0NTMyLDEuNjI5MzYgMC43ODIxNiwtMC4yNDMzNyAxLjc2OTU5LC0xLjA4MTU3IDIuMzg0MzUsLTIuMDI0NjkgMC40MDM3OSwtMC42MTk0OCAwLjY3ODMxLC0xLjIwNzQ3IDAuOTI5NjYsLTEuOTkyMTIgMC43ODY5LC0yLjQ1NjU3IDAuNzE1MDgsLTQuNjQzOCAtMC4yMTU1LC02LjU1NzIzIC0wLjMzMywtMC42ODQ3MiAtMC43MDgzNSwtMS4xMDEyOCAtMS4xOTE2NSwtMS4zMjI0IHogbSAxOC4xNTM5MywwLjAxNiBjIC0wLjk5ODQ3LC0wLjAwMyAtMS44NTQzMywwLjYwODk2IC0yLjc4MDIsMS45ODk1NCAtMC41NDk4OSwwLjgxOTk1IC0wLjkxMzk0LDEuNTUyMjUgLTEuMjAyNTEsMi40MjAwMSAtMC4zMDE4NiwwLjkwNzc0IC0wLjQxODY5LDEuNTg1MDkgLTAuNDYwOTUsMi42NjkwOCAtMC4wNDA4LDEuMDQ2NzggMC4wNTgxLDEuNzY4MTQgMC4zNzEwNCwyLjcwMzIgMC42NTYwOCwxLjk2MDY1IDEuNTkwMTIsMi42OTE5MSAyLjg4MTk5LDIuMjU2NzEgMC40MjcyMywtMC4xNDM5MyAwLjcyNDMyLC0wLjMyMDYgMS4yNTU3NCwtMC43NDcyNCAxLjA3MTQ4LC0wLjg2MDIyIDEuOTQ3OTYsLTIuNDkzMzggMi4zNTk1NCwtNC4zOTY2MyAwLjE1Njc4LC0wLjcyNDk1IDAuMjY5ODEsLTIuMDczMjggMC4yMjA2NiwtMi42Mjk4MiAtMC4xMTM0NywtMS4yODUwNyAtMC40ODcyNiwtMi40Nzg3NCAtMS4wMzUwOCwtMy4zMDU3NCAtMC40NDAwNSwtMC42NjQyOSAtMC45MzExMSwtMC45NTY4OSAtMS42MTAyMywtMC45NTkxMSB6IG0gLTc0LjEwOTY4OSwwLjA1NjggYyAtMC4wNDY1MywwIC0wLjA4MTk1LDAuMjY3ODkgLTAuMDc4NTUsMC41OTUzMSAwLjAwMzQsMC4zMjc0MiAtMC4wODg4MSw2LjkzMjY1IC0wLjIwNTE1NiwxNC42NzgxOCBsIC0wLjIxMTg3MywxNC4wODI4NiAwLjc0OTMwOCwxLjA1ODg1IGMgMC40MTIxMDQsMC41ODI0MyAwLjgzODYwNSwxLjA2MTUzIDAuOTQ3NzQ2LDEuMDY0NTMgMC4xMDkxNCwwLjAwMyAwLjIwNDg5LC0yLjUyNDQgMC4yMTIzOSwtNS42MTY3MSAwLjAxMzIsLTUuNDU2NSAwLjE0NjkwMywtMTMuMzY4OTYgMC4zNTQ1LC0yMC45Nzg1NiBsIDAuMTAxMjg2LC0zLjcxNDUxIC0wLjg5MjQ1MiwtMC41ODQ5NyBjIC0wLjQ5MDg1NSwtMC4zMjE3NiAtMC45MzA2NzEsLTAuNTg0OTggLTAuOTc3MjAxLC0wLjU4NDk4IHogbSAyOS4zMTQ0ODksMS4yMDA0NCAtNy4wNjE1OCwwLjAzMzYgYyAwLjQzOTg0LDIuNDc0ODEgMC45Mjk1NSw0Ljk3NyAxLjM2NTI5LDYuOTA3MDggMC41MzA5MywyLjM1MTcgMC41OTg3OSwyLjYzMjgzIDAuNjM1NjIsMi42MzA4NCAwLjAxOTMsLTAuMDAxIDEuNDQ1MTQsLTIuNjkwMjEgMy4xNjgyOCwtNS45NzU4NiAwLjk2NjE2LC0xLjg0MjI3IDEuMjYyODEsLTIuNDAwNTEgMS44OTIzOSwtMy41OTU2NSB6IiAvPjwvZz48L3N2Zz4K',
			56
		);

		add_submenu_page
		( 
			'gestaoclick', 
			'Sincronizar produtos', 
			'Produtos', 
			'manage_options',
			'gestaoclick-products', 
			array($this, 'display_products'),
			1,
		);

		add_submenu_page
		( 
			'gestaoclick', 
			'Sincronizar categorias', 
			'Categorias', 
			'manage_options',
			'gestaoclick-categories', 
			array($this, 'display_categories'),
			2,
		);

		add_submenu_page
		( 
			'gestaoclick', 
			'Sincronizar atributos', 
			'Atributos', 
			'manage_options',
			'gestaoclick-attributes',
			array($this, 'display_attributes'),
			3,
		);

		add_submenu_page
		( 
			'gestaoclick', 
			'Configurações', 
			'Configurações', 
			'manage_options',
			'gestaoclick-settings',
			function() {
				$url = admin_url('admin.php?page=wc-settings&tab=integration&section=gestaoclick');
				wp_redirect($url);
				exit;
			},
			10,
		);
	}

	function register_quote_endpoint()
	{
		add_rewrite_endpoint('orcamentos', EP_PAGES);
	}

	public function create_quote_post_type()
	{
		$labels = array
		(
			'name' 				=> 'Orçamentos',
			'singular_name' 	=> 'Orçamento',
			'add_new' 			=> 'Adicionar novo',
			'add_new_item' 		=> 'Adicionar novo Orçamento',
			'edit_item' 		=> 'Editar Orçamento',
			'new_item' 			=> 'Novo Orçamento',
			'all_items' 		=> 'Orçamentos',
			'view_item' 		=> 'Ver Orçamento',
			'search_items' 		=> 'Buscar Orçamentos',
			'not_found' 		=> 'Nenhum Orçamento encontrado',
			'not_found_in_trash' => 'Nenhum Orçamento encontrado na Lixeira',
			'menu_name' 		=> 'Orçamentos',
		);

		$args = array
		(
			'labels' 		=> $labels,
			'public' 		=> true,
			'has_archive' 	=> true,
			'show_in_menu' 	=> 'gestaoclick',
			'rewrite' 		=> array('slug' => 'orcamentos'),
			'supports' 		=> array('title', 'editor'),
			'menu_position' => 4,
		);

		register_post_type('orcamento', $args);
	}

	/**
	 * Add a post display state for special WC pages in the page list table.
	 *
	 * @param array   $post_states An array of post display states.
	 * @param WP_Post $post        The current post object.
	 */
	public function add_display_post_states($post_states, $post)
	{
		$orcamento_page = get_page_by_path('orcamento');
		$finalizar_orcamento_page = get_page_by_path('finalizar-orcamento');

		if ($orcamento_page && $orcamento_page->ID == $post->ID) {
			$post_states[] = __('Página do orçamento', 'gestaoclick');
		}
		if ($finalizar_orcamento_page && $finalizar_orcamento_page->ID == $post->ID) {
			$post_states[] = __('Página de finalização do orçamento', 'gestaoclick');
		}

		return $post_states;
	}

	/**
	 * Return the products page.
	 *
	 * @since    1.0.0
	 */
	public function display_products()
	{
		$this->products = new GCW_WC_Products();

		if($this->products::test_connection()) {
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/gcw-admin-page-products.php';
		} 
		else {
			wp_admin_notice(__('GestãoClick: configure suas credenciais de acesso.', 'gestaoclick'), array('type' => 'error', 'dismissible' => true));
		}
	}

	/**
	 * Return the categories page.
	 *
	 * @since    1.0.0
	 */
	public function display_categories()
	{
		$this->categories = new GCW_WC_Categories();

		if($this->categories::test_connection()) {
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/gcw-admin-page-categories.php';
		} 
		else {
			wp_admin_notice(__('GestãoClick: configure suas credenciais de acesso.', 'gestaoclick'), array('type' => 'error', 'dismissible' => true));
		}
	}

	/**
	 * Return the attributes page.
	 *
	 * @since    1.0.0
	 */
	public function display_attributes()
	{
		$this->attributes = new GCW_WC_Attributes();
		
		if($this->attributes::test_connection()) {
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/gcw-admin-page-attributes.php';
		} 
		else {
			wp_admin_notice(__('GestãoClick: configure suas credenciais de acesso.', 'gestaoclick'), array('type' => 'error', 'dismissible' => true));
		}
	}

	/**
	 * Execute the importations of categories and products by cron schdeduled event.
	 * 
	 * @since    1.0.0
	 */
    public function import_all()
	{
		$this->categories = new GCW_WC_Categories();
		$this->categories->import('all');

		$this->products = new GCW_WC_Products();
		$this->products->import('all');

		update_option('gcw_gestaoclick_last_import', current_time('d/m/Y, H:i'));
    }

	public function export_order($order_id) 
	{
		$gc_venda = new GCW_GC_Venda($order_id);
		$gc_venda->export();
	}

	public function ajax_gcw_nfe($order_id)
	{
		if (isset($_POST['security']) && check_ajax_referer('gcw_nonce', 'security'))
		{
			$order_id = $_POST['order_id'];
			$order = wc_get_order($order_id);
			$redirect_url = 'https://gestaoclick.com/notas_fiscais/';

			if ($order->meta_exists('gcw_gc_venda_nfe_id'))
			{
				$nota_fiscal_id = $order->get_meta('gcw_gc_venda_nfe_id');
				$redirect_url .= 'index?id=' . $nota_fiscal_id;
			}
			else
			{
				$gc_venda = new GCW_GC_Venda($order_id);
				$gc_venda_data = $gc_venda->get();

				if(is_wp_error($gc_venda_data))
				{
					wp_send_json( array
						(
							'success' => false,
							'data' => $gc_venda_data,
							'message' => 'Nenhuma venda encontrada para este pedido.'
						)
					);
				}

				$nota_fiscal_id = $gc_venda_data['nota_fiscal_id'];

				if($nota_fiscal_id)
				{
					$order->add_meta_data('gcw_gc_venda_nfe_id', $nota_fiscal_id);
					$redirect_url .= 'index?id=' . $nota_fiscal_id;
					$order->save();
				}
				else 
				{
					$gc_venda_hash = $order->get_meta('gcw_gc_venda_hash');
					$redirect_url .= 'adicionar/venda:' . $gc_venda_hash;
				}	
			}

			wp_send_json_success($redirect_url);
		}
	}
}
