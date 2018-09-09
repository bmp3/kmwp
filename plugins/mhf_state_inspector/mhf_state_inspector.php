<?php

/*
Plugin Name: MHF State Inspector
Author: CA
Version: 1.0.0
*/


add_action ( 'wp_footer', 'add_medical_form_popup' );

function add_medical_form_popup() {

global $post;

$out = '';

if ( $post && preg_match( '/medical\shistory\sform/i', $post->post_title ) ) {
$out =
'<div class="session-modal-box">
	<div class="modal-content-box activity-modal">
		<!--<div class="modal-close close-modal">x</div>-->
		<div class="modal-content">
			<div class="modal-text">OK</div>
			<div class="buttons-box">
				<div class="button-block approve">yes</div>
				<div class="button-block reject">no</div>
			</div>
		</div>
	</div>
</div>';

$out .=
'<style>
	.session-modal-box { position : fixed; top : 0; left : 0; width : 0; height : 0; z-index : -5; background : rgba( 0, 0, 0, 0.5 ); overflow : hidden; opacity : 0; transition : opacity 0.5s; }
	.has-session-modal .session-modal-box { z-index : 10001; width : 100vw; height : 100vh; opacity : 1; }
	.session-modal-box .modal-content-box { position : absolute; left : 50%; top : 50%; transform : translate(-50%, -50%); width : 100%; max-width : 320px; background-color : rgba( 255, 255, 255, 0.8 ); }
	.session-modal-box .modal-close { position : absolute; top : 10px; right : 10px; width : 15px; height : 15px; font-size : 15px; }
	.session-modal-box .modal-content { padding : 30px; }
	.session-modal-box .modal-text { text-align : center; font-size : 18px; }
	.session-modal-box .buttons-box { display : flex; justify-content : space-between; align-items : center; }
	.session-modal-box .button-block { padding : 10px 15px; color : #fff; border-radius : 15px; }
	.session-modal-box .button-block:hover { cursor : pointer; }
	.session-modal-box .button-block.approve { background-color : #42c044; }
	.session-modal-box .button-block.reject { background-color : rgb(245, 104, 77); }

	@media only screen and (max-width: 768px) { }

</style>';

$out .=
	'<script  type="text/javascript">
	jQuery(document).ready( function( $ ) {

		var form_status_object = { "session_hash" : CommonParams.session_hash },
        mainTimer, popupTimer, session_state = false;
		
		function do_ajax( op ) { 
		    
		    var action = "get_status";
		    
		    if ( op ) {
		        action = op;
		        form_status_object.extend = 1;
		    }
		    else  {
		        delete form_status_object.extend;
		    }
		    
            $.ajax({
				type: "POST",
				url: "https://ln2.crmnet.com/api/v2/mhf/get-time-out",
				data: { "session_hash" : CommonParams.session_hash, "extend" : form_status_object.extend },
				success : function ( data ) {
				    //alert(data.timeout);
				    if ( ( !data || data.timeout < 1770 && !$("html body").hasClass("has-session-modal") ) ) {
			            $(".modal-text").html("are you here?");
			            $("html, body").addClass("has-session-modal");
			            popupTimer = setTimeout( function() {
				            location.assign("/medical-history-form/");
			            }, 5*60000);
		            }			    
				},
				error : function ( data ) {
				}	        
		    });
		    
		}

	    $(".page-medical-history-form").on( "keypress", function ( e ) {
		    clearTimeout(popupTimer);
	    });
	
	    $(".page-medical-history-form").on( "mousemove", function ( e ) {
		    clearTimeout(popupTimer);
	    });
	  
	    mainTimer = setInterval( function() {
	        do_ajax( );
	    }, 5*1000);
	
	    $(".button-block.approve").on( "click", function( e ) {
	        do_ajax( "prolong_session" )
		    $("html, body").removeClass("has-session-modal");
		    clearTimeout(popupTimer);
	    });
	
	    $(".button-block.reject").on( "click", function( e ) {
		    clearTimeout(popupTimer);
		    location.assign("/medical-history-form/");
	    });

});
</script>';

}
echo $out;

}

add_action( 'wp_ajax_get_session_state', 'get_session_state' );
add_action( 'wp_ajax_nopriv_get_session_state', 'get_session_state' );

function get_session_state() {

if ( $_POST['data']['status']['form_status'] == 1 ) {
$result = array( 'session_status' => 1 );
}
else {
$result = array( 'session_status' => 0 );
}

echo json_encode( $result );

wp_die();

}
