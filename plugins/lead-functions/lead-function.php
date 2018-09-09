<?php

/*
Plugin Name: CA Lead Form Functions
Author: CA
Version: 1.0.0
*/


function send_data_to_collector(){
	$post = $_POST;
	$data = array();
	$address = get_address_by_ip();

	$data['hash'] = 'o6WylVb8lb';
	$data['pass'] = 'TbIINa02FA';

	$data['first_name'] = $post['name'];
	$data['last_name'] = 'test';
	$data['email'] = $post['email'];
	$data['phone'] = $post['phone'];
	$data['ip'] = $address['ip'];
	$data['city'] = $address['city'];
	$data['state'] = $address['state'];
	$data['country'] = $address['country'];
	$data['description'] = (isset($post['Interested-in'])?$post['Interested-in']:$post['message']);
	$data['lead_source_url'] = curPageURL();
	$data['user_agent'] = get_client_data()['user_agent_raw'];

	$result = send_lead_to_collector('https://ln2.crmnet.com/api/v2/lead/collect', $data);

}

function send_lead_to_collector($url, $data=array()){

	$fields_string = '';

	foreach($data as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
	rtrim($fields_string, '&');

	$ch = curl_init();

	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, count($data));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

	$result = curl_exec($ch);

	curl_close($ch);
	return $result;
}


function get_address_by_ip(){
	if (isset( $_SERVER['HTTP_CLIENT_IP'] ) )
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if( isset( $_SERVER['HTTP_X_FORWARDED'] ) )
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) )
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if( isset( $_SERVER['HTTP_FORWARDED'] ) )
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if( isset( $_SERVER['REMOTE_ADDR'] ) )
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = 'UNKNOWN';

    $ipaddress = '32.212.229.170';

	$json = file_get_contents("http://ipinfo.io/{$ipaddress}");
	$usr_location1 = json_decode($json);

	$address = array(
		'ip' => $ipaddress
	);


	$address['city'] = isset($usr_location1->city)?$usr_location1->city:'';
	//    $address['city'] = '';
	$address['state'] = isset($usr_location1->region)?$usr_location1->region:'';
	//    $address['state'] = '';
	$address['country'] = isset($usr_location1->country)?$usr_location1->country:'';
	//    $address['country'] = '';

	$usr_location2 = null;
	foreach ($address as $item => $value) {
		if(empty($value)){
			if(empty($usr_location2)){
				$json = file_get_contents("http://api.db-ip.com/v2/e59af6bf0eb4d01964b4afadbebaaf41067e16a5/{$ipaddress}");
				$usr_location2 = json_decode($json);
			}
			$newVal = '';
			switch($item){
				case 'state':
					$newVal = $usr_location2 -> stateProv;
					break;
				case 'city':
					$newVal = $usr_location2 -> city;
					break;
				case 'country':
					$newVal = $usr_location2 -> countryCode;
					break;
			}

			$address[$item] = $newVal;
		}
	}

	return $address;
}


function curPageURL() {
	$pageURL = 'http';
	if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) {$pageURL .= "s";}
	$pageURL .= "://";
	$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

	return $pageURL;
}

function get_client_data(){
	$user_info['user_agent_raw'] = $_SERVER['HTTP_USER_AGENT'];
	return $user_info;
}


function wpb_disable_feed() {
	wp_die( __('No feed available,please visit our <a href="'. get_bloginfo('url') .'">homepage</a>!') );
}

add_action('do_feed', 'wpb_disable_feed', 1);
add_action('do_feed_rdf', 'wpb_disable_feed', 1);
add_action('do_feed_rss', 'wpb_disable_feed', 1);
add_action('do_feed_rss2', 'wpb_disable_feed', 1);
add_action('do_feed_atom', 'wpb_disable_feed', 1);
add_action('do_feed_rss2_comments', 'wpb_disable_feed', 1);
add_action('do_feed_atom_comments', 'wpb_disable_feed', 1);



add_action('phpmailer_init','send_smtp_email');
function send_smtp_email( $phpmailer )
{
	// Define that we are sending with SMTP
	$phpmailer->isSMTP();
	//$phpmailer->SMTPDebug = 4;

	// The hostname of the mail server
	$phpmailer->Host = "smtp.gmail.com";

	// Use SMTP authentication (true|false)
	$phpmailer->SMTPAuth = true;

	// SMTP port number - likely to be 25, 465 or 587
	$phpmailer->Port = "465";

	// Username to use for SMTP authentication
	$phpmailer->Username = "info@kingsbergmedical.com";

	// Password to use for SMTP authentication
	$phpmailer->Password = "Florida_7788";

	// The encryption system to use - ssl (deprecated) or tls
	$phpmailer->SMTPSecure = "ssl";

	$phpmailer->From = "info@kingsbergmedical.com";
	/*$info = get_post_info();

	if ( isset( $_POST['data'][0]['value'] ) ) $name = $_POST['data'][0]['value'];
	else $name = $_POST['your-name'];*/
	$name = 'Kingsberg Medical';

	$phpmailer->FromName = $name;
	
	$phpmailer->smtpConnect(
		array(
			"ssl" => array(
				"verify_peer" => false,
				"verify_peer_name" => false,
				"allow_self_signed" => true
			)
		)
	);
	
}


function kmwp_send_mails( $emails = null ) {

	if ( $emails ) {
		$emails = explode( ',', $emails );
	}
	else {
		return false;
	}


	$reasons = $data = array();

	$fields = array( 'name', 'age', 'email', 'phone', 'message', '_weight_i' );
	foreach ( $_POST as $i => $val ) {
		if ( in_array( $i, $fields ) ) {
			$n = $i;
			$v = $val;
			$valid = true;
			if ( $n == 'name' && !preg_match( '/^[a-zA-Z0-9]{2,15}.?[a-zA-Z0-9]{2,15}?$/', $v ) ) {
				$valid = false;
				$reasons[$n] = 'NAME field must contain letters and digits, allowed length from 2 to 15 symbols';
			}
			if ( $n == 'age' && !preg_match( '/[0-9]{1,3}$/', $v ) ) {
				$valid = false;
				$reasons[$n] = 'AGE field must contain digits only, allowed length from 2 to 3 symbols';
			}
			if ( $n == 'email' && !filter_var( $v, FILTER_VALIDATE_EMAIL ) ) {
				$valid = false;
				$reasons[$n] = 'EMAIL field must be valid email address';
			}
			if ( $n == 'phone' && !preg_match( '/^((\+\d{1,3})[\- ]?)?(\(?\d{2,3}\)?[\- ]?)?[\d\- ]{7,10}$/', $v ) ) {
				$valid = false;
				$reasons[$n] = 'PHONE field must be valid phone number ( example: +1(617)4017199 )';
			}
			if ( $n == 'message' && ( !preg_match( '/.+$/', $v ) || strlen( $v ) > 300 ) ) {
				$valid = false;
				$reasons[$n] = 'MESSAGE field must any text less 300 symbols';
			}
			else $val = filter_var( $val, FILTER_SANITIZE_STRING );
			if ( $valid ) $data[$i] = $val;
			$data[$i] = $val;

		}
	}


	$subject = 'New lead from ' . $_SERVER['HTTP_HOST'];
	if ( strlen( $_POST['_weight_i'] ) > 0 ) {
		$subject = $_SERVER['HTTP_HOST'] . ' - SPAM';
	}

	$headers = array(
		'From: '. $data['name'] . ' <info@kingsbergmedical.com>',
		'Content-type: text/html'
	);

	$address = get_address_by_ip();

	$message =
		'<body style="font-family: Georgia, "Times New Roman", Times, serif;">
			<div style="width: 50%; margin: auto;">
			  <table style="width: 98%; float: right; border: 1px solid #86a3cb;">
			    <tr>
			      <td height="50" colspan="2" align="center" style="white-space: nowrap; vertical-align: middle; font-weight: bold; font-size: 20px;">Lead information</td>
			    </tr>
			    <tr bgcolor="#DCDCDC" style="vertical-align: top;">
			      <td height="50" style="white-space: nowrap; font-weight: bold; font-size: 16px; vertical-align:middle; color:#000;">Date: </td>
			      <td style="font-size: 14px; vertical-align:middle; color:#000;">' . date( 'm/d/Y H:i:s' ) . '</td>
			    </tr>
			    <tr bgcolor="#A9A9A9" style="vertical-align: top;">
			      <td height="50" style="white-space: nowrap;  font-weight: bold; font-size: 16px; vertical-align:middle; color:#000;">Name: </td>
			      <td style="font-size: 14px; vertical-align:middle; color:#000;">' . $data['name'] . '</td>
			    </tr>
			    <tr bgcolor="#DCDCDC" style="vertical-align: top;">
			      <td height="50" style="white-space: nowrap;  font-weight: bold; font-size: 16px; vertical-align:middle; color:#000;">Email address: </td>
			      <td style="font-size: 14px; vertical-align:middle; color:#000;"><a href="mailto:' . $data['email'] . '">' . $data['email'] . '</a></td>
			    </tr>
			    <tr bgcolor="#A9A9A9" style="vertical-align: top;">
			      <td height="50" style="white-space: nowrap;  font-weight: bold; font-size: 16px; vertical-align:middle; color:#000;">Telephone: </td>
			      <td style="font-size: 14px; vertical-align:middle; color:#000;">' . $data['phone'] . '</td>
			    </tr>' .

		        /*
			    <tr bgcolor="#DCDCDC" style="vertical-align: top;">
			      <td height="50" style="white-space: nowrap;  font-weight: bold; font-size: 16px; vertical-align:middle; color:#000;">Age: </td>
			      <td style="font-size: 14px; vertical-align:middle; color:#000;">' . $data['age'] . '</td>
			    </tr>
		        */

			    '<tr bgcolor="#A9A9A9" style="vertical-align: top;">
			      <td height="50" style="white-space: nowrap;  font-weight: bold; font-size: 16px; vertical-align:middle; color:#000;">Message: </td>
			      <td style="font-size: 14px; vertical-align:middle; color:#000;">' . $data['message'] . '</td>
			    </tr>
			    <tr bgcolor="#DCDCDC" style="vertical-align: top;">
			      <td height="50" style="white-space: nowrap;  font-weight: bold; font-size: 16px; vertical-align:middle; color:#000;">IP: </td>
			      <td style="font-size: 14px; vertical-align:middle; color:#000;">' . $address['ip'] . '</td>
			    </tr>
			    <tr bgcolor="#A9A9A9" style="vertical-align: top;">
			      <td height="50" style="white-space: nowrap;  font-weight: bold; font-size: 16px; vertical-align:middle; color:#000;">State: </td>
			      <td style="font-size: 14px; vertical-align:middle; color:#000;">' . $address['state'] . '</td>
			    </tr>
			    <tr bgcolor="#DCDCDC" style="vertical-align: top;">
			      <td height="50" style="white-space: nowrap;  font-weight: bold; font-size: 16px; vertical-align:middle; color:#000;">City: </td>
			      <td style="font-size: 14px; vertical-align:middle; color:#000;">' . $address['city'] . '</td>
			    </tr>
			    <tr bgcolor="#A9A9A9" style="vertical-align: top;">
			      <td height="50" style="white-space: nowrap;  font-weight: bold; font-size: 16px; vertical-align:middle; color:#000;">Source Page: </td>
			      <td class="domain-box" style="font-size: 14px; vertical-align:middle; color:#000;">' . $_SERVER['HTTP_REFERER'] . '</td>
			    </tr>
			  </table>
			  <div style="clear: both;"> </div>
			</div>
		</body>';

	$result1 = wp_mail( $emails, $subject, $message, $headers );

	if ( !preg_match( '/spam/i', $subject ) ) {

		$emails  = array( $data['email'] );
		$subject = 'Welcome to Kingsberg Medical';
		$message =
			'<p>' . $data['name'] . ', Congratulations!</p><br />
         <p>Welcome to Kingsberg Medical!  You have taken the first step toward a vastly improved life. </p>
         <p>We have received your initial inquiry.  In keeping with our Privacy Policy, your information will be held in the strictest confidence.  No one will ever see this information except our doctors and their assistants.</p>
         <p>We will contact you by phone very soon to discuss all the details.  Rest assured, we are here to answer all of your questions.  We will be happy to explain all the positive changes you can look forward to in your body and in your life.</p>
         <p>Live Youthfully!</p>
         <br /><br />

         <p>George Kingsberg<br />
         Phone: 800-787-0408<br />
         Fax:  954-321-8882<br />
         email: <a href="mailto:info@kingsbergmedical.com">info@kingsbergmedical.com</a><br />
         <a href="http://www.kingsbergmedical.com" target="_blank">www.kingsbergmedical.com</a><br />
         </p>';

		$result2 = wp_mail( $emails, $subject, $message, $headers );

	}
	else $result2 = 1;

	$result = $result1 and $result2;

	return $result;

}


?>