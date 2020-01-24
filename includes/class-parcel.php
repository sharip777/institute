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
                        'default' 		=> 'Посылка'
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

