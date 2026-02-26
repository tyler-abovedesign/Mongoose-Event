<?php
/**
 * Plugin Name: Mongoose Events
 * Description: Event management with custom post type, taxonomy, ACF fields, and AJAX filtering.
 * Version: 1.2.0
 * Author: Mongoose
 * Text Domain: mongoose-events
 * Requires Plugins: advanced-custom-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MONGOOSE_EVENTS_VERSION', '1.2.0' );
define( 'MONGOOSE_EVENTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'MONGOOSE_EVENTS_URL', plugin_dir_url( __FILE__ ) );

require_once MONGOOSE_EVENTS_PATH . 'vendor/autoload.php';
require_once MONGOOSE_EVENTS_PATH . 'includes/class-mongoose-events.php';

// GitHub-based plugin updates.
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$mongoose_events_updater = PucFactory::buildUpdateChecker(
    'https://github.com/tyler-abovedesign/Mongoose-Event/',
    __FILE__,
    'mongoose-events'
);
$mongoose_events_updater->setBranch( 'main' );

Mongoose_Events::instance();

/**
 * On activation: register CPT/taxonomy, seed terms, flush rewrites.
 */
register_activation_hook( __FILE__, function () {
    require_once MONGOOSE_EVENTS_PATH . 'includes/class-mongoose-events-cpt.php';
    $cpt = new Mongoose_Events_CPT();
    $cpt->register_post_type();
    $cpt->register_taxonomy();
    $cpt->seed_default_terms();
    flush_rewrite_rules();
} );

/**
 * On deactivation: flush rewrites.
 */
register_deactivation_hook( __FILE__, function () {
    flush_rewrite_rules();
} );
