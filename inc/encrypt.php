<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// check if class already exists
if( !class_exists('acf_field_tokenfield') ) :

class acf_field_tokenfield extends acf_field {
    
    /*
    *  __construct
    *
    *  This function will setup the field type data
    *
    *  @type	function
    *  @date	5/03/2014
    *  @since	5.0.0
    *
    *  @param	n/a
    *  @return	n/a
    */
    
    function __construct() {

        global $wp_filter;
        
        // vars
        $this->name = 'token';
        $this->label = __("Token",'acf');
        $this->defaults = array(
            'default_value'	=> '',
            'max_tokens'	=> '',
            'placeholder'	=> '',
        );
        
        // do not delete!
        parent::__construct();
    }
    
    /*
    *  render_field()
    *
    *  Create the HTML interface for your field
    *
    *  @param	$field - an array holding all the field's data
    *
    *  @type	action
    *  @since	3.6
    *  @date	23/01/13
    */
    
    function render_field( $field ) {
        
        // vars
        $field['type'] = 'text';
        $field['class'] = 'tokenfield';
        $field['value'] = decrypt($field['value']);
        $atts = array();
        $o = array( 'type', 'id', 'class', 'name', 'value', 'placeholder' );
        $s = array( 'readonly', 'disabled' );
        
        // append atts
        foreach( $o as $k ) {
            $atts[ $k ] = $field[ $k ];	
        }
        
        // append special atts
        foreach( $s as $k ) {
            if( !empty($field[ $k ]) ) $atts[ $k ] = $k;
        }
        
        // render
        $e = '
            <div class="acf-input">
                <input ' . acf_esc_attr( $atts ) . ' />
            </div>
        ';
        
        // return
        echo $e;
    }
    
    /*
    *  render_field_settings()
    *
    *  Create extra options for your field. This is rendered when editing a field.
    *  The value of $field['name'] can be used (like bellow) to save extra data to the $field
    *
    *  @param	$field	- an array holding all the field's data
    *
    *  @type	action
    *  @since	3.6
    *  @date	23/01/13
    */
    
    function render_field_settings( $field ) {
        
        // default_value
        acf_render_field_setting( $field, array(
            'label'			=> __('Default Value','acf'),
            'instructions'	=> __('Appears when creating a new post','acf'),
            'type'			=> 'text',
            'name'			=> 'default_value',
        ));
        
        // placeholder
        acf_render_field_setting( $field, array(
            'label'			=> __('Placeholder Text','acf'),
            'instructions'	=> __('Appears within the input','acf'),
            'type'			=> 'text',
            'name'			=> 'placeholder',
        ));
        
        // maxlength
        acf_render_field_setting( $field, array(
            'label'			=> __('Token Limit','acf'),
            'instructions'	=> __('Leave blank for no limit','acf'),
            'type'			=> 'number',
            'name'			=> 'max_tokens',
        ));
        
    }
    
    /*
    *  format_value()
    *
    *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
    *
    *  @type	filter
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$value (mixed) the value which was loaded from the database
    *  @param	$post_id (mixed) the $post_id from which the value was loaded
    *  @param	$field (array) the field array holding all the field options
    *
    *  @return	$value (mixed) the modified value
    */
    
    function format_value( $value, $post_id, $field ) {
        $value = explode(',', $value);
        if(empty(end($value)))
            array_pop($value);
        return $value;
    }
}

// initialize
new acf_field_tokenfield();

// class_exists check
endif;

/* http://biostall.com/hashing-acf-password-type-fields-in-wordpress/ */
function ns_function_encrypt_passwords( $value, $post_id, $field  )
{
    $value = encrypt( $value );
 
    return $value;
}
add_filter('acf/update_value/type=token', 'ns_function_encrypt_passwords', 10, 3);

?>