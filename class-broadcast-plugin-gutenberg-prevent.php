<?php

namespace threewp_broadcast\premium_pack\gutenberg_protect;

/**
	@brief			Protects specific Gutenberg blocks from being overwritten during broadcasting.
	@plugin_group	Control
	@since			2020-03-18 12:11:01
**/
class Broadcast_Gutenberg_Prevent_Plugin
	extends \threewp_broadcast\premium_pack\base
{
	public function _construct()
	{
		$this->add_action( 'threewp_broadcast_broadcasting_after_switch_to_blog' );
		$this->add_action( 'threewp_broadcast_broadcasting_modify_post', 100 );	// Wait until everyone else is done.
	}

	// --------------------------------------------------------------------------------------------
	// ----------------------------------------- Callbacks
	// --------------------------------------------------------------------------------------------

	/**
		@brief		threewp_broadcast_broadcasting_after_switch_to_blog
		@since		2020-03-18 12:12:13
	**/
	public function threewp_broadcast_broadcasting_after_switch_to_blog( $action )
	{
		$bcd = $action->broadcasting_data;
		$this->protected_blocks = [];

		// Does this child exist?
		$child_post_id = $bcd->broadcast_data->get_linked_child_on_this_blog();
		if ( ! $child_post_id )
			return;

		// Find all blocks.
		$child_post = get_post( $child_post_id );
		$blocks = ThreeWP_Broadcast()->gutenberg()->parse_blocks( $child_post->post_content );

		if ( count( $blocks ) < 1 )
			return;

		foreach( $blocks as $block )
		{
			$this->debug( 'debug protected_blocks %s', var_export($this->protected_blocks, true) );
			
			$block_id = $block[ 'attrs' ][ 'idBroadcast' ];
			if ( $block[ 'attrs' ][ 'preventBroadcast' ] === true ) {
				$this->debug( 'Protecting block %s', $block_id );
				$this->protected_blocks[ $block_id ] = $block;
			}
		}
	}

	/**
		@brief		threewp_broadcast_broadcasting_modify_post
		@since		2020-03-18 12:12:00
	**/
	public function threewp_broadcast_broadcasting_modify_post( $action )
	{
		if ( count( $this->protected_blocks ) < 1 )
			return;

		$bcd = $action->broadcasting_data;
		$modified_post = $bcd->modified_post;
		$modified_post_content = $modified_post->post_content;

		// Find all of the blocks again
		$blocks = ThreeWP_Broadcast()->gutenberg()->parse_blocks( $modified_post_content );

		// Replace the protected blocks with our stuff.
		foreach( $blocks as $block )
		{
			$block_id = $block[ 'attrs' ][ 'idBroadcast' ];
			
			if ( ! isset( $this->protected_blocks[ $block_id ] ) )
				continue;
			$this->debug( 'Restoring %s', $block_id );
			$modified_post_content = ThreeWP_Broadcast()->gutenberg()->replace_text_with_block( $block[ 'original' ], $this->protected_blocks[ $block_id ], $modified_post_content );
		}

		$bcd->modified_post->post_content = $modified_post_content;
	}
}	// class

new Broadcast_Gutenberg_Prevent_Plugin();