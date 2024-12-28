<?php

/**
 * Customize the preview button in the WordPress admin to point to the headless client.
 *
 * @param  str $link The WordPress preview link.
 * @return str The headless WordPress preview link.
 */
function set_headless_preview_link($link) {
  $frontend_url = get_field('frontend_url', 'option');
  return $frontend_url.'/'.get_post_field('post_name', get_the_ID()).'/';
}

add_filter('preview_post_link', 'set_headless_preview_link');

// Initially, the post/page does not have the `post` parameter in the URL
if (isset($_GET['post'])) {
  add_action('admin_head', function () {

    $title = get_post_field('post_title', get_the_ID());

    $script = <<<EOD
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var preview_button = document.querySelectorAll('#preview-action .preview.button')[0];
    preview_button.innerText = 'View Live Content';
    preview_button.title = 'View currently published page: $title';
  });
</script>
EOD;

    echo $script;

  });
}
