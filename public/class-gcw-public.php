<?php

require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-orcamento.php';
require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-cliente.php';
require_once GCW_ABSPATH . 'public/views/class-gcw-shortcode-orcamento.php';

class GCW_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'assets/css/gestaoclick-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'assets/js/gestaoclick-public.js', array('jquery'), $this->version, false);
	}

	/**
	 * Insert the Orçamento form in its shortcode place.
	 *
	 * @since    1.0.0
	 */
	public function shortcode_orcamento()
	{
		if (isset($_POST['gcw_nonce_orcamento']) && wp_verify_nonce($_POST['gcw_nonce_orcamento'], 'gcw_form_orcamento')) {
			$gc_cliente = new GCW_GC_Cliente($_POST, 'form');
			$gc_cliente->export();

			$gc_orcamento = new GCW_GC_Orcamento($_POST, $gc_cliente->get_id(), 'form');
			$gc_orcamento->export();
		}

		$orcamento = new GCW_Shortcode_Orcamento();

		return $orcamento->render_form();
	}
}
