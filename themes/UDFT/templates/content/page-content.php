<?php
/**
 * Created by PhpStorm.
 * User: Prog-SEO
 * Date: 27.03.2018
 * Time: 12:34
 */

global $post, $kmwp;

?>

<div class="page-content-box">
    <?php
        $img_id = get_post_thumbnail_id( $post );
	    if ( $img_id ) {
		    $attachment = get_post( $img_id );
		    /*array_push($img_set, array(
				    'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
				    'caption' => $attachment->post_excerpt,
				    'description' => $attachment->post_content,
				    'href' => get_permalink($attachment->ID),
				    'src' => $attachment->guid,
				    'title' => $attachment->post_title
			    )
		    );*/
	        echo get_the_post_thumbnail( $post->ID, 'full', array( 'class' => 'page-thumbnail-max', 'title' => $attachment->post_title ) );
	    }
	    the_content();
    ?>
</div>