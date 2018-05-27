<?php

/*
Plugin Name: Medical History Form plugin
Author: CA
Version: 1.0.0
*/

?>
<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The WooCommerceCustomProductTabsLite global object
 * @name $woocommerce_product_tabs_lite
 * @global WooCommerceCustomProductTabsLite $GLOBALS['woocommerce_product_tabs_lite']
 */
$GLOBALS['mhf_plugin'] = new MHF_Plugin();


register_activation_hook(__FILE__, 'mhf_set_options');
register_deactivation_hook(__FILE__, 'mhf_unset_options');



class MHF_Plugin{

    /**
     * Gets things started by adding an action to initialize this plugin once
     */
    public function __construct() {
        // Installation
        //        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) $this->install();

        // frontend stuff
        add_shortcode( 'get_medical_history_form' , array( __CLASS__, 'get_medical_history_form' ) );
        //add_action( 'wp_head', array( __CLASS__, 'integrate_css'), 100 );
        //        add_action('wp_footer',  array( __CLASS__, 'my_custom_scripts'), 100);
    }



    /*********************************************************Frontend*************************************************************/


    //    static function integrate_css()
    //    {
    //        $script = '<style type="text/css">';
    //        $script .= str_replace('url("../', 'url("'.str_replace(get_home_url(), '', plugin_dir_url( __FILE__ )), file_get_contents(__DIR__.'/css/style.css'));
    //        $script .= '</style>';
    //        echo $script;
    //    }

    //    static function my_custom_scripts()
    //    {
    //        $script = '<script type="text/javascript">';
    //        $script .= file_get_contents(__DIR__ . '/js/script.js');
    //        $script .= '</script>';
    //        echo $script;
    //    }

    static function get_web_page( $url, $data )
    {
        $fields_string = '';
        $baseUrl = 'https://ln2.crmnet.com/api/v2/mhf';
        $url = $baseUrl.'/'.$url;

        foreach($data as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
        rtrim($fields_string, '&');

        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            //            CURLOPT_FOLLOWLOCATION => 1,        // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "spider", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_POST           => count($data),
            CURLOPT_POSTFIELDS     => $fields_string,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        );

        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        return $header;
    }


    static public function start_session() {
        if(!session_id()) {
            //session_start();
        }
        //vars initialization
        if(!isset($_SESSION['viewed_posts']))
            $_SESSION['viewed_posts'] = array();
    }


    static public function get_collector_form(){
        $source_pass = '&929&*jh8';
        $source_hash = 'f23#2dLO';

        self::start_session();
        $session_possibly_expired = true;
        $data = 'error';

        while($session_possibly_expired){
            if(empty($_SESSION['session_hash'])||$_SESSION['session_hash']=='error'){
                //$response = $client->post('new-hash',['hash'=>$source_hash,'pass'=>$source_pass])->send();
                $response = self::get_web_page('new-hash',['hash'=>$source_hash,'pass'=>$source_pass]);

                if($response['http_code'] == 200) {
                    $session_hash = $response['content'];
                    if ($session_hash == 'error') {
                        return 'error';
                    }
                    $_SESSION['session_hash'] = $session_hash;
                }
                else{
                    return 'error';
                }
            }

            $session_hash = $_SESSION['session_hash'];

            $response = self::get_web_page('get-mhf',['hash'=>$source_hash,'pass'=>$source_pass,'session_hash'=>$session_hash]);
            if($response['http_code']==200){
                $data = $response['content'];
            }

            switch($data){
                case 'session expired':
                    unset($_SESSION['session_hash']);
                    break;
                default:
                    $session_possibly_expired = false;
                    break;
            }
        }

        return json_decode($data,true);
    }

    static function get_medical_history_form(){
        $form_data = self::get_collector_form();
        $form = $form_data;
        //        var_dump($form);
        //        exit;
        $js = [];
        $css = [];
        if(is_array($form)){
            $js = isset($form['js'])?$form['js']:[];
            $css = isset($form['css'])?$form['css']:[];
            $form = $form['form'];
        }

        $num = 1;
        foreach ($js as $style) {
            //var_dump('heare_'.$num);
            wp_enqueue_script( 'mhf_script_'.$num, $style, [], true, 1);
            $num++;
        }

        $num = 1;
        foreach ($css as $style) {
            //var_dump('heare_'.$num);
            wp_enqueue_style( 'mhf_script_'.$num, $style, [], true);
            $num++;
        }
        $form = "<script>
                    var CommonParams = {
                        host:'ln2.crmnet.com',
                        source:'kingsbergmedical.com',
                        session_hash: '{$_SESSION['session_hash']}'
                    }
                </script>".$form;


        return $form;
    }
}

