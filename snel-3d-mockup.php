<?php
/**
 * Plugin Name:       Snel 3D Mockup
 * Description:       3D device-mockup studio (MacBook / iMac / iPhone) voor elk post type: featured image op een 3D device, posen, belichten en als PNG naar de media library exporteren.
 * Version:           1.2.1
 * Author:            Snelstack
 * Text Domain:       snel-mockup
 */

if (! defined('ABSPATH')) exit;

define('SNEL_MOCKUP_VERSION', '1.2.1');
define('SNEL_MOCKUP_DIR', plugin_dir_path(__FILE__));
define('SNEL_MOCKUP_URL', plugin_dir_url(__FILE__));

// Auto-updates from GitHub releases (same setup as snel-translations).
// Guarded so a dev checkout without vendor/ never fatals.
if (file_exists(SNEL_MOCKUP_DIR . 'vendor/autoload.php')) {
    require_once SNEL_MOCKUP_DIR . 'vendor/autoload.php';

    $snel_mockup_updater = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/LaupWing/snel-3d-mockup/',
        __FILE__,
        'snel-3d-mockup'
    );
    $snel_mockup_updater->setAuthentication(defined('SNEL_MOCKUP_GITHUB_TOKEN') ? constant('SNEL_MOCKUP_GITHUB_TOKEN') : '');
    $snel_mockup_updater->getVcsApi()->enableReleaseAssets();
}

/**
 * Every public post type gets the studio (dynamic — new CPTs join
 * automatically). Trim the list via the `snel_mockup_post_types` filter.
 */
function snel_mockup_post_types(): array
{
    $types = get_post_types(['public' => true]);
    unset($types['attachment']);
    return apply_filters('snel_mockup_post_types', array_values($types));
}

add_action('add_meta_boxes', function () {
    foreach (snel_mockup_post_types() as $type) {
        add_meta_box(
            'snel-3d-mockup',
            __('3D Mockup', 'snel-mockup'),
            'snel_mockup_render_metabox',
            $type,
            'side',
            'default'
        );
    }
});

add_action('admin_enqueue_scripts', function ($hook) {
    if (! in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }
    $screen = get_current_screen();
    if (! $screen || ! in_array($screen->post_type, snel_mockup_post_types(), true)) {
        return;
    }

    $asset_file = SNEL_MOCKUP_DIR . 'build/index.asset.php';
    if (! file_exists($asset_file)) {
        return;
    }
    $asset = require $asset_file;

    wp_enqueue_script(
        'snel-3d-mockup',
        SNEL_MOCKUP_URL . 'build/index.js',
        $asset['dependencies'],
        $asset['version'],
        true
    );
    wp_enqueue_style('wp-components');
    wp_enqueue_media(); // for the screen-image picker

    global $post;
    $post_id = $post ? $post->ID : 0;

    // Models/HDRIs live with the plugin (tracked in git, served from src).
    $base = SNEL_MOCKUP_URL . 'src/assets';

    // Featured image as the default screen texture — always the ORIGINAL:
    // 'large' caps at 1024px and makes every screen render soft.
    $image_url = '';
    if ($post_id && has_post_thumbnail($post_id)) {
        $image_url = get_the_post_thumbnail_url($post_id, 'full');
    }

    wp_localize_script('snel-3d-mockup', 'snelMockup', [
        'models'   => [
            [ 'id' => 'imac',              'label' => 'iMac',              'url' => "$base/models/imac.glb" ],
            [ 'id' => 'macbook-pro-m3',    'label' => 'MacBook Pro 16"',   'url' => "$base/models/macbook-pro-m3.glb" ],
            [ 'id' => 'iphone-13-pro-max', 'label' => 'iPhone 13 Pro Max', 'url' => "$base/models/iphone-13-pro-max.glb" ],
        ],
        'tableUrl' => "$base/models/noguchi_coffee_table.glb",
        'hdris'    => [
            [ 'label' => 'Studio',              'url' => "$base/hdri/studio.hdr" ],
            [ 'label' => 'Photo Studio (dark)', 'url' => "$base/hdri/photostudio.hdr" ],
            [ 'label' => 'Interior (windows)',  'url' => "$base/hdri/interior.hdr" ],
            [ 'label' => 'Room',                'url' => "$base/hdri/room.hdr" ],
        ],
        'imageUrl'     => $image_url,
        'productId'    => $post_id,
        'productTitle' => $post_id ? get_the_title($post_id) : '',
        // ONE global settings blob, reused everywhere. null until first Save.
        'config'   => get_option('snel_mockup_config', null),
        // Named global presets: { name: { panel, studio } }.
        'presets'  => (object) get_option('snel_mockup_presets', []),
        'restUrl'  => esc_url_raw(rest_url('snel-mockup/v1/')),
        'nonce'    => wp_create_nonce('wp_rest'),
    ]);
});

/**
 * REST: save the single global studio config blob.
 */
add_action('rest_api_init', function () {
    register_rest_route('snel-mockup/v1', '/config', [
        'methods'             => 'POST',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'callback'            => function (WP_REST_Request $req) {
            update_option('snel_mockup_config', $req->get_json_params());
            return ['ok' => true];
        },
    ]);

    // Save/update one named global preset: { name, config: { panel, studio } }.
    register_rest_route('snel-mockup/v1', '/presets', [
        'methods'             => 'POST',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'callback'            => function (WP_REST_Request $req) {
            $params = $req->get_json_params();
            $name   = isset($params['name']) ? sanitize_text_field($params['name']) : '';
            if ($name === '' || ! isset($params['config']) || ! is_array($params['config'])) {
                return new WP_Error('bad_preset', 'Expected a name and a config object.', ['status' => 400]);
            }
            $presets          = get_option('snel_mockup_presets', []);
            $presets[ $name ] = $params['config'];
            update_option('snel_mockup_presets', $presets);
            return ['ok' => true, 'presets' => (object) $presets];
        },
    ]);

    // Save a captured mockup PNG into the Media Library, optionally as the
    // post's featured image.
    register_rest_route('snel-mockup/v1', '/thumbnail', [
        'methods'             => 'POST',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'callback'            => 'snel_mockup_save_thumbnail',
    ]);
});

function snel_mockup_save_thumbnail(WP_REST_Request $req)
{
    $params       = $req->get_json_params();
    $data_url     = isset($params['image']) ? $params['image'] : '';
    $post_id      = isset($params['productId']) ? intval($params['productId']) : 0;
    $set_featured = ! empty($params['setFeatured']);
    $alt          = isset($params['alt']) ? sanitize_text_field($params['alt']) : '';
    $filename     = isset($params['filename']) ? sanitize_file_name($params['filename']) : '';

    if (! preg_match('#^data:image/png;base64,#', $data_url)) {
        return new WP_Error('bad_image', 'Expected a PNG data URL.', ['status' => 400]);
    }
    $binary = base64_decode(substr($data_url, strpos($data_url, ',') + 1));
    if ($binary === false || $binary === '') {
        return new WP_Error('decode_failed', 'Could not decode the image.', ['status' => 400]);
    }

    require_once ABSPATH . 'wp-admin/includes/image.php'; // pulls in file.php + media.php helpers

    if ($filename === '') {
        $filename = 'mockup-' . ($post_id ?: 'post') . '-' . time();
    }
    if (! preg_match('/\.png$/i', $filename)) {
        $filename .= '.png';
    }
    $upload = wp_upload_bits($filename, null, $binary);
    if (! empty($upload['error'])) {
        return new WP_Error('upload_failed', $upload['error'], ['status' => 500]);
    }

    $attach_id = wp_insert_attachment([
        'post_mime_type' => 'image/png',
        'post_title'     => $alt !== '' ? $alt : pathinfo($filename, PATHINFO_FILENAME),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ], $upload['file'], $post_id);
    if (is_wp_error($attach_id)) {
        return $attach_id;
    }
    wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $upload['file']));
    if ($alt !== '') {
        update_post_meta($attach_id, '_wp_attachment_image_alt', $alt);
    }

    if ($set_featured && $post_id) {
        set_post_thumbnail($post_id, $attach_id);
    }

    return [
        'ok'       => true,
        'id'       => $attach_id,
        'url'      => wp_get_attachment_url($attach_id),
        'featured' => $set_featured && $post_id,
    ];
}

function snel_mockup_render_metabox($post)
{
    ?>
    <p><?php esc_html_e('Genereer een 3D device-mockup van de featured image.', 'snel-mockup'); ?></p>
    <button
        type="button"
        class="button button-primary"
        id="snel-open-3d-mockup"
        data-post-id="<?php echo esc_attr($post->ID); ?>"
    >
        <?php esc_html_e('Open 3D Mockup', 'snel-mockup'); ?>
    </button>
    <?php
}
