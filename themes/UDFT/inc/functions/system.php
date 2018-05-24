<?php

function kmwp_get_sidebar_content( $sidebar_id, $mode = 'return' ) {

	ob_start();
	dynamic_sidebar( $sidebar_id );
	$out = ob_get_contents();
	ob_end_clean();

	return $out;

}

function kmwp_get_template_part ( $template, $part_name = null, $mode = 'return' ) {

	if ( $mode == 'return' ) {
		ob_start();
		get_template_part( $template, $part_name );
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}
	else {
		get_template_part( $template );
	}

}

function kmwp_get_header_content() {

	global $post, $kmwp;
    $out = '';

    if ( $kmwp['width-layout'] == 1 ) {
        $kmwp['box-type'] = 'container-fluid';
    }
    else if ( $kmwp['width-layout'] == 2 ) {
        $kmwp['box-type'] = 'container';
    }

    $kmwp['header_calc_bg'] = 'style="background-image:url(' .  $kmwp['header-bg']['url'] . ');"';
    if ( $kmwp['header-content'] == 1 ) $template = 'templates/header/header-text';
    else if ( $kmwp['header-content'] == 2 ) $template = 'templates/header/header-form';
    else if ( $kmwp['header-content'] == 3 ) $template = 'templates/header/thank-you-block';
    else $template = 'templates/header/header-text';
    $out = kmwp_get_template_part( $template );

	return $out;

}


add_action('init', 'register_post_types');
function register_post_types(){


	$labels = array(
		'name'              => _x( 'FAQs', 'taxonomy general name', 'textdomain' ),
		'singular_name'     => _x( 'FAQ', 'taxonomy singular name', 'textdomain' ),
		'search_items'      => __( 'Search FAQs', 'textdomain' ),
		'all_items'         => __( 'All FAQs', 'textdomain' ),
		'parent_item'       => __( 'Parent FAQ', 'textdomain' ),
		'parent_item_colon' => __( 'Parent FAQ:', 'textdomain' ),
		'edit_item'         => __( 'Edit FAQ', 'textdomain' ),
		'update_item'       => __( 'Update FAQ', 'textdomain' ),
		'add_new_item'      => __( 'Add New FAQ category', 'textdomain' ),
		'new_item_name'     => __( 'New FAQ Name', 'textdomain' ),
		'menu_name'         => __( 'FAQ categories', 'textdomain' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'faqs' ),
	);

	register_taxonomy( 'faqs', array( 'faq' ), $args );

	register_post_type('faq', array(
		'label'  => 'FAQ',
		'labels' => array(
			'name'               => 'FAQ',
			'singular_name'      => 'FAQ',
			'add_new'            => 'Add FAQ',
			'add_new_item'       => 'Adding FAQ',
			'edit_item'          => 'Edit FAQ',
			'new_item'           => 'New FAQ',
			'view_item'          => 'View FAQ',
			'search_items'       => 'Search FAQ',
			'not_found'          => 'Not found FA@Qs',
			'not_found_in_trash' => 'Not found FAQs in trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'FAQs',
		),
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => null,
		'exclude_from_search' => null,
		'show_ui'             => null,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => null,
		'show_in_nav_menus'   => null,
		'show_in_rest'        => true,
		'rest_base'           => null,
		'menu_position'       => 40,
		'menu_icon'           => null,
		//'capability_type'   => 'post',
		//'capabilities'      => 'post',
		//'map_meta_cap'      => null,
		'hierarchical'        => false,
		'supports'            => array('title', 'editor', 'excerpt'),
		'taxonomies'          => array( 'faqs' ),
		'has_archive'         => false,
		'rewrite'             => true,
		'query_var'           => true,
	) );
}


add_action( 'admin_init', 'kmwp_admin_init' );

function kmwp_admin_init() {

    add_action( 'admin_enqueue_scripts', 'kmwp_get_admin_scripts', 99 );
    add_action( 'add_meta_boxes', 'kmwp_get_metabox' );
    add_action( 'wp_ajax_save_post_settings', 'kmwp_post_settings' );
    add_action( 'save_post', 'kmwp_save_post_settings' );

}

function kmwp_get_admin_scripts() {

    //wp_enqueue_style( 'kmwp_admin_bootstrap', get_stylesheet_directory_uri() . '/css/template.css' );
    //wp_enqueue_style( 'kmwp_admin_bootstrap', get_stylesheet_directory_uri() . '/css/bootstrap-system.css' );
    //wp_enqueue_style( 'kmwp_admin_font-awesome', get_stylesheet_directory_uri() . '/css/font-awesome.css' );
    wp_enqueue_style( 'kmwp_admin_css', get_stylesheet_directory_uri() . '/css/admin/admin.css' );
    wp_enqueue_script( 'jquery-ui-core', array('jquery'));
    wp_enqueue_script( 'kmwp_admin_js', get_stylesheet_directory_uri() .  '/js/admin/admin.js', array('jquery', 'jquery-ui-core') );

}

function kmwp_get_metabox() {

    global $post;

    add_meta_box( __( 'Settings', '' ), __( 'Settings', '' ), 'kmwp_temp_post_metabox_process', array ( 'post', 'page' ), 'normal', 'low' );

}

function kmwp_temp_post_metabox_process() {

    $out =
        '<div class="kmwp-post-settings">
            <div class="reset-box">
            <button class="btn reset-post-settings">reset to global</button>
            <input class="ps-reset" name="reset-ps" value="0">
            <input class="ps-changed" name="ps-changed" value="0">
            </div>
        </div>';

    echo $out;
}


function kmwp_post_metabox_process() {

    global $post, $kmwp;

    $meta = get_post_meta( $post->ID, 'post-settings', true );
    if ( $meta != '') $meta = json_decode( $meta, true ); else $meta = array();
    $meta = correct_global_var( $kmwp, $meta );

    $out =
        '<div class="kmwp-post-settings">
            <div class="reset-box">
            <button class="btn reset-post-settings">reset to global</button>
            <input class="ps-reset" name="reset-ps" value="0">
            <input class="ps-changed" name="ps-changed" value="0">
            </div>
            <div class="post-settings tabs-box">
                <div class="tab-nav row">
                    <div class="tab-item col"><a class="tab-a" data-trigger="layout">layout</a></div>
                    <div class="tab-item col"><a class="tab-a" data-trigger="header">header</a></div>
                    <div class="tab-item col"><a class="tab-a" data-trigger="content">content</a></div>
                    <div class="tab-item col"><a class="tab-a" data-trigger="footer">footer</a></div>
                </div> 
                <div class="tab-contents">
                    <div class="tab-content-item" data-target="layout">
                        <div class="tab-item">
                            <div class="width-layout row">
                                <div class="control-title col">Choose page layout</div>
                                <div class="control-values col">
                                    <div class="control-value img-control" data-value="1"><img src="' . get_template_directory_uri() . '/img/admin/full.gif' . '"></div>
                                    <div class="control-value img-control" data-value="2"><img src="' . get_template_directory_uri() . '/img/admin/boxed.gif' . '"></div> 
                                </div> 
                                <input class="ps-input" name="ps[width-layout]" value="' . $meta['width-layout']. '">                           
                            </div>
                            <div class="width-layout row">
                                <div class="control-title col">Choose sidebar layout</div>
                                <div class="control-values col">
                                    <div class="control-value img-control" data-value="1"><img src="' . get_template_directory_uri() . '/img/admin/nosidebar.gif' . '"></div>
                                    <div class="control-value img-control" data-value="2"><img src="' . get_template_directory_uri() . '/img/admin/2sidebars.gif' . '"></div>
                                    <div class="control-value img-control" data-value="3"><img src="' . get_template_directory_uri() . '/img/admin/leftsidebar.gif' . '"></div>
                                    <div class="control-value img-control" data-value="4"><img src="' . get_template_directory_uri() . '/img/admin/rightsidebar.gif' . '"></div>   
                                </div> 
                                <input class="ps-input" name="ps[sidebar-layout]" value="' . $meta['sidebar-layout']. '">                           
                            </div>                            
                        </div>
                    </div>
                    <div class="tab-content-item" data-target="header">
                        <div class="tab-item">
                        
                        </div>
                    </div>
                    <div class="tab-content-item" data-target="content">
                        <div class="tab-item"></div>
                    </div>
                    <div class="tab-content-item" data-target="footer">
                        <div class="tab-item"></div>
                    </div>                                      
                </div>   
           </div> 
        </div>';

    echo $out;

}

function kmwp_save_post_settings(){

    global $post, $kmwp;

    if ( isset( $_POST['ps'] ) && is_array( $_POST['ps'] ) ) {
        if ( $_POST['reset-ps'] == 0 && $_POST['ps-changed'] == 1 ) {
            $ps = json_encode($_POST['ps']);
            update_post_meta($post->ID, 'post-settings', $ps);
        }
        else if ( $_POST['reset-ps'] == 1 ) {
            delete_post_meta( $post->ID, 'post-settings' );
        }
    }

    if ( isset( $_POST['reset-ps'] ) && $_POST['reset-ps'] == 1 ) {
        $fields = array( 'width-layout', 'sidebar-layout', 'header-bg', 'header-content', 'header-text-img', 'header-form-img', 'thank-you-img' );
        foreach ( $fields as $f ) {
            delete_field( $f, $post->ID);
        }
    }

}


function kmwp_acf_save_post( $post_id ) {

	$var_fields = array( 'width-layout', 'sidebar-layout', 'header-bg', 'header-content', 'header-text-img', 'header-form-img', 'thank-you-img' );
	$var_fields = array( '5aae58990c1c5', '5aae594e0c1c6', '5aae62def4386', '5aae6329f4387', '5aae6426f4388', '5aae64b5f4389', '5aaf98844d81c' );

    if ( isset( $_POST['_acfchanged'] ) && $_POST['_acfchanged'] == 0 ) {
        $_POST['acf'] = array();
    }

	if ( isset( $_POST['ps-changed'] ) && $_POST['ps-changed'] == 0 ) {
	     foreach ( $var_fields as $f ) {
	         if ( isset( $_POST['acf']['field_' . $f] ) )
	         	unset( $_POST['acf']['field_' . $f] );
	     }
	}

	$a = 1;

}

add_filter('acf/save_post' , 'kmwp_acf_save_post', 1, 1 );


function correct_global_var( $global = null, $meta = null ) {

    global $post, $kmwp;

    if ( !$global )
        $global = $kmwp;

    if ( $post && $post->ID ) {
        $self = array();
        $fields = array( 'width-layout', 'sidebar-layout', 'header-bg', 'header-content', 'header-text-img', 'header-form-img', 'thank-you-img' );
        $global['fields'] = $fields;
        foreach ( $fields as $i => $field ){
            $val = get_field( $field, $post->ID );
            if ( $val )
                $self[$field] = $val;
        }
        foreach ( $self as $index => $value ) {
            if ( isset ( $global[$index] ) )
                $global[$index] = $value;
        }
    }

    return $global;

}



function kmwp_acf_load_field( $field ) {

    global $post, $kmwp, $wpdb;

    if ( !$post ) $pid = $_POST['post_id']; else $pid = $post->ID;

    $query = 'SELECT * FROM ' . $wpdb->prefix . 'postmeta WHERE post_id = ' . $pid . ' AND meta_key = "' . $field['_name'] . '"';
    $f = $wpdb->get_results( $query );

    if ( is_array( $f ) && count( $f ) == 0 ) {
        $val = $kmwp[$field['_name']];
        $field['default_value'] = $val;
        if( $field['type'] == 'image' ) {
            $field['value'] = $val['id'];
        }
    }

	return $field;

}

add_filter('acf/load_field/name=width-layout', 'kmwp_acf_load_field');
add_filter('acf/load_field/name=sidebar-layout', 'kmwp_acf_load_field');
add_filter('acf/load_field/name=header-bg', 'kmwp_acf_load_field');
add_filter('acf/load_field/name=header-content', 'kmwp_acf_load_field');
add_filter('acf/load_field/name=header-text-img', 'kmwp_acf_load_field');
add_filter('acf/load_field/name=header-form-img', 'kmwp_acf_load_field');
add_filter('acf/load_field/name=thank-you-img', 'kmwp_acf_load_field');



?>