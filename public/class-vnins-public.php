<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://localhost/test.com
 * @since      1.0.0
 *
 * @package    Vnins
 * @subpackage Vnins/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Vnins
 * @subpackage Vnins/public
 * @author     noname <abakan_ac545@mail.ru>
 */
class Vnins_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
        add_filter( 'woocommerce_states', array($this, 'vnis_states_as_institute') );
        add_action("woocommerce_before_shipping_calculator", function (){
            //WC()->customer->set_shipping_country("");
        });

    }
    public function vnis_states_as_institute( $states ) {
        global $wpdb;

        $data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}" . VNINS_DBTN);

        $institutes1 = array_map(function ($institute) {
            return $institute->name;
        }, $data);
        $states[''] = array();
        foreach ($institutes1 as $value){
            $states['CISO'][$value] =  $value;
        };
        return $states;
    }
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Vnins_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Vnins_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/vnins-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Vnins_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Vnins_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/vnins-public.js', array( 'jquery' ), $this->version, true );

	}

}
