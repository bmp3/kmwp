<?php

class kmwp_nav_menu extends Walker_Nav_Menu {

	public $last_zero_first_item;
	public $last_first_item;

	function start_lvl( &$output, $depth = 0, $args = array() ) {

		global $post, $wp_query;
		$depth_class_names = array();

		$display_depth = ( $depth + 1); // because it counts the first submenu as 0
		if ( $depth == 0 ) {
			$depth_class_names[] = 'menu-top-block';
		}
		else {
			$depth_class_names[] = 'sub-menu-block';
			if ( $depth == 1 ) {
				$depth_class_names[] = 'mm-blocks-box';
			}
			if ( $depth == 2 ) {
				$depth_class_names[] = 'mm-block';
			}
		}
		$depth_class_names[] = 'sub-menu menu-block-depth-' . $depth;

		if ( $this->last_zero_item ) {
			$zero_link =  '<li class="parent-link-box zero"><a class="parent-link menu-link sub-menu-link" href="' . $this->last_zero_item->href . '"><span>' . $this->last_zero_item->title . '</a></li>';
		}
		else $zero_link = '';

		if ( $this->last_first_item ) {
			$first_link =  '<li class="parent-link-box first"><a class="parent-link menu-link sub-menu-link" href="' . $this->last_first_item->href . '"><span>' . $this->last_first_item->title . '</a></li>';
		}
		else $first_link = '';

        if ( $depth == 0 )
		    $output .= '<ul class="' . implode( ' ',$depth_class_names ) . '">' . $zero_link . "\n";
        else if ( $depth == 1 )
	        $output .= '<div class="mm-content-div"><ul class="' . implode( ' ',$depth_class_names ) . '">' . '</span></a>' . $first_link . "\n";

	}

	function start_el( &$output, $item, $depth = 0, $args = array(), $current_object_id = 0 ) {
		global $wp_query;
		$depth_class_names = array();

		if ( $depth == 0 ) {
			$depth_class_names[] = 'menu-top-item';
		}
		else {
			$depth_class_names[] = 'sub-menu-item';
			if ( $depth == 1 ) {
				$depth_class_names[] = 'mm-items-box';
			}
			if ( $depth == 2 ) {
				$depth_class_names[] = 'mm-item';
			}
		}
		$depth_class_names[] = 'menu-item-depth-' . $depth;

		$depth_class_names = esc_attr( implode( ' ', $depth_class_names ) );

		// passed classes
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );

		// build html
		$output .= '<li id="nav-menu-item-'. $item->ID . '" class="' . $depth_class_names . ' ' . $class_names . '">';

		// link attributes
		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		$attributes .= ' class="menu-link ' . ( $depth > 0 ? 'sub-menu-link' : 'main-menu-link' ) . '"';

		if ( $depth == 0 ) {
			$item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
				$args->before,
				$attributes,
				$args->link_before,
				apply_filters( 'the_title', $item->title, $item->ID ),
				$args->link_after,
				$args->after
			);
		}
		else if ( $depth == 1 ) {
			$item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
				$args->before,
				$attributes,
				$args->link_before,
				apply_filters( 'the_title', $item->title, $item->ID ),
				$args->link_after,
				$args->after
			);
		}
		else if ( $depth == 2 ) {
			$id = $item->object_id;
			//$img = get_the_post_thumbnail( $id, 'full', array( 'class' => 'mmc-img' ) );
			//$content = apply_filters( 'the_content', get_post_field('post_content', $id ) );
			$img = $content = '';
			$img_id = get_field ( 'menu_img', $id );
			if ( $img_id && is_array( $img_id ) ) {
				$img = wp_get_attachment_image( $img_id['id'], 'full', array( 'class' => 'mmc-img' ) );
			}
			$content = get_field ( 'temp_content', $id );
            $item_output =
	            '<div class="mm-content-box">' .
	                '<a class="mm-content-title" href="' . get_permalink( $id ) . '">' . $item->title . '</a>' .
	                '<div class="mm-content">' .
                        '<div class="mmc-img-box">' . $img . '</div>' .
	                    '<div class="mmc-content">' . $content . '</div>' .
	                '</div>' .
                '</div>';
		}

		if ( in_array( 'menu-item-has-children', $classes ) ) {
			$item_output = preg_replace( '/<\/a>/', '<div class="item-icon-box"><div class="item-icon depth-' . $depth . '"></div></div></a>', $item_output );
		}

		// build html
		if ( $depth == 0 ) {
			$this->last_zero_item = $item;
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
		else if ( $depth == 1 ) {
			$this->last_first_item = $item;
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
		else if ( $depth == 2 ) {
			$this->last_zero_item = $this->last_first_item = null;
			$output .= $item_output;
		}

	}

	/*
	function end_el( &$output, $item, $depth = 0, $args = array() ) {

	}
	*/

	/*function end_lvl( &$output, $depth = 0, $args = array() ) {

	}*/

}

?>