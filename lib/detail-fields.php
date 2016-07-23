<?php
/**
 * 	Detail Fields Script
 *
 *	Author: Jannik Portz (@janizde)
 * 	Author URI: www.jannikportz.de
 *	GitHub Project: github.com/janizde/WP-Detail-Fields
 */

global	$detail_fields;

if ( !is_array( $detail_fields ) ) :
	$detail_fields = array();
endif;

/**
 * 	Register a detail field for a specific post type
 *
 *	@param 		string|array 		$post_type
 *	@param 		array 					$args
 */
if ( !function_exists( 'register_detail_field' ) ) {
	function register_detail_field ( $post_types	= 'post', $args = null ) {
		if (!$args or empty($post_types))
			return;

		if (is_string( $post_types ))
			$post_types		= array( $post_types );

		global $detail_fields;

		foreach ( $post_types as $post_type )
			$detail_fields[ $post_type ][ ]		= $args;
	}
}

/**
 *	Register All Hooked Detail Fields
 *	triggers action 'add_detail_fields'
 *
 *	@wp_action 		init
 */
if ( !function_exists( 'action_register_detail_fields' ) ) {
	function action_register_detail_fields() {
		do_action ( 'add_detail_fields' );
	}

	add_action ( 'init', 'action_register_detail_fields', 20 );
}

/**
 *	Markup Function
 *
 *	@param 			WP_Post 		$post
 *	@param 			array 			$metabox
 */
if ( !function_exists( 'details_box_markup' ) ) {
	function details_box_markup ( $post, $metabox ) {

		global	$post;

		/**
		 * Get field instance
		 */
		$post_type			= $metabox[ 'args' ][ 'post_type' ];
		$fields				= $metabox[ 'args' ][ 'fields' ];

		/**
		 * None Field
		 */
		wp_nonce_field( $post_type.'-details-metabox', $post_type.'-details-metabox-nonce' );

		$has_field	= array(
			'text'		   	=> false,
			'textarea'	 	=> false,
			'select'	   	=> false,
			'checkbox'	 	=> false,
			'radio'		   	=> false,
	    	'date'       	=> false,
			'time'			=> false,
			'url'			=> false,
			'email'			=> false,
			'post-select'	=> false,
		);

		foreach ( $fields as $field ) :

			if ( is_callable( $field['hide'] ) )
				$field['hide'] = call_user_func( $field['hide'] );

			if ( $field['hide'] === true )
				continue;

			if ( isset( $field[ 'page-template' ] ) and $field[ 'page-template' ] != get_post_meta( $post->ID, '_wp_page_template', true ) )
				continue;

			$field[ 'id' ]			= $post_type . '-detail-' . $field[ 'slug' ];
			$field[ 'key' ]			= '_' . $field[ 'id' ];
			$field[ 'saved-val' ]	= get_post_detail( $field[ 'slug' ], $post->ID );

			$has_field[ $field[ 'key' ] ]	= true;

			echo	'<div class="field">';

			/**
			 * Field Caption
			 */
			if ( $field[ 'caption' ] ) :
				echo '<p class="label">';
					echo '<label for="'. $field[ 'id' ] .'">';
					echo '<strong>' . $field[ 'caption' ] . '</strong>';
					echo '</label>';
				echo '</p>';
			endif;

			switch ( $field[ 'type' ] ) :

				/**
				 * Case: Heading
				 */
				case ( 'heading' ) :
					echo	'<h4 id="'. $field['id'] .'" class="heading" style="font-size: 1.3em">';
					echo	$field[ 'heading' ];
					echo	'</h4>';
				break;

				/**
				 * Case: Text Input, Date Input, Time Input, Email Input, URL Input
				 */
				case ( 'text' ) :
				case ( 'date' ) :
				case ( 'time' ) :
				case ( 'email' ) :
				case ( 'url' ) :
					echo	'<input type="'. $field[ 'type' ] .'" class="widefat" id="'. $field[ 'id' ] .'" name="'. $field[ 'id' ] .'" value="'. $field[ 'saved-val' ] .'" />';
				break;

				/**
				 * Case: Teaxtarea
				 */
				case ( 'textarea' ) :
					echo	'<textarea class="widefat" id="'. $field[ 'id' ] .'" name="'. $field[ 'id' ] .'">'. $field[ 'saved-val' ] .'</textarea>';
				break;

				/**
				 * Case: Select
				 */
				case ( 'select' ) :
					echo	'<select size="1" name="'. $field[ 'id' ] .'" id="'. $field[ 'id' ] .'" class="widefat">';

						/* No Option - Label */
						if ( empty( $field[ 'default-val' ] ) ) :
							echo	'<option value="">';
								echo	(isset( $field[ 'caption-none' ] )) ? $field[ 'caption-none' ] : __('-- None --', 'roots');
							echo	'</option>';
						endif;

						if ( is_callable( $field[ 'options' ] ) )
							$field[ 'options' ]		= call_user_func( $field[ 'options' ] );

						/* Custom Options */
						foreach ( $field[ 'options' ] as $value => $caption ) :
							$checked	= ( $field[ 'saved-val' ] == $value or ( empty($field[ 'saved-val' ]) and $field[ 'default-val' ] == $value ) ) ? 'selected="selected"' : '';
							echo '<option value="'.$value.'" '.$checked.'>'. $caption .'</option>';
						endforeach;

					echo 	'</select>';
				break;

				/**
				 *	Case: 	Post-Select
				 */
				case 'post-select' :
				case 'post-select-multi' :

					$defaultArgs 	= array(
						'post_type'		=> 'post',
						'numberposts'	=> -1
					);

					$args 	= wp_parse_args( $field[ 'post-args' ], $defaultArgs );

					if ( isset( $field[ 'posts' ] ) and is_array( $field[ 'posts' ] ) ) :
						$posts 	= $field[ 'posts' ];

					elseif ( isset( $field[ 'post-strategy' ] ) and is_callable( $field[ 'post-strategy' ] ) ) :
						$posts 	= call_user_func( $field[ 'post-strategy' ] );

					else :
						$posts 	= get_posts( $args );

					endif;

					$size 		= ( $field[ 'type' ] == 'post-select-multi' ) ? 5 : 1;
					$style 		= ( $field[ 'type' ] == 'post-select-multi' ) ? 'style="height:100px;"' : '';
					$multi 		= ( $field[ 'type' ] == 'post-select-multi' ) ? 'multiple="multiple"' : '';
					$nameExt 	= ( $field[ 'type' ] == 'post-select-multi' ) ? '[]' : '';

					echo 	'<select size="'. $size .'" name="'. $field[ 'id' ] . $nameExt .'" id="'. $field[ 'id' ] .'" class="widefat" '. $style .' '. $multi .'>';

						if ( isset( $field[ 'caption-none' ] ) ) :
							$selected 	= ( in_array( 0, (array) $field[ 'saved-val' ] ) ) ? 'selected' : '';
							echo '<option value="0" '. $selected .'>'. $field[ 'caption-none' ] .'</option>';
						endif;

						foreach ( $posts as $postItem ) :
							$selected 	= ( in_array( $postItem->ID, (array) $field[ 'saved-val' ] ) ) 	? 'selected="selected"' : '';
							echo '<option value="'. $postItem->ID .'" '. $selected .'>'. $postItem->post_title .'</option>';
						endforeach;

					echo 	'</select>';
				break;

				/**
				 * Case: Radio or Checkbox
				 */
				case 'radio' :
				case 'checkbox' :

					if ( is_array( $field[ 'options' ] ) ) :

						foreach ( $field[ 'options' ] as $value => $caption ) :
							echo	'<label for="'. $field[ 'id' ] .'-'. $value .'">';

								if ( $field[ 'type' ] == 'radio' ) :
									$checked	= ( $value == $field[ 'saved-val' ]
												or (empty( $field[ 'saved-val' ] ) and $value == $field[ 'default-val' ]) )
												? 'checked="checked"' : '';

								elseif ( $field[ 'type' ] == 'checkbox' ) :
									$checked	= ( in_array( $value, (array)$field[ 'saved-val' ] ) ) ? 'checked="checked"' : '';

								endif;

								$brackets	= ( $field[ 'type' ] == 'checkbox' ) ? '[]' : '';
								echo	'<input type="'. $field[ 'type' ] .'" name="'. $field[ 'id' ] . $brackets .'" value="'. $value .'" id="'. $field[ 'id' ] .'-'. $value .'" '.$checked.' /> '.$caption.'';
							echo	'</label>';
							echo	'<br />';
						endforeach;

					endif;
				break;

			endswitch;

			/**
			 * Field description
			 */

			if ( $field[ 'description' ] )
				echo '<p><small>'. $field[ 'description' ] .'</small></p>';

			echo	'</div>';

		endforeach;

	}
}

/**
 * 	Register the details metabox
 *
 *	@wp_action 		add_meta_boxes
 */
if ( !function_exists( 'register_details_metabox' ) ) {
	function register_details_metabox() {

		global	$detail_fields;

		/**
		 * Register Metabox for each post_type
		 */
		foreach ( $detail_fields as $post_type => $post_type_details ) :

			/**
			 * Add Meta Box for post_type
			 */
			add_meta_box(
				$post_type.'-details',
				apply_filters( 'detail_fields_metabox_title', 'Details' ),
				'details_box_markup',
				$post_type,
				apply_filters( 'detail_fields_metabox_context', ( post_type_supports( $post_type, 'editor' ) ) ? 'side' : 'advanced' ),
				apply_filters( 'detail_fields_metabox_priority', 'core' ),
				array(
					'post_type'		=> $post_type,
					'fields'		=> $post_type_details
				)
			);

		endforeach;

	}

	add_action ( 'add_meta_boxes', 'register_details_metabox' );
}

/**
 * 	Save Meta Data
 *
 *	@wp_action 		save_post
 */
if ( !function_exists( 'detail_fields_save_meta_data' ) ) {
	function detail_fields_save_meta_data () {

		global $post, $detail_fields;

		// Verify nonce
		if ( !wp_verify_nonce( $_POST[ $post->post_type .'-details-metabox-nonce' ], $post->post_type .'-details-metabox' ) )
			return;

		foreach ( $detail_fields[ $post->post_type ] as $field ) :

			$field[ 'id' ]			= $post->post_type . '-detail-' . $field[ 'slug' ];
			$field[ 'key' ]			= '_' . $field[ 'id' ];

			if ( $field[ 'type' ] == 'checkbox' ) :

				delete_post_meta	( $post->ID, $field[ 'key' ] );

				foreach ( (array)$_POST[ $field[ 'id' ] ] as $value )
					add_post_meta( $post->ID, $field[ 'key' ], $value );

			else :

				$value 		= $_POST[ $field[ 'id' ] ];

				if ( $field[ 'type' ] == 'url' )
					$value 		= esc_url( $value );

				if ( !empty( $_POST[ $field[ 'id' ] ] ) ) :
					update_post_meta ( $post->ID, $field[ 'key' ], $value );
				else :
					delete_post_meta( $post->ID, $field[ 'key' ], $value );
				endif;

			endif;

		endforeach;
	}

	add_action ( 'save_post', 'detail_fields_save_meta_data' );
}

/**
 *	Get Post Detail
 *
 *	@param 			string 		$slug
 *	@param 			int 			$post_id 	(optional)
 *	@param 			bool 			$returnPost (optional)
 *	@return 		mixed
 */
if ( !function_exists( 'get_post_detail' ) ) {
	function get_post_detail ( $slug, $post_id = false, $returnPost = false ) {
		if (!$slug)		return;

		if (!$post_id) :
			global $post;
			$post_id	= $post->ID;
		else :
			$post		= get_post( $post_id );
		endif;

		$post_type		= $post->post_type;
		$key			= '_' . $post_type .'-detail-'. $slug;

		$result			= get_post_meta( $post_id, $key, false );

		if ( $returnPost ) :

				if ( is_numeric( $result ) ) :
					$result 	= get_post( $result );

				elseif ( is_array( $result ) ) :
					$posts 		= array();

					foreach ( $result as $id ) :

						if ( is_numeric( $id ) )
							$posts[] 	= get_post( $id );

					endforeach;

					$result 	= $posts;

				endif;

		endif;

		if ( !count($result) ) :
			return false;
		elseif ( is_array( $result ) and count($result) === 1 ) :
			$result 	= $result[ 0 ];
		endif;

		return apply_filters( 'get_post_detail', $result, $slug, $post_id, $returnPost );
	}
}

/**
 *	Get Meta Key
 *
 *	@param 			string 		$slug
 *	@param 			string 		$post_type
 *	@return 		string
 */
if ( !function_exists( 'get_meta_key' ) ) {
	function get_meta_key( $slug, $post_type = 'post' ) {
		return 	'_' . $post_type . '-detail-' . $slug;
	}
}
?>
