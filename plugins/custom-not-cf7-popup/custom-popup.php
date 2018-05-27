<?php
/*
Plugin Name: Custom Not CF7 Popup
Author: CA
Version: 1.0.0
*/

add_action( 'get_popup_button', 'ncf7_get_get_popup_button', 10, 1 );

function ncf7_get_popup_button( $echo ) {

	global $post;
	//return;
	$button = '<div class="button-box top-bottom-right"><button class="btn top-form-trigger">Get Free Consultation</button></div>';

	if ( !$echo )
	return $button;
	else
	echo $button;

}

add_action( 'wp_footer', 'ncf7_get_top_cf' );

function ncf7_get_top_cf ( $echo ) {

	global $post;
	if ( !$echo ) $echo = true;

	$form =
	'<div class="modal-box">
		<div class="modal-content top-cf-form top-bottom-right">
			<div class="modal-close close-top-form"></div>
			    <form class="modal-form top-form" action="">
				    <div class="form-content">
					    <div class="form-title">Get Free Consultation</div>
					    <div class="form-description">Our specialists will be in touch with you within the next 48 hours.</div>
					    <div class="form-h-line">
					        <div class="f-control-box">
					            <input class="f-input" name="name" placeholder="Name" required>
					        </div>
					        <div class="f-control-box">
					            <input class="f-input" name="age" placeholder="Age" required>
					        </div>    
					    </div>
					
					    <div class="form-h-line">
					        <div class="f-control-box">
					            <input class="f-input" name="email" placeholder="Email" required>
					        </div>
					        <div class="f-control-box">    
					            <input class="f-input" name="phone" placeholder="Phone" required>
					        </div>    
					    </div>
					
	                    <div class="f-control-box ta">				
					        <textarea class="f-textarea" name="message" placeholder="Message"></textarea>
					    </div>    
					
					    <div class="submit-area">
					        <button class="f-submit">Send</button>
	                    </div>
				    </div>
				</form>    			
			</div>
	</div>';

	if ( !$echo )
	return $form;
	else
	echo $form;

}

add_action ( 'wp_footer', 'ncf7_add_custom_settings' );

function ncf7_add_custom_settings() {

	$style =
		'<style>
		.top-form { background-color: rgb(248, 248, 248); }
		.top-form .form-content { padding : 30px; }
		.top-form .form-title { padding : 10px; font-size : 20px; color : rgb(56, 124, 187); font-weight : 600; text-align : center; }
		.top-form .form-description { line-height : 1.4; margin-bottom : 15px; text-align : center; color : #000; }
		.top-form .form-h-line { display : flex; justify-content : space-between; }
		.top-form .form-h-line > div { display : block; width : 45%; margin : 0 0 15px 0; }
		.top-form .f-control-box { position : relative; height: 78px; }
		.f-control-box.ta { height : 110px; }
		.top-form .form-h-line input, .top-form textarea { background : none; color : #000; width: 100%; padding-left: 20px; min-height: 35px; font-size: 16px; background: 0 0; border: 2px solid #ebebeb; outline: 0; resize: none; }
		.top-form  textarea { height : 100px; }
		.top-form .form-h-line input:focus, .top-form textarea:focus { border-color : #08cae8; }
		.top-form .form-h-line input::-webkit-input-placeholder, .top-cf-form textarea::-webkit-input-placeholder { color : #000 !important; }
		.top-form textarea { max-height : 100px; } 
		.top-form button.f-submit { background-color : #08cae8; border-radius: 5px; border: none; height: 36px; line-height: 35px; color: #fff; font-size: 10pt; padding: 0 20px; transition: opacity 0.7s; opacity : 1; }
		.top-form button.f-submit:hover { opacity: 0.6; cursor : pointer; }
		.top-form span.wpcf7-not-valid-tip { font-size : 10px; color : #000; background-color : rgb(248, 248, 248) !important; width : 94%; border : 1px solid #f30d0f; padding : 5px; }
		.top-form div.wpcf7-validation-errors { border : 2px solid #f30d0f; }
		.top-form div.wpcf7-mail-sent-ok { color : #000; }
		.modal-box { position : fixed; top : 0; left : 0; width : 0; height : 0; z-index : -5; background : rgba( 0, 0, 0, 0.5 ); overflow : hidden; opacity : 0; transition : opacity 0.5s;  }
		.has-modal .modal-box { z-index : 10001; width : 100vw; height : 100vh; opacity : 1; }
		.modal-content { position : absolute; max-width : 480px; top : 100%; left : 50%; transform : translate(-50%, -50%); transition : top 0.5s; }
		.has-modal .modal-content { top : 50%; }
		.modal-close { position : absolute; right : 0; top : 0; width : 30px; height : 30px; text-align : center; }
		.modal-close:hover { cursor : pointer; }
		.modal-close:before { content : "\eb14"; display : inline-block; font-family  : "fontello"; font-size : 20px; vertical-align : middle;  }		
		
		@media only screen and (max-width: 768px) {
			.modal-content { width : 100%; }
		}		
	</style>';

	$script =
		'<script type="text/javascript">
            jQuery(document).ready( function( $ ) {
			    /*$("button.btn.large").on( "click", function( e ) {
			        $("html").addClass("has-modal");
			    });*/
			
			    $(".modal-box, .modal-close").on( "click", function( e ) {
			        if ( $(e.target).hasClass("modal-box") || $(e.target).hasClass("modal-close") )
			            $("html").removeClass("has-modal");
			    });
			    
 			    $("button.f-submit").on( "click", function( e ) {
			        
 			        e.preventDefault();
					$.ajax({
						type: "POST",
						url: location.protocol + "//" + location.host + "/wp-admin/admin-ajax.php",
						data: { action: "get_lead", data: $(e.target).parents("form").serializeArray() },
						success : function ( data ) {
						    data = JSON.parse( data );
						    alert(data.message);
						},
						error : function ( data ) {
						}	        
				    });
				    
				}); 
			});    
		</script>';


	if ( !is_admin() ) {
		echo $style;
		echo $script;
	}

}

add_action('wp_ajax_get_lead', 'ncf7_get_lead');
add_action('wp_ajax_nopriv_get_lead', 'ncf7_get_lead');

function ncf7_get_lead() {

	$rules =
		array(
			'name' => array(
				'required' => array ( 'pattern' => '.+', 'result' => 1, 'message' => '__name__ is required filled' ),
				'correct'  => array ( 'pattern' => '^[a-zA-Z0-9]{2,15}$', 'result' => 1, 'message' => '__name__ must contain letters and digits, allowed length from 2 to 15 symbols' ),
			),
			'age' => array(
				'required' => array ( 'pattern' => '.+', 'result' => 1, 'message' => '__name__ is required filled' ),
				'correct'  => array ( 'pattern' => '^[0-9]{1,3}$', 'result' => 1, 'message' => '__name__ must contain digits only, max length is 3 symbols' ),
			),
			'email' => array(
				'native_method' => array (
					array( 'method' => 'filter_var', 'key' => 'FILTER_VALIDATE_EMAIL' )
				)
			),
			'phone' => array(
				'required' => array ( 'pattern' => '.+', 'result' => 1, 'message' => '__name__ is required filled' ),
				'correct'  => array ( 'pattern' => '^[0-9]{1,15}$', 'result' => 1, 'message' => '__name__ must contain digits only, max length is 15 symbols' ),
			),
			'message' => array(
				'required' => array ( 'pattern' => '.+', 'result' => 1, 'message' => '__name__ is required filled' ),
				'correct'  => array ( 'pattern' => '^.+{1, 300}$', 'result' => 1, 'message' => '__name__ must have max length is 3 symbols' ),
				'native_method' => array (
					array( 'method' => 'FILTER_SANITIZE_STRING' )
				)
			),
		);

	$fields = array( 'name', 'age', 'email', 'phone', 'message' );
	foreach ( $_POST['data'] as $i => $val ) {
		if ( in_array( $val['name'], $fields ) )
			$data[$val['name']] = $val['value'];
	}

	$headers = array(
		'From: Top Form <wordpress@' . $_SERVER['HTTP_HOST'] . '>',
	);
	$message = 'name : ' . $data['name'] . ', age : ' . $data['age'] . ', email : ' . $data['email'] . ', phone : ' . $dats['phone'] . PHP_EOL . 'message : ' . $data['message'];

	$result = wp_mail( 'cronvadim@gmail.com', $_SERVER['HTTP_HOST'] . ' - Top Form Message', $message, $headers );

	if ( $result == 1 )
		$result = array( 'result' => 1, 'message' => 'Mail Sent' );
	else
		$result = array( 'result' => 0, 'message' => 'Not Sent' );

	echo json_encode( $result );
	wp_die();

}

?>