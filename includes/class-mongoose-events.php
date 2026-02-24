<?php
/**
 * Core singleton — loads CPT, fields, and AJAX classes.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mongoose_Events {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init() {
        require_once MONGOOSE_EVENTS_PATH . 'includes/class-mongoose-events-cpt.php';
        require_once MONGOOSE_EVENTS_PATH . 'includes/class-mongoose-events-ajax.php';

        new Mongoose_Events_CPT();
        new Mongoose_Events_AJAX();

        if ( class_exists( 'ACF' ) ) {
            require_once MONGOOSE_EVENTS_PATH . 'includes/class-mongoose-events-fields.php';
            new Mongoose_Events_Fields();
        }
    }
}
