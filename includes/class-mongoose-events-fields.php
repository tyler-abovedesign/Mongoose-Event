<?php
/**
 * Registers ACF fields for the mongoose_event post type.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mongoose_Events_Fields {

    public function __construct() {
        add_action( 'acf/init', [ $this, 'register_fields' ] );
    }

    public function register_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( [
            'key'      => 'group_mongoose_event_details',
            'title'    => 'Event Details',
            'fields'   => [
                [
                    'key'            => 'field_me_event_date',
                    'label'          => 'Start Date',
                    'name'           => 'event_date',
                    'type'           => 'date_picker',
                    'required'       => 1,
                    'display_format' => 'F j, Y',
                    'return_format'  => 'Ymd',
                    'first_day'      => 0,
                ],
                [
                    'key'            => 'field_me_event_end_date',
                    'label'          => 'End Date',
                    'name'           => 'event_end_date',
                    'type'           => 'date_picker',
                    'required'       => 0,
                    'display_format' => 'F j, Y',
                    'return_format'  => 'Ymd',
                    'first_day'      => 0,
                    'instructions'   => 'Leave blank for single-day events.',
                ],
                [
                    'key'           => 'field_me_event_location_type',
                    'label'         => 'Location Type',
                    'name'          => 'event_location_type',
                    'type'          => 'button_group',
                    'choices'       => [
                        'online'    => 'Online',
                        'in-person' => 'In-Person',
                    ],
                    'default_value' => 'online',
                    'layout'        => 'horizontal',
                ],
                [
                    'key'               => 'field_me_event_location',
                    'label'             => 'Location / Address',
                    'name'              => 'event_location',
                    'type'              => 'text',
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'field_me_event_location_type',
                                'operator' => '==',
                                'value'    => 'in-person',
                            ],
                        ],
                    ],
                ],
                [
                    'key'   => 'field_me_event_url',
                    'label' => 'Event URL',
                    'name'  => 'event_url',
                    'type'  => 'url',
                ],
            ],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'mongoose_event',
                    ],
                ],
            ],
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
        ] );
    }
}
