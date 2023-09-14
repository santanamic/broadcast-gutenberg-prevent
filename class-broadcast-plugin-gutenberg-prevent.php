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

		// Does this child exist?
		$child_post_id = $bcd->broadcast_data->get_linked_child_on_this_blog();
		if ( ! $child_post_id )
			return;

		// Find all blocks.
		$this->child_post = get_post( $child_post_id );
	}

	public function threewp_broadcast_broadcasting_modify_post( $action )
	{		
		$bcd = $action->broadcasting_data;
		$modified_post = $bcd->modified_post;
		$modified_post_content = $modified_post->post_content; //original
		
		// Find all of the blocks again
		$modified_blocks = ThreeWP_Broadcast()->gutenberg()->parse_blocks( $modified_post_content );
		$protected_blocks = ThreeWP_Broadcast()->gutenberg()->parse_blocks( $this->child_post->post_content );

		// Replace the protected blocks with our stuff.
		foreach( $modified_blocks as $block )
		{
			$this->debug( 'The original block: ' . var_export($block, true) );
			
			$block_id = $block[ 'attrs' ][ 'idBroadcast' ];

			foreach( $protected_blocks as $child_block )
			{
				$this->debug( 'debug child block %s', var_export($child_block, true) );
				
				$child_block_id = $child_block[ 'attrs' ][ 'idBroadcast' ];

				if ( $child_block_id == $block_id && $block[ 'attrs' ][ 'preventBroadcast' ] === true ) {
					$this->debug( 'The block to replace %s', $block_id );
					
					$protected_block = $child_block[ 'original' ];
					$original_block  = $block[ 'original' ];

					// Get block html
					$protected_block = static::search_gutenberg_block($protected_block, $this->child_post->post_content );
					$original_block  = static::search_gutenberg_block($original_block, $modified_post_content);

					$modified_post_content = str_replace($original_block, $protected_block, $modified_post_content);
				}
			}
		}
		
		$this->debug( 'New modified post content: ' . var_export($modified_post_content, true) );

		$bcd->modified_post->post_content = $modified_post_content;
	}
	
	public static function search_gutenberg_block($start_search, $html) {
		$parts = explode($start_search, $html);

		if (count($parts) > 1) {
			
			$end_search = '<!-- ';
			$blockContent = explode($end_search, $parts[1]);
			
			if (count($blockContent) > 0) {
				return $start_search . $blockContent[0];
			}

			return null;
		}
	}
}	// class

new Broadcast_Gutenberg_Prevent_Plugin();