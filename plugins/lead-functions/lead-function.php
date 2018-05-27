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

	//$_SESSION['collector_lead_id'] = $result;
	$a = 1;
	echo "1111111111111111";
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
	if ($_SERVER['HTTP_CLIENT_IP'])
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if($_SERVER['HTTP_X_FORWARDED_FOR'])
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if($_SERVER['HTTP_X_FORWARDED'])
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if($_SERVER['HTTP_FORWARDED_FOR'])
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if($_SERVER['HTTP_FORWARDED'])
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if($_SERVER['REMOTE_ADDR'])
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
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
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


?>