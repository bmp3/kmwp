<?php
/*
Plugin Name: Restore Image Title
Description: Reverses WP 3.5's behaviour of stripping title from images inserted into posts
Version: 1.5
License: GPL
Author: Les Bessant
Author URI: http://losingit.me.uk/
*/

function lcb_restore_image_title( $html, $id ) {

	$attachment = get_post($id);
    if (strpos($html, "title=")) {
    	return $html;
    	}
    else {
		$title = esc_attr($attachment->post_title);
		//return str_replace('<img', '<img title="' . $title . '" '  , $html);
	    $html = preg_replace('/(alt="([^"]+)?")/', '$1 title="' . $title . '"', $html );
	    return $html;
    }
}
add_filter( 'media_send_to_editor', 'lcb_restore_image_title', 15, 2 );

function lcb_restore_title_to_gallery( $content, $id ) {
	$thumb_title = get_the_title($id);
	return str_replace('<a', '<a title="' . esc_attr($thumb_title) . '" ', $content);
}	
add_filter('wp_get_attachment_link', 'lcb_restore_title_to_gallery', 10, 4);

?>