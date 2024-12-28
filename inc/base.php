<?php

// Disable automatic WordPress plugin updates
add_filter( 'auto_update_plugin', '__return_false' );

// Disable automatic WordPress theme updates
add_filter( 'auto_update_theme', '__return_false' );

// Disable Gutenberg on the back end.
add_filter( 'use_block_editor_for_post', '__return_false' );

// Disable Gutenberg for widgets.
add_filter( 'use_widgets_block_editor', '__return_false' );

add_action( 'wp_enqueue_scripts', function() {
    // Remove CSS on the front end.
    wp_dequeue_style( 'wp-block-library' );

    // Remove Gutenberg theme.
    wp_dequeue_style( 'wp-block-library-theme' );

    // Remove inline global CSS on the front end.
    wp_dequeue_style( 'global-styles' );

    // Remove classic-themes CSS for backwards compatibility for button blocks.
    wp_dequeue_style( 'classic-theme-styles' );
}, 20 );

// SVG Support
function add_file_types_to_uploads($file_types){
    $new_filetypes = array();
    $new_filetypes['svg'] = 'image/svg+xml';
    $file_types = array_merge($file_types, $new_filetypes );
    return $file_types;
}
add_filter('upload_mimes', 'add_file_types_to_uploads');

// Disable WordPress AutoSave
function disableAutoSave(){
     wp_deregister_script('autosave');
}
add_action( 'wp_print_scripts', 'disableAutoSave' );

// Disable WordPress Frontend for Headless
function disable_wp_frontend() {

    // If it's an API request or an admin page, allow it
    if (is_admin() || strpos($_SERVER['REQUEST_URI'], '/wp-json/') === 0 || strpos($_SERVER['REQUEST_URI'], '/cms/wp-admin/') === 0) {
        return;
    }

    // Return a 404 for all other frontend requests
    $frontend_url = get_field('frontend_url', 'option');
    wp_redirect($frontend_url, 301);
    exit;
}
add_action('template_redirect', 'disable_wp_frontend');


// Add button to Options page
add_action('acf/render_field/name=deploy', function( $field ) {
    echo '<div style="display: flex; gap: 10px; align-items: center; margin-top: 15px;">';
    echo '<button id="deploy" class="button button-primary">Create build</button>';
    echo '<div id="deploy_message" style="color: #ccc; font-style: italic;"></div>';
    echo '</div>';
});

add_action('acf/render_field/name=apply_build', function( $field ) {
    echo '<div style="display: flex; gap: 10px; align-items: center; margin-top: 15px; flex-direction: column;  ">';
    echo '<button id="apply_build" class="button button-primary">Apply build</button>';
    echo '<div id="apply_build_message" style="color: #ccc; font-style: italic;"></div>';
    echo '</div>';
});

// JS for Options pahe buton AJAX call
function enqueue_custom_admin_scripts($hook) {
    if( $hook === 'toplevel_page_options' ) { // Replace 'admin-options' with your menu_slug
        wp_enqueue_script( 'deploy-script', get_template_directory_uri() . '/js/deploy.js', array('jquery'), null, true );
        wp_localize_script( 'deploy-script', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('custom_ajax_nonce')
        ));
    }
}
add_action( 'admin_enqueue_scripts', 'enqueue_custom_admin_scripts' );

function deploy() {
    // Verify the nonce for security
    check_ajax_referer('custom_ajax_nonce', 'nonce');
    $github_user = get_field('github_user', 'option');
    $github_repo = get_field('github_repo', 'option');
    $github_access_token = get_field('github_access_token', 'option');
	$url = 'https://api.github.com/repos/'.$github_user.'/'.$github_repo.'/dispatches';
	$data = json_encode([
		'event_type' => 'custom-event', // Matches the event in your workflow
		'client_payload' => ['key' => 'value'] // Optional additional data
	]);

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Authorization: token ' . $github_access_token,
		'Accept: application/vnd.github.v3+json',
		'User-Agent: PHP-Request'
	]);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	curl_close($ch);

	// echo $response;
    // Perform your custom action here
    // $response = array('message' => 'AJAX action processed successfully.');

    wp_send_json_success($response);
}
add_action('wp_ajax_deploy', 'deploy');

function apply_build() {
    // Verify the nonce for security
    check_ajax_referer('custom_ajax_nonce', 'nonce');
    $build_file = get_attached_file( $_POST['build'] );
    $destination = get_field('deploy_directory', 'option');
    $zip = new ZipArchive;
    $res = $zip->open($build_file);
    if ($res === TRUE) {
        $firstLevelFolder = '';

        // Determine the first-level folder
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (substr($filename, -1) === '/') {
                $firstLevelFolder = $filename;
                break;
            }
        }

        // Extract files, stripping the first-level folder
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $relativePath = substr($filename, strlen($firstLevelFolder));
            $fileinfo = pathinfo($relativePath);

            // Create directories if they do not exist
            if (isset($fileinfo['dirname']) && !is_dir($destination . $fileinfo['dirname'])) {
                mkdir($destination . $fileinfo['dirname'], 0755, true);
            }

            // Check if the current item is a directory
            if (substr($filename, -1) === '/') {
                // Create the directory
                if (!is_dir($destination . $relativePath)) {
                    mkdir($destination . $relativePath, 0755, true);
                }
            } else {
                // Extract file
                copy("zip://{$build_file}#{$filename}", $destination . $relativePath);
            }
        }
        $zip->close();
        $response = array('message' => 'Build applied successfully.');
    } else {
        $response = array('message' => 'You have to choose a Build.');
    }
    
    $response = array('message' => $build_file);
    wp_send_json_success($response);
}
add_action('wp_ajax_apply_build', 'apply_build');

// Remove update metabox in Options page
// add_action('admin_menu', 'maybe_find_and_remove_that_meta_box', 100, 0);

function maybe_find_and_remove_that_meta_box() {
    $option_page_slug = get_plugin_page_hookname('options', '');

    add_action("load-{$option_page_slug}", 'add_that_remove_meta_box_action', 11);
}

function add_that_remove_meta_box_action() {
    add_action('acf/input/admin_head', 'remove_that_damn_meta_box', 11);

    // also change it to 1 column, so you don't have empty sidebar 
    add_screen_option('layout_columns', array('max' => 1, 'default' => 1));
}

function remove_that_damn_meta_box() {
    remove_meta_box('submitdiv', 'acf_options_page', 'side');
}