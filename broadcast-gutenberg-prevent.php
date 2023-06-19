<?php 

/*
Plugin Name: Broadcast Gutenberg Prevent
Version: 1.0.0
Description: 
*/

function load_broadcast_lifterlms() {

	if ( ! function_exists( 'ThreeWP_Broadcast' ) )
		wp_die( 'Please activate Broadcast before this plugin.' );
		
		$plugin_path = plugin_dir_path( __FILE__ );
		$broadcast_path = plugin_dir_path( dirname( __FILE__ ) ) . 'threewp-broadcast/';

		require_once( $broadcast_path . 'src/premium_pack/base.php' );
		require_once( $plugin_path . 'class-broadcast-plugin-gutenberg-prevent.php' );	
}

function load_broadcast_lifterlms() {

	wp_enqueue_script(
		'broadcast-gutenberg-prevent',
		plugin_dir_url( __FILE__ ) . '/src/build/index.js',
		array(
			'wp-blocks',
			'wp-components',
			'wp-element',
			'wp-i18n',
			'wp-editor'
		),
		'1.0.0', true
	);	
}

add_action( 'threewp_broadcast_loaded', 'load_broadcast_lifterlms' );
add_action( 'enqueue_block_assets', [ $this, 'resgister_block_scripts' ] );