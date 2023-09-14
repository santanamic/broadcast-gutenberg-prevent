<?php 

/*
Plugin Name: Broadcast Gutenberg Prevent
Version: 1.0.5
Description: 
*/

function load_broadcast_gutenberg_prevent() {

	if ( ! function_exists( 'ThreeWP_Broadcast' ) )
		wp_die( 'Please activate Broadcast before this plugin.' );
		
		$plugin_path = plugin_dir_path( __FILE__ );
		$broadcast_path = plugin_dir_path( dirname( __FILE__ ) ) . 'threewp-broadcast/';

		require_once( $broadcast_path . 'src/premium_pack/base.php' );
		require_once( $plugin_path . 'class-broadcast-plugin-gutenberg-prevent.php' );	
}

function resgister_scripts_gutenberg_prevent() {
	wp_enqueue_script(
		'broadcast-gutenberg-prevent',
		plugin_dir_url( __FILE__ ) . 'build/index.js',
		array(
			'wp-blocks',
			'wp-components',
			'wp-element',
			'wp-i18n',
			'wp-editor'
		),
		'1.0.1', true
	);	
}

add_action( 'threewp_broadcast_loaded', 'load_broadcast_gutenberg_prevent' );
add_action( 'enqueue_block_assets', 'resgister_scripts_gutenberg_prevent' );