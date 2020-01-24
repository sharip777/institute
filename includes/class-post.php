<?php
	
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
};

function vn_post_init(){
	if (!class_exists('Vn_Shipping_Post_Method')) {
		# code...
		class Vn_Shipping_Post_Method extends WC_Shipping_Method{
			public function __construct($instance_id= 0 ){
				parent::__construct($instance_id);
				$this -> id = "vn_post";

				$this -> method_title = "Бандероль";
				$this ->method_description = "Отправка по почте";
				$this -> init_post();
				$this ->enabled =isset($this -> settings["enabled"]) ? $this -> settings["enabled"] : "yes";
				$this ->title =isset($this -> settings["title"])
                    ? $this -> settings["title"] . " " . $this -> settings['mass'] . "кг"
                    : "Бандероль $this->settings['mass'] кг";

			}
			public function init_post(){
				$this -> init_form_fields();
				$this -> init_settings();
				add_action("woocommerce_update_options_shipping_" . $this -> id, array($this , "process_admin_options"));
			}
			public function init_form_fields(){
				$this ->form_fields= array(
						'enabled' => array(
							'title' => "Включить",
							'type'=> 'checkbox',
							'label' => 'Включить метод доставки',
							'default' => 'yes'



						),
						'title' => array(
							'title' => "Бандероль ",
							'type'=> 'text',
							'label' => 'post',
							'default' => "Бандероль"


						),
						'mass' => array(
							'title' => "Максимальный вес",
							'type'=> 'number',
							'label' => 'кг',

							'default' => 30


						),
						'cost' => array(
							'title' => "Стоимость доставки",
							'type'=> 'number',
							'label' => 'Стоимость одной посылки',

							'default' => 2300


						),


				);
			}
			public function calculate_shipping($package=array()){
				$cost = isset($this -> settings['cost']) ? $this -> settings["cost"] : 2300 ;
				$this -> add_rate(
					array(
						'id' => $this -> id,
						'label' => $this -> title,
						'cost' => $cost 
					)
				);
			}

		}
	}

}

add_action( "woocommerce_shipping_init", "vn_post_init");




function vn_post_add($m){
	$m['vn_post'] = "Vn_Shipping_Post_Method";
	return $m;
};
add_filter("woocommerce_shipping_methods", "vn_post_add");



function vn_post_validate_order( $posted )   {

    $packages = WC()->shipping->get_packages();

    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

    if( is_array( $chosen_methods ) && in_array( 'vn_post', $chosen_methods ) ) {

        foreach ( $packages as $i => $package ) {

            if ( $chosen_methods[ $i ] != "vn_post" ) {

                continue;

            }

            $Vn_Shipping_Post_Method = new Vn_Shipping_Post_Method();
            $weightLimit = (int) $Vn_Shipping_Post_Method->settings['weight'];
            $weight = 0;

            foreach ( $package['contents'] as $item_id => $values )
            {
                $_product = $values['data'];
                $weight = $weight + $_product->get_weight() * $values['quantity'];
            }

            $weight = wc_get_weight( $weight, 'kg' );

            if( $weight > $weightLimit ) {

                $message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', 'tutsplus' ), $weight, $weightLimit, $Vn_Shipping_Post_Method->title );
                $message .= " ****TUPLUS****";
                $messageType = "error";

                if( ! wc_has_notice( $message, $messageType ) ) {

                    wc_add_notice( $message, $messageType );

                }
            }
        }
    }
}

add_action( 'woocommerce_review_order_before_cart_contents', 'vn_post_validate_order' , 10 );
add_action( 'woocommerce_after_checkout_validation', 'vn_post_validate_order' , 10 );