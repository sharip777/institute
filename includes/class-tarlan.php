<?php
if (!defined('ABSPATH')) {
    exit;
}

function vn_tarlan_init()
{
    if (!class_exists('Vn_Shipping_Tarlan_Method')) {
        class Vn_Shipping_Tarlan_Method extends WC_Shipping_Method
        {

            public function __construct($instance_id = 0)
            {
                parent::__construct($instance_id);
                $this->id = 'vn_tarlan';
                $this->method_title = "Заказное Письмо";
                $this->method_description = "Отправка по почте. Максимальное количество 20 карт";
                $this->availability = "including";
                $this->countries = array("VNS");
                $this->init();

                $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : "Заказное Письмо";
            }

            public function init()
            {
                $this->init_form_fields();
                $this->init_settings();

                // Save settings in admin if you have any defined
                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }

            public function init_form_fields()
            {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' 		=> "Включить",
                        'type' 			=> 'checkbox',
                        'label' 		=> "Включить метод доставки",
                        'default' 		=> 'yes'
                    ),
                    'title' => array(
                        'title' 		=> "Название метода",
                        'type' 			=> 'text',
                        'label' 		=> "Сменить название",
                        'default' 		=> 'Заказное Письмо'
                    ),

                    'cost' => array(
                        'title' 		=> "Стоимость доставки",
                        'type' 			=> 'number',
                        'description' => "Стоимость одного письма",
                        'label' 		=> "тг",
                        'default' 		=>  450
                    ),
                );
            }

            public function calculate_shipping($package = array())
            {
                //floor
                $count = (int) 0;
                foreach ( $package['contents'] as $item_id => $values )
                {
                    $_product = $values['data'];
                    $count = (int) ( $count + $values['quantity'] );
                }
                $cost =  isset( $this->settings['cost']) ? $this->settings['cost'] : 450;
                $count = (float) $count;
                $coef = (float) $count/20;
                $cost = floor($coef) == $coef ? $coef * $cost : (floor($coef) + 1) * $cost;
                
                $this->add_rate( array(
                    'id'    => $this->id,
                    'label' => $this->title,
                    'cost'  => $cost ,
                ) );
            }
        }
    }
}

add_action("woocommerce_shipping_init", "vn_tarlan_init");
function vn_tarlan_add($methods)
{
    $methods['vn_tarlan'] = 'Vn_Shipping_Tarlan_Method';
    return $methods;
}

add_filter('woocommerce_shipping_methods', 'vn_tarlan_add');

/*
 * Hide shipping rates when free shipping is available.
 * Updated to support WooCommerce 2.6 Shipping Zones.
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */
function woo_cart_has_product_no_tarlan() {

    foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
        $_product = $values['data'];        
        $categories =  array_map( strip_tags, explode(",", $_product ->get_categories()) );
        if (! in_array('Телефонные карты', $categories)) {
            return true;
        }       
    }
    return false;
}
function vn_is_no_sizo()
{
    $country_id = WC()->customer->get_shipping_country();
    return $country_id == 'VNS';
}
function vn_hide_shipping_for_tarlan( $rates ){
    if ( (! woo_cart_has_product_no_tarlan() ) && vn_is_no_sizo() ) {
        return array("vn_tarlan"  => $rates["vn_tarlan"]);
    }
    $rates_unset_tarlan = array_filter($rates, function ($value)
    {
        return $value != "vn_tarlan";
    }, ARRAY_FILTER_USE_KEY);
    return $rates_unset_tarlan;
}

add_filter( 'woocommerce_package_rates', 'vn_hide_shipping_for_tarlan', 100 );