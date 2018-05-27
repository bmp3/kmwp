<?php
/*
Plugin Name: Custom Popup
Author: CA
Version: 1.0.0
*/

add_action( 'get_popup_button', 'get_popup_button', 10, 1 );

function get_popup_button( $echo ) {

	global $post;
	//return;
	$button = '<div class="button-box top-bottom-right"><button class="btn top-form-trigger">Get Free Consultation</button></div>';

	if ( !$echo )
	return $button;
	else
	echo $button;

}

add_action( 'wp_footer', 'get_top_cf' );

function get_top_cf ( $echo ) {

	global $post;
	if ( !$echo ) $echo = true;

	switch_to_blog( 1 );
	
		$form =
		'<div class="modal-box">
			<div class="modal-content top-cf-form top-bottom-right">
				<div class="modal-close close-top-form"></div>' .
				do_shortcode('[contact-form-7 id="127" title="PopUp Form"]') .
				'</div>
		</div>';

	restore_current_blog();	
		
	if ( !$echo )
	return $form;
	else
	echo $form;

}

add_action ( 'wp_footer', 'add_custom_settings' );

function add_custom_settings() {

	$style =
		'<style>
		.top-cf-form { background-color: rgb(248, 248, 248); }
		.top-cf-form .form-content { padding : 30px; }
		.top-cf-form .form-title { padding : 10px; font-size : 20px; color : rgb(56, 124, 187); font-weight : 600; text-align : center; }
		.top-cf-form .form-description { line-height : 1.4; margin-bottom : 15px; text-align : center; color : #000; }
		.top-cf-form .form-h-line { display : flex; justify-content : space-between; }
		.top-cf-form .form-h-line span { display : block; width : 45%; margin : 0 0 0 0; }
		.top-cf-form .form-h-line input { background : none; color : #000; }
		.top-cf-form .form-h-line input::-webkit-input-placeholder, .top-cf-form textarea::-webkit-input-placeholder { color : #000 !important; }
		.top-cf-form textarea { max-height : 100px; } 
		.top-cf-form input.wpcf7-submit { /*background : #3065b5 !important;*/ color : #fff; }
		.top-cf-form input.wpcf7-submit:hover { color : #fff; }
		.top-cf-form span.wpcf7-not-valid-tip { font-size : 10px; color : #000; background-color : rgb(248, 248, 248) !important; width : 100%; border : 1px solid #f30d0f; padding : 5px; }
		.top-cf-form div.wpcf7-validation-errors { border : 2px solid #f30d0f; }
		.top-cf-form div.wpcf7-response-output { margin : 1em 0.5em; }
		.top-cf-form div.wpcf7-mail-sent-ok { color : #000; }
		.top-cf-form .wpcf7-form-control-wrap { height : 78px; }
		.top-cf-form .wpcf7-form-control-wrap.your-message { height : 100px; }
		.modal-box { position : fixed; top : 0; left : 0; width : 0; height : 0; z-index : -5; background : rgba( 0, 0, 0, 0.5 ); overflow : hidden; opacity : 0; transition : opacity 0.5s;  }
		.has-modal .modal-box { z-index : 10001; width : 100vw; height : 100vh; opacity : 1; }
		.modal-content { position : absolute; max-width : 480px; top : 100%; left : 50%; transform : translate(-50%, -50%); transition : top 0.5s; }
		.has-modal .modal-content { top : 50%; }
		.modal-close { position : absolute; right : 0; top : 0; width : 30px; height : 30px; text-align : center; }
		.modal-close:hover { cursor : pointer; }
		.modal-close:before { content : "\e661"; display : inline-block; font-family  : "Nucleo Glyph"; font-size : 20px; vertical-align : middle;  }		
		
		@media only screen and (max-width: 768px) {
			.modal-content { width : 100%; }
		}		
	</style>';

	$script =
		'<script type="text/javascript">
            jQuery(document).ready( function( $ ) {
			    $("li.hide-mobile > a, #call-out a.button").on( "click", function( e ) {
					e.preventDefault();
			        $("html").addClass("has-modal");
			    });
				
				$("input.wpcf7-form-control").on( "click", function( e ) {
					if ( !$(e.target).hasClass("wpcf7-submit") ) {
					    $(e.target).parent().find("span.wpcf7-not-valid-tip").detach();
					}	
				});
			
			    $(".modal-box, .modal-close").on( "click", function( e ) {
			        if ( $(e.target).hasClass("modal-box") || $(e.target).hasClass("modal-close") )
			            $("html").removeClass("has-modal");
			    });
			});   
		</script>';


	if ( !is_admin() ) {
		echo $style;
		echo $script;
	}

}

?>