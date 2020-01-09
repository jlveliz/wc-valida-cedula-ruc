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



