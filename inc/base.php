<?php

// Enable error reporting
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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
    $frontend_url = H_FRONT_URL;
    wp_redirect($frontend_url, 301);
    exit;
}
add_action('template_redirect', 'disable_wp_frontend');


// Add button to Options page
add_action('acf/render_field/name=deploy', function( $field ) {
    echo '<div style="display: flex; gap: 10px; align-items: center; margin-top: 15px;">';
    echo '<button id="deploy" class="button button-primary">Build website</button>';
    echo '<div id="deploy_message" style="color: #ccc; font-style: italic;"></div>';
    echo '<div id="github_metrics_container" style="flex: auto;"></div>';
    echo '</div>';
    echo '<div id="recent_build_zips_container" style="margin-top: 15px;">';
    echo "<div class='acf-label'>";
    echo "<label >Recent Build Zips</label></div>";
    echo '<ul id="recent_build_zips_list"></ul>';
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

function get_recent_build_zips() {
    $attachments = get_posts( array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_parent' => $post->ID,
            'post_mime_type' => 'application/zip',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => array(
                array(
                    'key'     => '_wp_attached_file',
                    'value'   => 'build_',
                    'compare' => 'LIKE'
                )
            )
        ) );

    wp_reset_postdata();
    return $attachments;
}

function deploy() {
    // Verify the nonce for security
    check_ajax_referer('custom_ajax_nonce', 'nonce'); 
	$url = 'https://api.github.com/repos/'.H_GITHUB_USER.'/'.H_GITHUB_REPO.'/dispatches';
	$data = json_encode([
		'event_type' => 'custom-event', // Matches the event in your workflow
		'client_payload' => ['key' => 'value'] // Optional additional data
	]);

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Authorization: token ' . H_GITHUB_AT,
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


function fetch_github_metrics() {
    // Verify the nonce for security
    check_ajax_referer('custom_ajax_nonce', 'nonce');
    $url = 'https://api.github.com/repos/' . H_GITHUB_USER . '/' . H_GITHUB_REPO . '/actions/workflows';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: token ' . H_GITHUB_AT,
        'Accept: application/vnd.github.v3+json',
        'User-Agent: PHP-Request'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        wp_send_json_error('Failed to fetch metrics from GitHub.');
    }
    $workflows = json_decode($response, true)['workflows'];

    // Fetch timing data for each workflow
    foreach ($workflows as &$workflow) {
        $timing_url = 'https://api.github.com/repos/' . H_GITHUB_USER . '/' . H_GITHUB_REPO . '/actions/workflows/' . $workflow['id'] . '/timing';
        $ch = curl_init($timing_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: token ' . H_GITHUB_AT,
            'Accept: application/vnd.github.v3+json',
            'User-Agent: PHP-Request'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $timing_response = curl_exec($ch);
        curl_close($ch);

        if ($timing_response !== false) {
            $workflow['timing'] = json_decode($timing_response, true);
        } else {
            $workflow['timing'] = 'Failed to fetch timing data';
        }
    }

    wp_send_json_success($workflows);
}
add_action('wp_ajax_fetch_github_metrics', 'fetch_github_metrics');

function apply_build() {
    // Verify the nonce for security
    check_ajax_referer('custom_ajax_nonce', 'nonce');
    $build_url = urldecode($_POST['build']);
    $build_file = get_attached_file_from_url($build_url);  
    $destination = H_DEPLOY_DIRECTORY;
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
    wp_send_json_success($response);
}
add_action('wp_ajax_apply_build', 'apply_build');

function get_attached_file_from_url($url) {
    // Get the upload directory paths
    $upload_dir = wp_get_upload_dir();
    $baseurl = $upload_dir['baseurl'];
    $basedir = $upload_dir['basedir'];

    // Replace the base URL with the base directory path
    $file_path = str_replace($baseurl, $basedir, $url);

    return $file_path;
}

function fetch_recent_build_zips() {
    check_ajax_referer('custom_ajax_nonce', 'nonce');

    $files = get_recent_build_zips();
    wp_send_json_success($files);
}
add_action('wp_ajax_fetch_recent_build_zips', 'fetch_recent_build_zips');

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


// MENUS REGISTERING
function register_my_menus() {
    register_nav_menus(
        array(
            'header-menu' => __( 'Header Menu' ),
            'footer-menu' => __( 'Footer Menu' )
        )
    );
}
add_action( 'init', 'register_my_menus' );


// MENUS and FRONTPAGE API ENDPOINTS
function register_menu_endpoint() {
    register_rest_route('custom/v1', '/menu/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_menu_by_slug',
        'permission_callback' => '__return_true', // Adjust permissions as needed
    ));
    register_rest_route('custom/v1', '/frontpage', array(
        'methods'  => 'GET',
        'callback' => 'get_frontpage',
        'permission_callback' => '__return_true', // Adjust permissions as needed
    ));
}
add_action('rest_api_init', 'register_menu_endpoint');

function get_menu_by_slug($data) {
    $menu_slug = $data['slug'];
    $menu = wp_get_nav_menu_items($menu_slug);

    if (empty($menu)) {
        return new WP_Error('no_menu', 'Invalid menu slug', array('status' => 404));
    }
    $menu_items = array();
    foreach ($menu as $item) {
        $menu_items[] = array(
            'ID' => $item->ID,
            'title' => $item->title,
            'url' => $item->url,
            'menu_order' => $item->menu_order,
            'parent' => $item->menu_item_parent,
        );
    }

    return rest_ensure_response($menu_items);
}

function get_frontpage( $object ) {

  // Get WP options front page from settings > reading.
  $frontpage_id = get_option('page_on_front');

  // Handle if error.
  if ( empty( $frontpage_id ) ) {
    // return error
    return 'error';
  }

  // Create request from pages endpoint by frontpage id.
  $request  = new \WP_REST_Request( 'GET', '/wp/v2/pages/' . $frontpage_id );

  // Parse request to get data.
  $response = rest_do_request( $request );

  // Handle if error.
  if ( $response->is_error() ) {
     return 'error';
  }

  return $response->get_data();
}

function create_ACF_meta_in_REST() {
    $postypes_to_exclude = ['acf-field-group','acf-field'];
    $extra_postypes_to_include = ["page","works"];
    $post_types = array_diff(get_post_types(["_builtin" => false], 'names'),$postypes_to_exclude);

    array_push($post_types, $extra_postypes_to_include);

    foreach ($post_types as $post_type) {
        register_rest_field( $post_type, 'ACF', [
            'get_callback'    => 'expose_ACF_fields',
            'schema'          => null,
       ]
     );
    }

}

function expose_ACF_fields( $object ) {
    $ID = $object['id'];
    return get_fields($ID);
}

add_action( 'rest_api_init', 'create_ACF_meta_in_REST' );