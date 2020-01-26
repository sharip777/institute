<?php
if (!defined('ABSPATH')) {
    exit;
}

function vn_parcel_init()
{
    if (!class_exists('Vn_Shipping_Parcel_Method')) {
        class Vn_Shipping_Parcel_Method extends WC_Shipping_Method
        {

            public function __construct($instance_id = 0)
            {
                parent::__construct($instance_id);
                $this->id = 'vn_parcel';
                $this->method_title = "Посылка";
                $this->method_description = "Отправка по почте. Максимальный вес посылки 20 кг";
                $this->availability = "including";
                $this->countries = array("VNS");
                $this->init();

                $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : "Посылка";
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
                        'default' 		=> 'Посылка (20 кг)'
                    ),
                    'mass' => array(
                        'title' 		=> "Максимальный вес",
                        'type' 			=> 'number',
                        'description' => "Максимальный вес посылки",
                        'label' 		=> "кг",
                        'default' 		=>  20
                    ),

                    'cost' => array(
                        'title' 		=> "Стоимость доставки",
                        'type' 			=> 'number',
                        'description' => "Стоимость одной посылки",
                        'label' 		=> "тг",
                        'default' 		=>  5000
                    ),
                );
            }

            public function calculate_shipping($package = array())
            {
                $cost =  isset( $this->settings['cost']) ? $this->settings['cost'] : 5000;
                $this->add_rate( array(
                    'id'    => $this->id,
                    'label' => $this->title,
                    'cost'  => $cost,
                ) );
            }
        }
    }
}

add_action("woocommerce_shipping_init", "vn_parcel_init");
function vn_parcel_add($methods)
{
    $methods['vn_parcel'] = 'Vn_Shipping_Parcel_Method';
    return $methods;
}

add_filter('woocommerce_shipping_methods', 'vn_parcel_add');





function vn_parcel_display_cart( $message )   {

    $packages = WC()->shipping->get_packages();


    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    if( is_array( $chosen_methods ) && in_array( 'vn_parcel', $chosen_methods ) ) {

        foreach ( $packages as $i => $package ) {

            if ( $chosen_methods[ $i ] != "vn_parcel" ) {

                continue;

            }

            $Vn_Shipping_Parcel_Method = new Vn_Shipping_Parcel_Method();
            $weightLimit = (int) $Vn_Shipping_Parcel_Method->settings['mass'];
            $weight = 0;

            foreach ( $package['contents'] as $item_id => $values )
            {
                $_product = $values['data'];
                $weight = $weight + $_product->get_weight() * $values['quantity'];
            }

            $weight = wc_get_weight( $weight, 'kg' );

            if( $weight > $weightLimit ) {
                $message = sprintf( __( 'Извините, %s превышает максимально допустимый вес %s кг  %s', 'vn_parcel' ), vn_kg($weight), $weightLimit, $Vn_Shipping_Parcel_Method->title );


                $messageType = "error";

                if( ! wc_has_notice( $message, $messageType ) ) {

                    wc_print_notice( $message, $messageType );

                }
            }else{
                $ost = $weightLimit-$weight;
                $message = sprintf( __( 'Общий вес %s, осталось %s', 'vn_parcel' ), vn_kg($weight), vn_kg($ost), $Vn_Shipping_Parcel_Method->title );

                $messageType = "notice";

                if( ! wc_has_notice( $message, $messageType ) ) {

                    wc_print_notice( $message, $messageType );

                }
            }
        }
    }
}
add_action( 'woocommerce_before_cart', 'vn_parcel_display_cart' );


function vn_parcel_display_order( $posted )   {

    $packages = WC()->shipping->get_packages();

    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

    if( is_array( $chosen_methods ) && in_array( 'vn_parcel', $chosen_methods ) ) {

        foreach ( $packages as $i => $package ) {

            if ( $chosen_methods[ $i ] != "vn_parcel" ) {

                continue;

            }

            $Vn_Shipping_Parcel_Method= new Vn_Shipping_Parcel_Method();
            $weightLimit = (float) $Vn_Shipping_Parcel_Method->settings['mass'];
            $weight = 0;

            foreach ( $package['contents'] as $item_id => $values )
            {
                $_product = $values['data'];
                $weight = $weight + $_product->get_weight() * $values['quantity'];
            }

            if( $weight > $weightLimit ) {

                $message = sprintf( __( 'Извините, %s превышает максимально допустимый вес %s кг  %s', 'vn_parcel' ), vn_kg($weight), $weightLimit, $Vn_Shipping_Parcel_Method->title );

                $messageType = "error";

                if( ! wc_has_notice( $message, $messageType ) ) {

                    wc_add_notice( $message, $messageType );
                }

            }else{

                $ost = $weightLimit - $weight;
                $message = sprintf( __( 'Общий вес %s, осталось %s', 'vn_parcel' ), vn_kg($weight), vn_kg($ost), $Vn_Shipping_Parcel_Method->title );

                $messageType = "notice";

                if( ! wc_has_notice( $message, $messageType ) ) {

                    wc_add_notice( $message, $messageType );
                }
            }
        }
    }
}

add_action( 'woocommerce_review_order_before_cart_contents', 'vn_parcel_display_order' , 10 );
add_action( 'woocommerce_after_checkout_validation', 'vn_parcel_display_order' , 10 );