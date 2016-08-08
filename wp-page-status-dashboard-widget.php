<?php
/**
 * Plugin Name: Page status widget
 * Plugin URI: https://github.com/domingobishop
 * Description: Displays a list pages with pending and draft statuses on the dashboard.
 * Version: 0.1
 * Author: Chris Bishop
 * Author URI: https://github.com/domingobishop
 * License: GPL2
 */

// Loads admin CSS
function load_tna_page_status_admin_style() {
	wp_register_style( 'custom_wp_admin_css', plugin_dir_url(__FILE__) . '/style.css', false, '0.1' );
	wp_enqueue_style( 'custom_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'load_tna_page_status_admin_style' );

// Adds dashboard column option to screen options
function dashboard_columns() {
	add_screen_option(
		'layout_columns',
		array(
			'max'     => 3,
			'default' => 2
		)
	);
}
add_action( 'admin_head-index.php', 'dashboard_columns' );

// Adds widget to dashboard
function page_status_add_dashboard_widgets() {
	wp_add_dashboard_widget(
		'page_status_widget',
		'Pending and draft pages',
		'cb_page_status_widget'
	);
}
add_action( 'wp_dashboard_setup', 'page_status_add_dashboard_widgets' );

// Page status function
function cb_page_status_widget() {

	$query = array(
		'post_type' => 'page',
		'post_status' => array('draft', 'pending'),
		'orderby' => 'modified'
	);
	$loop = new WP_Query($query);

	$current_user = wp_get_current_user();
	$html = '<div class="cb-page-status-widget current-user-id-'  . $current_user->ID . '">';
	$html .= '<h4>Hello ' . $current_user->display_name . '</h4>';
	$html .= '<table>';
	$html .= '<tr>';
	$html .= '<th>Title</th>';
	$html .= '<th>Last modified by</th>';
	$html .= '<th>Current status</th>';
	$html .= '</tr>';

	while ( $loop->have_posts() ) : $loop->the_post();
		global $post;
		$status = get_post_status( $post->ID );
		$author = get_the_modified_author();
		if ( $author == $current_user->user_login ) {
			$my_page = 'my-page';
		} else {
			$my_page = 'not-my-page';
		}
		$html .= '<tr class="page-'. $status . ' ' . $my_page . '">';
		$html .= '<td class="title">' . get_the_title();
		$html .= ' <a href="' . get_edit_post_link( $post->ID ) . '">edit</a>';
		$html .= '</td>';
		$html .= '<td>' . $author . ' on ' . get_the_modified_date( $d = 'j/n/y' ) .'</td>';
		$html .= '<td>' . $status . '</td>';
		$html .= '</tr>';
	endwhile;

	$html .= '</table></div>';

	echo $html;

}
