<?php

add_action('acf/render_field_settings/type=text', 'render_field_settings', 10, 3);
add_action('acf/update_value', 'update_value', 10, 3);
add_action('acf/load_value', 'load_value', 10, 3);
add_filter('acf/prepare_field', 'prepare_field', 10, 3);

function render_field_settings($field){
    acf_render_field_setting( $field, [
        'label'         => __('Encrypt Field?'),
        'instructions'  => '',
        'name'          => '_acf_encrypt',
        'type'          => 'true_false',
        'ui'            => 1,
    ], true);   
}

function update_value($value, $post_id, $field)
    {
        if ( isset($field['_acf_encrypt']) && $field['_acf_encrypt'] )
        {
            $ciphering = get_field('ciphering', 'option');
            $encryption_key = get_field('encryption_key', 'option');
            $encryption_iv = get_field('encryption_iv', 'option');
            if(!empty($ciphering)){
                $iv_length = openssl_cipher_iv_length($ciphering);
                $encryption = openssl_encrypt($value, $ciphering, $encryption_key, 0, $encryption_iv);
                return $encryption;
            }
            return $value;    
        }
        return $value;
    }

function load_value($value, $post_id, $field)
    {
        if ( isset($field['_acf_encrypt']) && $field['_acf_encrypt'] )
        {
            $ciphering = get_field('ciphering', 'option');
            $encryption_key = get_field('encryption_key', 'option');
            $encryption_iv = get_field('encryption_iv', 'option');
            if(!empty($ciphering)){
                $decryption=openssl_decrypt($value, $ciphering, $encryption_key, 0, $encryption_iv);
                return $decryption;
            }
            return $value;
        }
        return $value;
    }

function prepare_field($field)
    {
        if ( isset($field['_acf_encrypt']) && $field['_acf_encrypt'] )
        {
            $field_selector = '.acf-field-'.substr($field['key'], 6);
            ?>
            <style type="text/css">
                <?= $field_selector; ?> label:after{
                    content: " (encrypted)";
                    font-size: 80%;
                    font-weight: normal;
                    color: #CCC;
                }
                <?= $field_selector; ?> .acf-input-wrap input {
                    display: none;
                }
            </style>
            <script>
                (function () {
                    document.addEventListener('DOMContentLoaded', function () {
                        var inputWrapper = document.querySelector('<?= $field_selector; ?>');
                        var input = inputWrapper.querySelector('input')
                        var button = document.createElement('a');
                        button.href = '#';
                        button.innerHTML = (input.value)  ? 'Click to Show' : 'Click to Add';
                        button.className = 'acf-button button';
                        button.addEventListener('click', function (e) {
                            e.preventDefault();
                            this.style.display = 'none';
                            input.style.display = 'block';
                            return false;
                        });
                        inputWrapper.appendChild(button)
                    })
                }())
            </script>
            <?php
        }
        return $field;
    }
?>