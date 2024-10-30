<?php
/**
 * Plugin Name: Bulk Trackback & Comments On/Off
 * Plugin URI: http://lapuvieta.lv
 * Description: Bulk Trackback & Comments On/Off for your posts or pages in a super easy way.
 * Version: 1.0.2
 * Author: Janis Itkacs
 * Author URI: http://lapuvieta.lv
 * License: GPL2
 */

if (!defined( 'ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!is_admin()) {
    return false;
} // Don't run if not admin page

define('JEPC_VERSION', '1.0.2');

/**
 * Main Plugin function
 */
function jepc_bulk_trackback_comments_on_off_run() {

    // Add custom columns
    add_filter('manage_posts_columns', 'jepc_add_comments_column_table_head');
    add_filter('manage_posts_columns', 'jepc_add_pings_column_table_head');
    add_filter('manage_page_posts_columns', 'jepc_add_comments_column_table_head');
    add_filter('manage_page_posts_columns', 'jepc_add_pings_column_table_head');


    // Fill data rows with values
    add_action('manage_posts_custom_column', 'jepc_add_pings_column_table_content', 10, 2);
    add_action('manage_posts_custom_column', 'jepc_add_comments_column_table_content', 10, 2);
    add_action('manage_page_posts_custom_column', 'jepc_add_pings_column_table_content', 10, 2);
    add_action('manage_page_posts_custom_column', 'jepc_add_comments_column_table_content', 10, 2);

    // Enable sorting for columns
    add_filter('manage_edit-post_sortable_columns', 'jepc_add_pings_column_table_sorting');
    add_filter('manage_edit-post_sortable_columns', 'jepc_add_comments_column_table_sorting');
    add_filter('manage_edit-page_sortable_columns', 'jepc_add_pings_column_table_sorting');
    add_filter('manage_edit-page_sortable_columns', 'jepc_add_comments_column_table_sorting');

    // Sort columns by WP_posts table fields - comment_status and ping_status
    add_filter('posts_orderby', 'jepc_ping_comment_status_column_sort', 10, 2);

    // Add custom styles
    add_action('admin_head', 'jepc_styles');

    // Enqueue JS script
    add_action('admin_init', 'jepc_enqueue_js_scripts');

    // Add Ajax Server side script
    add_action( 'wp_ajax_update_pings_comments_status', 'jepc_update_pings_comments_status' );

}

/**
 * Add Allow pings column to post type
 * @param $defaults
 * @return mixed
 */
function jepc_add_pings_column_table_head( $defaults ) {
    $defaults['allow_pings'] = __('Trackbacks', 'jepc');
    return $defaults;
}

/**
 * Add Allow comments column to post type
 * @param $defaults
 * @return mixed
 */
function jepc_add_comments_column_table_head( $defaults ) {
    $defaults['allow_comments'] = __('Comments', 'jepc');
    return $defaults;
}

/**
 * Make pings columns sortable
 * @param $columns
 * @return mixed
 */
function jepc_add_pings_column_table_sorting($columns) {
    $columns['allow_pings'] = 'ping_status';
    return $columns;
}

/**
 * Make comments columns sortable
 * @param $columns
 * @return mixed
 */
function jepc_add_comments_column_table_sorting($columns) {
    $columns['allow_comments'] = 'comment_status';
    return $columns;
}

/**
 * Sort by ping and comment status values
 * @param $orderby
 * @param WP_Query $q
 * @return string
 */
function jepc_ping_comment_status_column_sort($orderby, \WP_Query $q) {

    global $wpdb;

    $_orderby = $q->get( 'orderby' );
    $_order   = $q->get( 'order' );

    if ($_orderby != 'comment_status' && $_orderby != 'ping_status') {
        return $orderby;
    }

    if( $q->is_main_query() && did_action('load-edit.php')) {
        $orderby = " {$wpdb->posts}.{$_orderby} "
            . ( 'ASC' === strtoupper( $_order ) ? 'ASC' : 'DESC' )
            . ", {$wpdb->posts}.ID DESC ";
    }

    return $orderby;

}

/**
 * Show checkbox Allow pings for post type
 * @param $column_name
 */
function jepc_add_pings_column_table_content($column_name) {
    global $post;
    if ($column_name == 'allow_pings') {
        echo "<input class=\"editable_ping_comment prevent-submit\" data-type=\"ping\" name=\"allow_ping[]\" type=\"checkbox\" value=\"" . $post->ID . "\"" . ($post->ping_status == 'open' ? ' checked="checked"' : '') . " />";
        echo "<div id=\"result_ping_" . $post->ID . "\" style=\"display: none;\">" . __('Updating...', 'jepc') . "</div>";
    }
}

/**
 * Show checkbox Allow comments for post type
 * @param $column_name
 */
function jepc_add_comments_column_table_content($column_name) {
    global $post;
    if ($column_name == 'allow_comments') {
        echo "<input class=\"editable_ping_comment prevent-submit\" data-type=\"comment\" name=\"allow_comment[]\" type=\"checkbox\" value=\"" . $post->ID . "\"" . ($post->comment_status == 'open' ? ' checked="checked"' : '') . " />";
        echo "<div id=\"result_comment_" . $post->ID . "\" style=\"display: none;\">" . __('Updating...', 'jepc') . "</div>";
    }
}

/**
 * Enqueue JS Scripts
 */
function jepc_enqueue_js_scripts() {
    wp_enqueue_script( 'jepc-ajax', plugins_url( '/js/scripts.js', __FILE__ ), array('jquery'), JEPC_VERSION, true);
}

/**
 * Update pings or comments status
 */
function jepc_update_pings_comments_status() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        global $wpdb;

        $postId = $_POST['postId'];
        $postField = $_POST['postField'];
        $postFieldValue = $_POST['postFieldValue'];

        $wpdb->update(
            $wpdb->posts,
            array(
                $postField => $postFieldValue,
            ),
            array( 'ID' => $postId ),
            array(
                '%s',
            ),
            array( '%d' )
        );

    }
    die();
}

/**
 * Styles
 */
function jepc_styles() {
    echo '<style type="text/css">
           #allow_pings, #allow_comments { width: 120px;  }
         </style>';
}

/**
 * Run plugin
 */
jepc_bulk_trackback_comments_on_off_run();