<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require ( 'inc/redux-config.php' );
require ( 'inc/functions/system.php' );
require ( 'inc/functions/post-meta-box.php' );

register_nav_menus( array(
	'header-location' => 'Top Memu',
	'footer-location' => 'Footer Menu'
) );

if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 750, 420 );
	add_image_size( 'page-thumb', 800, 500, true );
	add_image_size( 'related-post-thumb-large', 400, 300, true );
	add_image_size( 'related-post-thumb', 200, 150, true );
	add_image_size( 'sidebar-thumb', 84, 63, true );
}

if ( function_exists('register_sidebar') ) {
	register_sidebar( array(
		'name'          => 'right-sidebar',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => "</div>\n",
		'before_title'  => '<h3 class="widgettitle">',
		'after_title'   => "</h3>\n",
	) );
}



require ( 'mega-menu.php' );

add_action( 'init', 'kmwp_init' );

function kmwp_init() {

	add_filter( 'widget_text', 'do_shortcode' );

	if ( !is_admin() ) {

		if(!session_id()) {
			session_start();
		}

		wp_enqueue_style( 'template_css', get_template_directory_uri() . '/css/template.css' );
		wp_enqueue_style( 'bootstrap-template_css', get_template_directory_uri() . '/css/bootstrap-system.css' );
		wp_enqueue_style( 'main_custom_css', get_template_directory_uri() . '/css/main_style.css' );
		wp_enqueue_style( 'scrollbar_css', get_template_directory_uri() . '/css/jquery.mCustomScrollbar.css' );
		wp_enqueue_style( 'fontawesome_css', get_template_directory_uri() . '/css/font-awesome.css' );

		wp_register_script( 'scrollbar_js', get_template_directory_uri() . '/js/jquery.mCustomScrollbar.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'scrollbar_js' );
		wp_register_script( 'mousewheel_js', get_template_directory_uri() . '/js/jquery.mousewheel.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'mousewheel_js' );
		wp_register_script( 'custom_js', get_template_directory_uri() . '/js/script.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'custom_js' );
	};
	

}


function kmwp_get_styles() {

	if ( !is_admin() ) {
		$out = file_get_contents( get_template_directory() . '/css/template.css' ) .
		       file_get_contents(get_template_directory() . '/css/bootstrap-system.css' ) .
		       file_get_contents( get_template_directory() . '/css/main_style.css' ) .
		       file_get_contents( get_template_directory() . '/css/jquery.mCustomScrollbar.css' ) .
		       file_get_contents( get_template_directory() . '/css/font-awesome.css' );

		echo '<style>' . $out . '</style>';
	}

}

//add_action ( 'wp_head', 'kmwp_get_styles' );


function kmwp_get_scripts() {

	if ( !is_admin() ) {
		$out = file_get_contents( get_template_directory() . '/js/jquery.mCustomScrollbar.js' ) .
		       file_get_contents(get_template_directory() . '/js/jquery.mousewheel.js' ) .
		       file_get_contents( get_template_directory() . '/js/script.js' );

		echo '<script type="text/javascript">' . $out . '</script>';
	}

}

//add_action( 'wp_footer', 'kmwp_get_scripts' );


function kmwp_site_title() {

	global $page, $paged;
	wp_title( '|', true, 'right' );
	bloginfo( 'name' );
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );

}


add_filter( 'body_class', 'custom_class_names' );
function custom_class_names( $classes ) {

	global $post;

	if ( is_page() ) {
		$classes[] = 'customized-page';
		if ( $post && is_int( $post->ID ) )
		    $classes[] = 'page-' . strtolower( preg_replace( array( '/\s/', '/,|\.|\"|\'/' ), array( '-', '' ), $post->post_title ) );
	}

	if ( $post && $post->post_type && $post->post_type == 'post' ) {
		$classes[] = 'customized-post';
	}

	return $classes;

}


function kmwp_start_content_layout( $echo = true ) {

    global $post, $kmwp;

    if ( $kmwp['sidebar-layout'] == 1 ) {
        $out = '<div class="content-box col col-lg-12">';
    }
    else if ( $kmwp['sidebar-layout'] == 2 ) {
        $out =
            '<div class="left-sidebar-box col col-lg-3 col-sm-12">' .
                kmwp_get_sidebar_content( 'left-sidebar' ) .
            '</div>
            <div class="content-box col col-lg-6 col-sm-12">';
    }
    else if ( $kmwp['sidebar-layout'] == 3 ) {
        $out =
            '<div class="left-sidebar-box col col-lg-4 col-sm-12">' .
                kmwp_get_sidebar_content( 'left-sidebar' ) .
            '</div>
             <div class="content-box col col-lg-8 col-sm-12">';
    }
    if ( $kmwp['sidebar-layout'] == 4 ) {
        $out =
            '<div class="content-box col col-lg-8 col-sm-12">';
    }

    if ( $echo )
        echo $out;
    else
        return $out;

}


function kmwp_finish_content_layout( $echo = true ) {

    global $post, $kmwp;

    if ( $kmwp['sidebar-layout'] == 1 ) {
        $out = '</div>';
    }
    else if ( $kmwp['sidebar-layout'] == 2 ) {
        $out = '</div>
                <div class="right-sidebar-box col col-lg-3 col-sm-12">' .
                    kmwp_get_sidebar_content( 'right-sidebar' ) .
	            '</div>';
    }
    else if ( $kmwp['sidebar-layout'] == 3 ) {
        $out =
            '</div>';
    }
    if ( $kmwp['sidebar-layout'] == 4 ) {
        $out = '</div>
                <div class="right-sidebar-box col col-lg-4 col-sm-12">' .
                   kmwp_get_sidebar_content( 'right-sidebar' ) .
                '</div>';
    }

    if ( $echo )
        echo $out;
    else
        return $out;

}

add_shortcode( 'kmwp_get_content_form', 'kmwp_get_content_form' );

function kmwp_get_content_form( $args ) {

	global $kmwp;

	$defaults = array( 'type' => 'regular', 'class' => 'white' );
	$atts = shortcode_atts( $defaults, $args );

	if ( $atts['class'] != 'header-form green' ) {
		if ( $atts['type'] != 'reverted' ) {
			$atts['class'] .= ' reverted';
			$src           = get_stylesheet_directory_uri() . '/img/form-doctor-reversed.png';
			$src           = $kmwp['form-doctor-reversed']['url'];
			$img_meta      = kmwp_get_img_meta( $kmwp['form-doctor-reversed']['id'] );
		} else if ( $atts['type'] == 'reverted' ) {
			$src      = get_stylesheet_directory_uri() . '/img/form-doctor.png';
			$src      = $kmwp['form-doctor']['url'];
			$img_meta = kmwp_get_img_meta( $kmwp['form-doctor']['id'] );
		}
	}
	else {
		$src      = get_stylesheet_directory_uri() . '/img/form-doctor.png';
		$src      = $kmwp['form-doctor']['url'];
		$img_meta = kmwp_get_img_meta( $kmwp['form-doctor']['id'] );
	}

	$out =
		'<div class="form-box-bg ' . $atts['class'] . ' row">
             <div class="img-box col"><img ' . $img_meta['title'] . $img_meta['alt']. 'src="' . $src . '"></div>
             <div class="gs-form-box col">' .
		         kmwp_get_form() .
             '</div>
        </div>';

	return $out;

}


function kmwp_get_form() {

	$out =
		'<form class="lead-form" action="">
             <div class="form-content">
                 <div class="form-title">get started</div>
                 <div class="form-description">complete the short form below to get free consultation</div>
                 <div class="inputs-box">
                     <div class="i-box"><input class="f-input" name="name" placeholder="Full Name" value=""></div>
                     <div class="i-box"><input class="f-input" name="email" placeholder="Email adress" value=""></div>
                     <div class="i-box"><input class="f-input" name="phone" placeholder="Phone number" value=""></div>
                     <div class="i-box"><textarea class="f-textarea" name="message" placeholder="Comment / Message" value=""></textarea></div>
                 </div>
                 <a class="button btn medium mhf">submit request</a>
             </div>
         </form>';

	return $out;

}


function kmwp_breadcrumbs() {

	global $post;

	$not_allowed = array( 451 );
	$out = '';

	if ( !in_array( $post->ID, $not_allowed ) ) {
		$out = smarty_breadcrumbs();
	}

	return $out;

}


add_shortcode( 'kmwp_get_sidebar_pages', 'kmwp_get_sidebar_pages' );

function kmwp_get_sidebar_pages( $args ) {

	global $wp_query;

	$defaults = array( 'ids' => null );

	$atts = shortcode_atts( $defaults, $args );

	if ( !$atts['ids'] ) {
		//$posts = get_posts( array( 'post_type' => 'page' ) );
		$posts = $wp_query->posts;
	}
	else if ( isset( $atts['ids'] ) ) {
		$posts = get_posts( array( 'post_type' => 'page', 'include' => $atts['ids'] ) );
	}

    $out = '';

	if ( count( $posts ) > 0 ) {
		$out = '<div class="sidebar-items">';
		foreach ( $posts as $p ) {

			$out .=
				'<div class="sb-item">
                     <div class="sb-item-img"><a href="' . get_permalink( $p->ID ) . '">' . get_the_post_thumbnail( $p->ID, 'sidebar-thumb', array( 'class' => 'sidebar-size' ) ) . '</a></div>
                     <div class="sb-item-content">
                         <div class="sb-item-category"><span>' . 'nutrition' . '</span></div>
                         <div class="sb-item-title"><a href="' . get_permalink( $p->ID ) . '">' . get_the_title( $p->ID ) . '</a></div>
                     </div>
                </div>';
			
		}
		$out .= '</div>';
	}

	return $out;

}



add_shortcode( 'kmwp_get_relative_pages', 'kmwp_get_relative_pages' );

function kmwp_get_relative_pages( $args ) {

	$defaults = array( 'ids' => null, 'class' => '' );

	$atts = shortcode_atts( $defaults, $args );

	if ( ! $atts['ids'] ) {
		$posts = get_posts( array( 'post_type' => 'page' ) );
	} else if ( isset( $atts['ids'] ) ) {
		$posts = get_posts( array( 'post_type' => 'page', 'include' => $atts['ids'] ) );
	}

	$out = '<div class="pa-items-box '. $atts['class'] . '"><h3 class="pa-items-title">read this text</h3>';

	if ( count( $posts ) > 0 ) {
		$out .= '<div class="pa-items">';
		foreach ( $posts as $p ) {

			$out .= kmwp_get_archive_single_post( $p, 'related-post-thumb-large' );

		}
		$out .= '</div>';
	}

	$out .= '</div>';

	return $out;

}

function kmwp_get_img_meta( $id ) {

	$result = array();

	$title = get_the_title( $id );
	if ( $title && $title != '' ) $title = ' title="' . $title . '" '; else $title = '';
	$alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
	if ( $alt && $alt != '' ) $alt = ' alt="' . $alt . '" '; else $alt = '';

	return array( 'title' => $title, 'alt' => $alt );

}


add_shortcode( 'test_widget', 'test_widget' );

function test_widget( $args ) {

	if ( !$args['id'] ) return '';
	if ( !$args['class'] ) $class = ' '; else $class = ' ' . $args['class'] . ' ';

	$img = wp_get_attachment_image_src( $args['id'], 'full' );
	$img = $img[0];
	$img_meta = kmwp_get_img_meta( $args['id'] );

	$out = '<div class="custom-banner' . $class . '"><img class="ps-image" ' . $img_meta['title'] . $img_meta['alt'] . 'src="' . $img . '"></div>';

	return $out;

}


function kmwp_get_archive_single_post( $p = null, $thumb_size = 'related-post-thumb' ) {

	global $post;
	if ( is_int( $p ) ) $p = get_post( $p );
	else if ( is_object( $p ) ) $p = $p;
	else $p = $post;

	$out =
		'<div class="pa-item-box">
             <div class="pa-item">
                 <div class="pa-img-box">
                     <a class="pa-link" href="' . get_permalink( $p->ID ) . '">' . get_the_post_thumbnail( $p->ID, $thumb_size, array( 'class' => $thumb_size ) ) . '</a>
                 </div>
                 <div class="pa-content-box">
                     <h3 class="pa-title"><a class="pa-title-link" href="' . get_permalink( $p->ID ) . '">' . get_the_title( $p->ID ) . '</a></h3>
                     <div class="pa-content">' . wp_trim_words( kmwp_get_the_excerpt( $p ), 24, null ) . '</div>
                     <a class="pa-readmore blue" href="' . get_permalink( $p->ID ) . '">read more</a>
                 </div> 
             </div>        
        </div>
        <div class="pa-item-border"></div>';

	return $out;

}


function kmwp_get_the_title( $p ) {

	$not_allowed = array( 2, 173, 451 );
	$out = '';

	if ( $p && $p->ID ) $id = $p->ID;
	else $id = 2;

	if ( !in_array( $id, $not_allowed ) ) {
		$title = get_field( 'h1', $id );
		if ( !$title || $title == '' ) $title = get_the_title( $id );
	    $out =
		    '<div class="page-title">
                 <h1>' . $title . '</h1>
            </div>';
	}

	return $out;

}

function kmwp_get_the_excerpt( $p = null, $apply_the_content = false ) {

	global $post;
	if ( is_int( $p ) ) $p = get_post( $p );
	else if ( is_object( $p ) ) $p = $p;
	else $p = $post;

    $content = $p->post_content;
	if ( $apply_the_content ) $content = apply_filters( 'the_content', $content );
	$content = str_replace(']]>', ']]&gt;', $content );

	return $content;

}


add_action ( 'kmwp_after_post_content', 'kmwp_after_post_content' );

function kmwp_after_post_content () {

	global $post;
	$out = '';

	if ( $post && $post->ID ) {

		if ( $post->ID == 597 ) {

			$out =
				'<div class="content-footer">' . do_shortcode( '[kmwp_get_content_form type="reverted"]' ) . '</div>';

		}

	}

	echo $out;

}


//add_action( 'the_content', 'kmwp_add_custom_content' );

function kmwp_add_custom_content( $content ) {

	global $post;

	if ( $post && $post->post_type = 'page' ) {

		$content .= kmwp_get_relative_pages( array( 'ids' => '597, 88, 102' ) );

	}

	return $content;

}


add_action( 'kmwp_footer_begin', 'kmwp_footer_begin' );

function kmwp_footer_begin() {

	global $post;

	$allowed_posts = array( 2, 173, 451 );
	$out = '';

	if ( $post && $post->ID && in_array( $post->ID, $allowed_posts ) ) {
		$out =
			'<div class="container footer-impove-section">
                <div class="f-box1">
                    <h3>Want to improve your health today</h3>
                    <button class="btn medium">Get Started</button>
                </div>
            </div>';
	}

	echo $out;

}

add_shortcode( 'get_faqs', 'get_faqs' );

function get_faqs( $args = null ) {

	$defaults = array( 'ids' => null );

	$atts = shortcode_atts( $defaults, $args );

    $faqs = get_posts( array( 'post_type' => 'faq', 'numberposts' => -1 ) );
    $out =  ''; $total_items = 0;
    $cats = $tabs_titles = $tab_items = array();

    if ( count( $faqs ) > 0 ) {

    	foreach ( $faqs as $idx => $faq ) {
    		$cat = wp_get_object_terms( $faq->ID, 'faqs', array( 'fields' => 'all_with_object_id' ) );
    		$cat = $cat[0];
    		if ( !array_key_exists( $cat->slug, $cats ) ) {
    			$cats[$cat->slug] = array( 'items' => 0, 'category' => $cat );
			    $tabs_titles[] = '<li class="tab-item-box"><a class="tab-item" data-target="' . $cat->slug . '" data-total-items="__total_' . $cat->slug . '_items__" data-active-items="8">' . $cat->name . ' (__total_' . $cat->slug . '_items__)</a></li>';
		    }
		    $cats[ $cat->slug ]['items']++;

    		$tab_items[] =
			    '<div class="tab main-tab tabs-box" data-target="' . $cat->slug . '">' .
			        '<a class="tab-link tab-item" data-target="inner-tab1" href="#">
			            <div class="bgicon tab-link-bg"></div>
			            <div class="tab-link-content"><p>' . get_the_title( $faq->ID ). '</p></div>
			            <div class="tab-link-arrow bgicon"></div>
			        </a>' .
				    '<div class="tab tab-content-box" data-target="inner-tab1">
				        <div class="tab-text">' . $faq->post_content . '</div>
				        <div class="tab-request">
				            <h3>This Information Helpful?</h3>
				            <div class="tab-request-box  justify-content-between">
				                <div class="tab-marks">
				                    <button class="bgicon tab-mark" type="button" title="1"></button>
				                    <button class="bgicon tab-mark" type="button" title="2"></button>
				                    <button class="bgicon tab-mark" type="button" title="3"></button>
				                    <button class="bgicon tab-mark" type="button" title="4"></button>
				                    <button class="bgicon tab-mark" type="button" title="5"></button>
				                </div>
				                <div class="tab-form-box">
				                    <form class="cf tab-form" method="POST" action="">
				                        <textarea class="cf-input" type="text" placeholder="Please enter a comment."></textarea>
				                        <button class="btn tab-btn">send</button>
				                        <input class="info-mark" name="mark" type="hidden">
	                                </form>
				                </div>
				            </div>
	                    </div>
	                </div>
	            </div>';

	    }

	    $tabs_titles = implode( '', $tabs_titles );
	    foreach ( $cats as $slug => $info ) {
		    $tabs_titles  = preg_replace( '/__total_' . $slug . '_items__/', $info['items'], $tabs_titles );
    		$total_items += $info['items'];
	    }
	    $tab_items = implode( '', $tab_items );

	    $tabs_titles = '<li class="tab-item-box"><a class="tab-item" data-target="" data-total-items="' . $total_items . '" data-active-items="8">All (' . $total_items . ')</a></li>' . $tabs_titles;

    	$out =
		    '<div class="tabs-container col col-lg-12">
                 <div class="tabs-titles-box">
                     <ul class="menu-faq menu tabs-titles">' . $tabs_titles. ' </ul>
                 </div>
                 <div class="tabs tab-page">' . $tab_items . '</div>
                <div class="more-button-box">
					<button class="btn medium more">view more</button>
				</div>               
            </div>';

    }

    return $out;

}


add_action( 'wp_ajax_send_form_data', 'send_form_data' );
add_action( 'wp_ajax_nopriv_send_form_data', 'send_form_data' );


function send_form_data() {

    $message = '';
	
	$result = send_data_to_collector();

	if ( intval( $result ) > 0 ) {
		echo json_encode( array( 'result' => 1, 'content' => 'Message send' ) );
	}
	else {
		echo json_encode( array( 'result' => 0, 'content' => 'Message send failed' ) );
	}

	wp_die();

}



add_action( 'wp_footer', 'kmwp_get_modal' );

function kmwp_get_modal() {

	$out =
		'<div class="modal-box">
             <div class="modal-loader"></div>
        </div>';

	echo $out;

}

//add_filter( 'post_thumbnail_html', 'img_filter', 10, 5 );

function img_filter( $html, $id, $post_thumbnail_id, $size, $attr ) {

	if ( !preg_match( '/title=/', $html ) ) {
		$img_meta       = wp_prepare_attachment_for_js( $post_thumbnail_id );
		$image_title    = $img_meta['title'] == '' ? esc_html_e('Missing title','{domain}') : $img_meta['title'];
		$a = 1;
	}

	//return $attr;
}

function banner($params){
    $return = file_get_contents(get_template_directory().'/img/banner.svg');

	if ( isset( $params['position'] ) ) $position = $params['position']; $position = '';
    $return = "<div class='svg-wrapper {$position}'>".$return."</div>";
    return $return;
}
add_shortcode('banner', 'banner');



/*add_shortcode( 'kmwp_get_read_section', 'kmwp_get_read_section' );

function kmwp_get_read_section( $args ) {

	$defaults = array( 'title' => 'regular', 'class' => 'regular', 'ids' => null );
	$atts = shortcode_atts( $defaults, $args );

    if ( !$atts['ids'] ) return;
    else $ids = explode( ',', $atts['ids'] );

    $rs_items = '';
    foreach ( $ids as $id ) {
    	$p = get_post( $id );
    	$img = get_the_post_thumbnail( $p->ID, 'related-post-thumb', array( 'class' => 'rs-img' ) );
    	$rs_items .=
		    '<div class="rs-item">
                 <div class="img-box">' . $img . '</div>
                 <div class="rsi-conten-box">
                     <div class="rsi-title">' . get_the_title( $p->ID ) . '</div>
                     <div class="rsi-content">' .  wp_trim_words( get_the_excerpt( $p ), 24, null ) . '<div>
	                 <a class="rsi-readmore blue" href="' . get_permalink( $p->ID ) . '">read more</a>
                 </div>
            </div>';
    }

	$out =
		'<div class="rs-box ' . $atts['class'] . '">
             <div class="rs-title">' . $atts['title'] . '</div>
             <div class="rs-content">' .
                  $rs_items .
             '</div>
        </div>';

	return $out;

}*/


add_filter( 'the_content', 'add_related_posts' );

function add_related_posts( $content ) {

	global $post;

	$not_allowed = array( 451 );

	if ( $post && $post->post_type == 'page' && !in_array( $post->ID, $not_allowed ) ) {

		ob_start();
		related_pages();
		$related = ob_get_contents();
		ob_end_clean();

	}
	else $related = '';

	$content .= preg_replace( '/yarpp-related/', 'yarpp-related fcw', $related );

	return $content;

}



?>