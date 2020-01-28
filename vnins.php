<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://localhost/test.com
 * @since             1.0.0
 * @package           Vnins
 *
 * @wordpress-plugin
 * Plugin Name:       vn_institute
 * Plugin URI:        http://localhost/test.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            noname
 * Author URI:        http://localhost/test.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       vnins
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'VNINS_VERSION', '1.0.0' );
define("VNINS_DBTN", "tablename");

function vn_kg($weight){
    $view = '';
    $g = wc_get_weight( $weight-floor($weight), 'g' );
    $kg = wc_get_weight(floor($weight), 'kg' );
    $view .= $kg !== (float)0 ? "$kg кг" : '';
    $view .= $g !== (float)0 ? " $g г" : '';
    return $view;

}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-vnins-activator.php
 */
function activate_vnins() {	
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vnins-activator.php';
	Vnins_Activator::activate(VNINS_DBTN);
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-vnins-deactivator.php
 */
function deactivate_vnins() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vnins-deactivator.php';
	Vnins_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_vnins' );
register_deactivation_hook( __FILE__, 'deactivate_vnins' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-vnins.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_vnins() {

	$plugin = new Vnins();
	$plugin->run();

}

add_action("init", "run_vnins");

add_filter( 'woocommerce_countries',  "create_countries");
add_filter( 'woocommerce_continents', "create_continents" );

function create_continents($continents)
{
    $continents['ASIA']['countries'][] = 'VSI';
    $continents['ASIA']['countries'][] = 'VNS';
    return $continents;
}

function create_countries($countries)
{
    $new_countries = array(
        'VSI' => __('СИЗО', 'woocommerce'),
        'VNS' => __('Не СИЗО', 'woocommerce')
    );

    return array_merge($countries, $new_countries);
}



// Убрать из корзины город и почтовый индекс
add_filter( 'woocommerce_shipping_calculator_enable_postcode', '__return_false' );
add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_false' );

add_filter( 'woocommerce_cart_shipping_method_full_label', 'filter_function_name_1136111', 10, 2 );
function filter_function_name_1136111( $label, $method ){
    
    $label     = $method->get_label();
    $has_cost  = 0 < $method->cost;
    $hide_cost = ! $has_cost && in_array( $method->get_method_id(), array( 'free_shipping', 'local_pickup' ), true );

    if ( $has_cost && ! $hide_cost ) {
        if ( WC()->cart->display_prices_including_tax() ) {
            $label .= ': ' . wc_price( $method->cost + $method->get_shipping_tax() );
            if ( $method->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
                $label .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
            }
        } else {
            $label .= ': ' . wc_price( $method->cost );
            if ( $method->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
                $label .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
            }
        }
    }
    return $label;
}