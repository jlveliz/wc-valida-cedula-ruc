<?php
/*
Plugin Name: Valida Cédula o Ruc
Plugin URI: http://thejlmedia.com
Description: Añade y valida un campo  de cédula o Ruc en el formulario de checkout de Woocommerce.
Version: 1.0
Author: Jorge Veliz
Author URI: http://thejlmedia.com
License: GPL2
*/


/**
 * AÑADIMOS LOS ASSETS
 */

 
if(!function_exists('jl_load_assets')) { 
    function jl_load_assets() {
        wp_enqueue_style('jl_valida_cedula_app.css',plugin_dir_url(__FILE__).'assets/jl_valida_cedula_app.css');
        wp_enqueue_script('jl_valida_cedula_app.js', plugin_dir_url(__FILE__).'assets/jl_valida_cedula_app.js',[], false ,true);
        // wp_enqueue_script( 'jquery');
        // wp_enqueue_script('script_fancy_box',plugin_dir_url(__FILE__).'assets/js/fany_box.min.js',['jquery'], false ,true);
    }
}


add_action("wp_enqueue_scripts", 'jl_load_assets');

/**
 * AÑADIMOS LOS CAMPOS
 */


if (!function_exists('jl_woocommerce_checkout_add_document_type')) {
    
    function jl_woocommerce_checkout_add_document_type($checkout) {
        
        woocommerce_form_field('document_type', [
                'type' => 'select',
                'label'     => __('Tipo de Identificación', 'woocommerce'),
                'placeholder'   => _x('Field Value', 'placeholder', 'woocommerce'),
                'required'  => true,
                'class' => ['select-field','form-row-wide'],
                'options' => [
                    'cedula' => 'Cédula',
                    'ruc' => 'Ruc'
                ]
            ], $checkout->get_value('document_type') 
        );


        woocommerce_form_field('cedula', [
            'type' => 'text',
            'label'     => __('Cédula', 'woocommerce'),
            'placeholder'   => _x('Ingrese la cédula', 'placeholder', 'woocommerce'),
            'required'  => true,
            'id' => 'jl-field-cedula',
            'maxlength' => 10,
            'class' => ['form-row-wide']
        ], $checkout->get_value('cedula') 
        );

       

        woocommerce_form_field('ruc', [
            'type' => 'text',
            'label'     => __('Ruc', 'woocommerce'),
            'placeholder'   => _x('Ingrese el Ruc', 'placeholder', 'woocommerce'),
            'required'  => true,
            'id' => 'jl-field-ruc',
            'maxlength' => 13,
            'class' => ['form-row-wide','no-display']
        ], $checkout->get_value('ruc') 
        );
    }

}

add_filter( 'woocommerce_before_checkout_billing_form' , 'jl_woocommerce_checkout_add_document_type' );


/**
 * PROCESA FORMULARIO
 */


if (!function_exists('jl_process_form_checkout')) {
 
    function jl_process_form_checkout() {
        
        require_once __DIR__."/valida-identificacion.php";
        
        $validator = new ValidarIdentificacion;
        
       
        if ($_POST['document_type'] == 'cedula') {
            
            //verifica si la cedula no está vacía
            if(empty($_POST['cedula'])) {
                wc_add_notice( 'Por Favor Ingrese la cédula', 'error' );
            }


            //procesa si la cedula es válida
            $isValid = $validator->validarCedula($_POST['cedula']);

            if (!$isValid ) {
                wc_add_notice( 'Por Favor Ingrese una cédula válida', 'error' );
            }

        } elseif($_POST['document_type'] == 'ruc') {

            //verifica si el ruc no está vacío
            if(empty($_POST['ruc'])) {
                wc_add_notice( 'Por Favor Ingrese el Ruc', 'error' );
            }

            //procesa si el ruc es válido
            $isValid = $validator->validarRucPersonaNatural($_POST['ruc']) || $validator->validarRucSociedadPrivada($_POST['ruc']) || $validator->validarRucSociedadPublica($_POST['ruc']);

            if (!$isValid ) {
                wc_add_notice( 'Por Favor Ingrese un Ruc válido', 'error' );
            }


        } else {
            wc_add_notice( 'Ingrese un tipo de documento válido', 'error' );
        }

    }

}

add_action('woocommerce_checkout_process', 'jl_process_form_checkout');


/**
 * Guarda Custom field in DB
 */

if(!function_exists('jl_update_custom_field_order_meta')) {
    function jl_update_custom_field_order_meta( $order_id ) {
        
        if ( ! empty( $_POST['document_type'] ) ) {
            update_post_meta( $order_id, 'document_type', sanitize_text_field( $_POST['document_type'] ) );
        }

        if ( ! empty( $_POST['cedula'] ) ) { 
            update_post_meta( $order_id, 'cedula', sanitize_text_field( $_POST['cedula'] ) );
        }

        if ( ! empty( $_POST['ruc'] ) ) { 
            update_post_meta( $order_id, 'ruc', sanitize_text_field( $_POST['ruc'] ) );
        }
    }
}

add_action( 'woocommerce_checkout_update_order_meta', 'jl_update_custom_field_order_meta' );




/**
 * Muestra la orden en el edit
 */


if(!function_exists('')) {

    function jl_document_display_admin_order_meta($order){

        if(get_post_meta( $order->get_id(), 'document_type', true )) {
            echo '<p><strong>'.__('Tipo de Identificacion').':</strong> <br/>' . get_post_meta( $order->get_id(), 'document_type', true ) . '</p>';
            if(get_post_meta( $order->get_id(), 'document_type', true ) == 'ruc') {
                echo '<p><strong>'.__('Ruc').':</strong> <br/>' . get_post_meta( $order->get_id(), 'ruc', true ) . '</p>';
            } else {
                echo '<p><strong>'.__('Cedula').':</strong> <br/>' . get_post_meta( $order->get_id(), 'cedula', true ) . '</p>';
            }
        }

    }

}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'jl_document_display_admin_order_meta', 10, 1 );


/**		
* Envia los campos personalizados por correo
**/
function jl_woocommerce_custom_email_order_meta_fields_documents($array, $sent_to_admin, $order) {

    if(get_post_meta( $order->get_id(), 'document_type', true )) {

       
        $array['document_type'] =  [
            'label' => 'Tipo de Identificacion',
            'value' => get_post_meta( $order->get_id(), 'document_type', true )
        ];

        if(get_post_meta( $order->get_id(), 'document_type', true ) == 'ruc') { 
            $array['ruc'] = [
                'label' => 'Ruc',
                'value' => get_post_meta( $order->get_id(), 'ruc', true )
                
            ];
        } else {
            $array['cedula'] = [
                'label' => 'Cedula',
                'value' => get_post_meta( $order->get_id(), 'cedula', true )
                
            ];
        }
            

        return $array;

    }
}
add_filter('woocommerce_email_order_meta_fields','jl_woocommerce_custom_email_order_meta_fields_documents',10,3);




/** 
 * Muestra los datos en la pagina de orden recibida 
 */


if(!function_exists('jl_thankyou_order_received')) {
    
    function jl_thankyou_order_received($order_id) {
        $order = wc_get_order($order_id);
        if(get_post_meta( $order_id, 'document_type', true )) {
            echo '<li>'.__('Tipo de Identificacion').':<strong> ' . get_post_meta( $order_id, 'document_type', true ) . '</strong></li>';
            if(get_post_meta( $order_id, 'document_type', true ) == 'ruc') {
                echo '<li>'.__('Ruc').': <strong>' . get_post_meta( $order_id, 'ruc', true ) . '</strong></li>';
            } else {
                echo '<li>'.__('Cedula').':<strong> ' . get_post_meta( $order_id, 'cedula', true ) . '<strong></li>';
            }
        }
    }

}

add_action('woocommerce_thankyou','jl_thankyou_order_received');