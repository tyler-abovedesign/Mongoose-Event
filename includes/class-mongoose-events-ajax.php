<?php
/**
 * AJAX handler for event filtering + shared card renderer.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mongoose_Events_AJAX {

    public function __construct() {
        add_action( 'wp_ajax_mongoose_filter_events', [ $this, 'handle_filter' ] );
        add_action( 'wp_ajax_nopriv_mongoose_filter_events', [ $this, 'handle_filter' ] );
    }

    /**
     * AJAX endpoint: returns filtered event cards.
     */
    public function handle_filter() {
        check_ajax_referer( 'mongoose_events_nonce', 'nonce' );

        $event_type = isset( $_POST['event_type'] ) ? sanitize_text_field( $_POST['event_type'] ) : 'all';
        $per_page   = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 12;
        $show_past  = isset( $_POST['show_past'] ) && $_POST['show_past'] === '1';

        $args = [
            'post_type'      => 'mongoose_event',
            'posts_per_page' => $per_page,
            'meta_key'       => 'event_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => [],
        ];

        if ( ! $show_past ) {
            $args['meta_query'][] = [
                'key'     => 'event_date',
                'value'   => gmdate( 'Ymd' ),
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ];
        }

        // Filter by taxonomy term.
        if ( 'all' !== $event_type ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'event_type',
                    'field'    => 'slug',
                    'terms'    => $event_type,
                ],
            ];
        }

        $query = new WP_Query( $args );

        ob_start();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                echo self::render_event_card( get_the_ID() );
            }
            wp_reset_postdata();
        } else {
            echo '<p class="mw-el-no-results">No events found.</p>';
        }
        $html = ob_get_clean();

        wp_send_json_success( [
            'html' => $html,
        ] );
    }

    /**
     * Icon class for each event type slug.
     */
    private static function get_type_icon( $slug ) {
        $icons = [
            'conferences' => 'eicon-users',
            'webinars'    => 'eicon-play-o',
            'speaking'    => 'eicon-microphone',
            'masterminds' => 'eicon-lightbulb',
        ];
        return isset( $icons[ $slug ] ) ? $icons[ $slug ] : 'eicon-tag';
    }

    /**
     * Format a date or date range for display.
     *
     * Single day:        "March 15, 2026"
     * Same month range:  "March 15 - 17, 2026"
     * Cross-month range: "March 28 - April 2, 2026"
     */
    private static function format_date_range( $start_raw, $end_raw ) {
        if ( ! $start_raw ) {
            return '';
        }

        $start_ts = strtotime( $start_raw );

        if ( ! $end_raw || $end_raw <= $start_raw ) {
            return date_i18n( 'F j, Y', $start_ts );
        }

        $end_ts = strtotime( $end_raw );

        if ( date( 'Ym', $start_ts ) === date( 'Ym', $end_ts ) ) {
            // Same month: "March 15 - 17, 2026"
            return date_i18n( 'F j', $start_ts ) . ' - ' . date_i18n( 'j, Y', $end_ts );
        }

        // Different months: "March 28 - April 2, 2026"
        return date_i18n( 'F j', $start_ts ) . ' - ' . date_i18n( 'F j, Y', $end_ts );
    }

    /**
     * Render a single event card.
     *
     * @param int $post_id Event post ID.
     * @return string HTML markup.
     */
    public static function render_event_card( $post_id ) {
        $date_raw      = get_field( 'event_date', $post_id );
        $end_date_raw  = get_field( 'event_end_date', $post_id );
        $location_type = get_field( 'event_location_type', $post_id );
        $location      = get_field( 'event_location', $post_id );
        $event_url     = get_field( 'event_url', $post_id );
        $title         = get_the_title( $post_id );
        $description   = wp_trim_words( get_the_excerpt( $post_id ), 20, '&hellip;' );

        // Format display date (supports ranges).
        $date_display = self::format_date_range( $date_raw, $end_date_raw );

        // Get first event type term.
        $terms     = get_the_terms( $post_id, 'event_type' );
        $term_name = '';
        $term_slug = '';
        if ( $terms && ! is_wp_error( $terms ) ) {
            $term_name = $terms[0]->name;
            $term_slug = $terms[0]->slug;
        }

        // Badge icon.
        $badge_icon = self::get_type_icon( $term_slug );

        // Featured image.
        $image = '';
        if ( has_post_thumbnail( $post_id ) ) {
            $image = get_the_post_thumbnail( $post_id, 'medium_large', [
                'loading' => 'lazy',
                'alt'     => esc_attr( $title ),
            ] );
        }

        // Location display + icon.
        $location_display = '';
        $location_icon    = '';
        if ( 'in-person' === $location_type && $location ) {
            $location_display = esc_html( $location );
            $location_icon    = 'eicon-map-pin';
        } elseif ( 'online' === $location_type ) {
            $location_display = 'Online';
            $location_icon    = 'fa fa-wifi';
        }

        $tag        = $event_url ? 'a' : 'div';
        $link_attrs = $event_url ? ' href="' . esc_url( $event_url ) . '" target="_blank" rel="noopener noreferrer"' : '';

        ob_start();
        ?>
        <<?php echo $tag; ?> class="mw-el-card"<?php echo $link_attrs; ?>>
            <div class="mw-el-card__image">
                <?php echo $image; ?>
                <?php if ( $term_name ) : ?>
                    <span class="mw-el-card__badge mw-el-card__badge--<?php echo esc_attr( $term_slug ); ?>"><i class="<?php echo esc_attr( $badge_icon ); ?>"></i> <?php echo esc_html( $term_name ); ?></span>
                <?php endif; ?>
            </div>
            <div class="mw-el-card__content">
                <?php if ( $date_display ) : ?>
                    <span class="mw-el-card__date"><i class="eicon-calendar mw-el-card__date-icon"></i> <?php echo esc_html( $date_display ); ?></span>
                <?php endif; ?>
                <h5 class="mw-el-card__title"><?php echo esc_html( $title ); ?></h5>
                <?php if ( $description ) : ?>
                    <p class="mw-el-card__description"><?php echo $description; ?></p>
                <?php endif; ?>
                <div class="mw-el-card__divider"></div>
                <?php if ( $location_display ) : ?>
                    <span class="mw-el-card__location"><i class="<?php echo esc_attr( $location_icon ); ?> mw-el-card__location-icon"></i> <?php echo esc_html( $location_display ); ?></span>
                <?php endif; ?>
            </div>
        </<?php echo $tag; ?>>
        <?php
        return ob_get_clean();
    }

}
