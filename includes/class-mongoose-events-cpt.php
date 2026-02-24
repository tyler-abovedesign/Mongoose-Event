<?php
/**
 * Registers the mongoose_event CPT and event_type taxonomy.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mongoose_Events_CPT {

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'register_taxonomy' ] );
    }

    public function register_post_type() {
        $labels = [
            'name'               => 'Events',
            'singular_name'      => 'Event',
            'add_new'            => 'Add New Event',
            'add_new_item'       => 'Add New Event',
            'edit_item'          => 'Edit Event',
            'new_item'           => 'New Event',
            'view_item'          => 'View Event',
            'search_items'       => 'Search Events',
            'not_found'          => 'No events found',
            'not_found_in_trash' => 'No events found in Trash',
            'all_items'          => 'All Events',
            'menu_name'          => 'Events',
        ];

        register_post_type( 'mongoose_event', [
            'labels'       => $labels,
            'public'       => true,
            'has_archive'  => true,
            'show_in_rest' => true,
            'supports'     => [ 'title', 'editor', 'thumbnail' ],
            'rewrite'      => [ 'slug' => 'events' ],
            'menu_icon'    => 'dashicons-calendar-alt',
        ] );
    }

    public function register_taxonomy() {
        $labels = [
            'name'              => 'Event Types',
            'singular_name'     => 'Event Type',
            'search_items'      => 'Search Event Types',
            'all_items'         => 'All Event Types',
            'parent_item'       => 'Parent Event Type',
            'parent_item_colon' => 'Parent Event Type:',
            'edit_item'         => 'Edit Event Type',
            'update_item'       => 'Update Event Type',
            'add_new_item'      => 'Add New Event Type',
            'new_item_name'     => 'New Event Type Name',
            'menu_name'         => 'Event Types',
        ];

        register_taxonomy( 'event_type', 'mongoose_event', [
            'labels'            => $labels,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => [ 'slug' => 'event-type' ],
        ] );
    }

    public function seed_default_terms() {
        $defaults = [ 'Conferences', 'Webinars', 'Speaking', 'Masterminds' ];

        foreach ( $defaults as $term_name ) {
            if ( ! term_exists( $term_name, 'event_type' ) ) {
                wp_insert_term( $term_name, 'event_type' );
            }
        }
    }
}
