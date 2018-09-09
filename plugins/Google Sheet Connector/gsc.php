<?php
/*
Plugin Name: WP Google Connector
Author: SecretLab
Version: 1.0.0
Text-domain: gsc
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class GSC {

    static $client;
    static $service;
    static $settings;
    static $forms_settings;
    static $spreadsheetId;
    static $sheet;

    static function init() {

        require __DIR__ . '/vendor/autoload.php';

        self::$settings = get_option( 'gsc-settings' );
        self::$forms_settings = get_option( 'gsc-forms' );
        if ( isset( self::$settings['extra']['googlesheetID'] ) )
            self::$spreadsheetId = self::$settings['extra']['googlesheetID'];

        add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
        add_action( 'admin_menu', array( __CLASS__, 'add_forms_page' ) );
        add_action ( 'admin_enqueue_scripts', function() {
            wp_enqueue_script( 'jquery-ui-core' );
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'jquery-ui-draggable' );
            wp_enqueue_style('gsc-css', plugins_url(false, __FILE__ ).'/assets/css/gsc.css', time(), true);
            wp_enqueue_script('gsc-js', plugins_url(false, __FILE__ ).'/assets/js/gsc.js', array('jquery'), time(), true);
        });

        add_action( 'wp_ajax_gsc_save_extra_settings', array( __CLASS__, 'save_extra_settings' ) );
        add_action( 'wp_ajax_gsc_save', array( __CLASS__, 'save_form_settings' ) );
        add_action( 'wpcf7_mail_sent', array( __CLASS__, 'save_data_to_google_sheet' ), 10, 1 );

        add_action( 'admin_enqueue_scripts', function() {
            wp_localize_script( 'jquery', 'ajaxdata',
                array(
                    'siteurl' => site_url(),
                    'dictionary' => array( 'Enabled' => __( 'Enablad', 'gsc' ), 'Disabled' => __( 'Disabled', 'gsc' ) )
                ) );
             }, 99 );

    }

    static function add_menu_page() {

        add_menu_page( __( 'Google Sheets Connector', 'gsc' ), __( 'Google Sheets Connector', 'gsc' ), 'manage_options', 'gsc.php', array( __CLASS__, 'render_gsc_admin_page' ), 'menu-icon-generic', 30 );

    }

    static function add_forms_page( ) {

        if ( isset( self::$settings['extra']['googlesheetID'] ) ) {
            add_submenu_page('gsc.php', __('Forms', 'gsc'), __('Forms', 'gsc'), 'manage_options', 'gsc-forms.php', array(__CLASS__, 'render_gsc_forms_page'));
        }

    }

    static function get_client( $get_template = false ) {

        $client = new Google_Client();
        $client->setApplicationName( __( 'WP Google Sheets Connector', 'gsc' ) );
        $client->setScopes( Google_Service_Sheets::SPREADSHEETS );

        //$client->setClientId( '99608917814-6s2p3q7b5imspsmgfgaophiommmbqo0c.apps.googleusercontent.com' );
        $client->setClientId( '349409689691-tot7orjabepo3jag1pnpbpdt00b4r7sk.apps.googleusercontent.com' );
        //$client->setClientSecret( 'TMB_p9Bt3fq0qrt1nYMMaWAH' );
        $client->setClientSecret( 'WALGzZdGJKo6wX-a4s4HBNiD' );
        //$client->setRedirectUri( site_url() . '/wp-admin/admin.php?page=gsc.php' );
        $client->setRedirectUri( 'urn:ietf:wg:oauth:2.0:oob' );
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        if ( $get_template )
            return $client;

        if ( self::$settings && is_array( self::$settings ) && is_array( self::$settings['access_data'] ) ) {
            if ( isset( self::$settings['access_data']['access_token'] ) ) {
                if ( $client->isAccessTokenExpired() ) {
                    if ( isset( self::$settings['access_data']['refresh_token'] ) ) {
                        $refreshToken = self::$settings['access_data']['refresh_token'];
                        $client->fetchAccessTokenWithRefreshToken($refreshToken);
                        $token = $client->getAccessToken();
                        $accessToken = $client->getAccessToken();
                        self::$settings['access_data']['refresh_token'] = $refreshToken;
                        update_option( 'gsc-settings', self::$settings );
                    }
                }
            }
            return $client;
        }

        return false;

    }


    static function render_gsc_admin_page() {

        $settings = self::$settings;
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'refresh_access_data' ) {
            delete_option( 'gsc-settings' );
            self::$settings = null;
        }
        else if ( isset( $_POST['action'] ) ) {
            if ( $_POST['action'] == 'submit-activation-code' ) {
                $settings['extra']['activation_code'] = $_POST['google-activation-code'];
            }
            else if ( $_POST['action'] == 'submit-ssid' ) {
                $settings['extra']['googlesheetID'] = $_POST['ssid'];
            }
            update_option( 'gsc-settings', $settings );
        }
        self::$settings = get_option( 'gsc-settings' );


        if ( !self::$settings || !isset( self::$settings['access_data']['access_token'] )  ) {

            $client = self::get_client(true );

            if ( !isset( self::$settings['extra']['activation_code'] ) ) {

                self::$settings = array();
                $authUrl = $client->createAuthUrl();

                $out = '<div class="get-google-access">
                            <div class="title-box">' . __( 'To save Contact Form 7 data to Google Sheets you need : ', 'gsc' ) . '</div>                
                            <a class="gsc-link" href="' . filter_var($authUrl, FILTER_SANITIZE_URL) . '" target="_blank">' . __( 'get google access code', 'gsc' ) . '</a>
                            <div class="input-box">
                                <input class="gsc-input gsc-code" name="google-activation-code" value="' . '' . '" required>
                                <span class="highlight"></span>
                                <span class="bar"></span>
                                <label>' . __( 'Put google code here', 'gsc' ) . '</label>
                            </div>
                            <input type="hidden" name="action" value="submit-activation-code">
                        </div>';

                $button = '<button class="gsc-settings-button gsc-btn disabled">' . __('Set access to google account', 'gsc') . '</button>';

            }
            else {

                if ( !isset( self::$settings['access_data']['access_token'] ) ) {

                    $authCode = self::$settings['extra']['activation_code'];

                    $accessToken = $client->fetchAccessTokenWithAuthCode( $authCode );

                    if (array_key_exists('error', $accessToken)) {
                        throw new Exception( join(', ', $accessToken) );
                    }

                    self::$settings['access_data'] = $accessToken;
                    update_option('gsc-settings', self::$settings);

                    $out = '<div class="input-box">
                                <input class="gsc-input gsc-ssId" name="ssid" value="' . '' . '">
                                <span class="highlight"></span>
                                <span class="bar"></span>
                                <label>' . __('Put google spreadsheet ID here', 'gsc') . '</label>
                            </div>    
                            <input type="hidden" name="action" value="submit-ssid">';

                    $button = '<button class="gsc-settings-button gsc-btn">' . __('Save', 'gsc') . '</button>';

                }

            }


        }
        else {

            $out =
                '<div class="title-box">' . __( 'All settings are ready, now you can <a href="' . site_url() . '/wp-admin/admin.php?page=gsc-forms.php">tune your forms</a>'  , 'gsc' ) . ' or</div>
                 <div class="gsc-change-settings">
                     <div class="tab-box">
                         <div class="tab-title"><span>' . __( 'Refresh access data', 'gsc' ) . '</span></div>
                         <a class=tab-content gsc-btn" href="' . site_url() . '/wp-admin/admin.php?page=gsc.php&action=refresh_access_data">' . __( 'Refresh', 'gsc' ) . '</a>
                     </div>
                     <div class="tab-box">
                         <div class="tab-title"><span>' . __( 'Chance Google SpreadSheet', 'gsc' ) . '</span></div>
                         <div class="tab-content">
                             <div class="input-box">
                                 <input class="refresh-ssid-input" name="ssid" value="' . self::$settings['extra']['googlesheetID'] . '">
                                 <span class="highlight"></span>
                                 <span class="bar"></span>
                                 <label>' . __('Put google spreadsheet ID here', 'gsc') . '</label>
                             </div>    
                             <button class="refresh-ssid gsc-btn">' . __( 'change', 'gsc' ) . '</button>
                         </div>
                     </div>                        
                 </div>';
            
            $button = '';


        }

        echo
            '<form class="gsc-settings-form" method="POST" action="' . site_url(). '/wp-admin/admin.php?page=gsc.php">
                 <div class="gsc-settings">' .
                     $out .
                     $button .
                  '</div> 
             </form>';

    }


    static function create_form_box( $form )
    {

        $forms_settings = self::$forms_settings;

        if (isset($forms_settings) && (isset($forms_settings[$form->ID]) && is_array($forms_settings[$form->ID]))) {
            $f_data = $forms_settings[$form->ID]['fields'];
            $state = $forms_settings[$form->ID]['state'];
        } else {
            preg_match_all('/\[(text|email|url|tel|textarea|number|range|chexbox|radio|select|date|hidden|file)\*?\s{1,5}([^\s]+)/', $form->post_content, $f_fields);
            if (isset($f_fields[1]) && isset($f_fields[2]) && (count($f_fields[1]) > 0 && count($f_fields[2]) > 0) && (count($f_fields[1]) == count($f_fields[2]))) {
                $f_data = array();
                foreach ($f_fields[1] as $i => $type) {
                    $f_data[$i] = array('position' => $i, 'type' => $type, 'name' => str_replace(array('"', ']', '[', "'", '>'), '', $f_fields[2][$i]), 'state' => 'active');
                }
            }
            $state = 'disabled';
        }
        $active = $hidden = array();
        $j = $k = 0;
        foreach ($f_data as $i => $data) {
            if ($data['state'] == 'active') {
                $active[$j] = '<div class="form-item" data-position="' . $j . '" data-type="' . $data['type'] . '" data-name="' . $data['name'] . '">' . $data['name'] . '</div>';
                $j++;
            } else {
                $hidden[$k] = '<div class="form-item" data-position="' . $k . '" data-type="' . $data['type'] . '" data-name="' . $data['name'] . '">' . $data['name'] . '</div>';
                $k++;
            }
        }

        $action = ( $state == 'enabled' ) ? 'disable' : 'enable';

        $out =
            '<div class="ff-manage">
                 <button class="gsc-btn enable-disable-btn ' . $action . '-btn">' . ucfirst( $action ) . __( ' Google Sheets for this form', 'gsc' ) . '</button>
                 <button class="gsc-btn save-btn">' . __( 'save form settings', 'gsc' ) . '</button>
            </div>      
            <div class="ff-content">
                <div class="form-fields sortable connectedSortable active">' . implode( '', $active ) . '</div>
                <div class="form-fields sortable connectedSortable hidden">' . implode( '', $hidden ) . '</div>
            </div>';

        return array( 'content' => $out, 'state' => $state );

    }


    static function render_gsc_forms_page() {

        $args = array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1); $cf7Forms = get_posts( $args );
        $forms = get_posts( $args );

        $forms_select = $fields = '';
        if ( count ( $forms ) > 0 ) {
            $forms_select = '<select class="gsc-forms">';
            $fields = '<div class=fields-box>';
            foreach ( $forms as $i => $form ) {
                if ( $i == 0 ) { $active = ' active'; $selected = ' selected'; } else { $active = $selected = ''; }
                $forms_select .= '<option value="' . $form->ID . '"' . $selected . '>' . $form->post_title . '</option>';
                $form_content = self::create_form_box( $form );
                $fields .= '<div class="form-fields-box' . $active . ' ' . $form_content['state'] . '" data-form="' . $form->ID . '" data-state="' . $form_content['state'] . '">';
                $fields .= $form_content['content'];
                $fields .= '</div>';
            }
            $fields .= '</div>';
            $forms_select .= '</select>';
        }

        $out =
            '<div class="gsc-forms-box">
                 <div class="select-box">' .
                     $forms_select .
                 '</div>
                 <div class="fields-container">' .
                     $fields .
                 '</div>
             </div>';

        echo $out;

    }


    static function save_form_settings() {

        $settings = self::$forms_settings;
        if ( !$settings ) $settings = array();
        $data = json_decode( str_replace( '\\', '', $_POST['data'] ) , true );
        $settings[$data['form_id']] = array( 'state' => $data['state'], 'fields' => array_merge( $data['fields']['active'], $data['fields']['hidden'] ) );
        update_option( 'gsc-forms', $settings );

        echo json_encode( array( 'result' => 1, 'content' => '', 'value' => $data['state']) );
        wp_die();

    }


    static function create_new_sheet( $cf7 ) {

        $body = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest( array(
            'requests' => array( 'addSheet' => array( 'properties' => array( 'title' => GSC::$sheet ) ) )
        ) );
        $result = self::$service->spreadsheets->batchUpdate( self::$spreadsheetId, $body );
        $id = $result['modelData']['replies'][0]['addSheet']['properties']['sheetId'];

        $fields = self::$forms_settings[$cf7->id()]['fields'];
        $requests = array();

        $titles = array();
        foreach( $fields as $i => $f_data ) {
            if ( $f_data['state'] == 'active' ) {
                $requests[] = new Google_Service_Sheets_Request([
                    'updateDimensionProperties' => [
                        'range' => [
                            'sheetId' => $id,
                            'dimension' => 'COLUMNS',
                            'startIndex' => $i,
                            'endIndex' => ($i + 1)
                        ],
                        'properties' => [
                            'pixelSize' => 250
                        ],
                        'fields' => 'pixelSize'
                    ],
                ]);
                $titles[] = $f_data['name'];
            }
        }

        $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);
        $response = self::$service->spreadsheets->batchUpdate( self::$spreadsheetId, $batchUpdateRequest );

        $range = self::$sheet . '!A1:Z';
        $valueRange = new Google_Service_Sheets_ValueRange();
        $valueRange->setValues( array( $titles ) );
        $conf = [ "valueInputOption" => "USER_ENTERED" ];
        self::$service->spreadsheets_values->update( self::$spreadsheetId, $range, $valueRange, $conf );

    }


    static function write_form_to_sheet( $cf7 ) {

        $fields = self::$forms_settings[$cf7->id()]['fields'];
        $submission = WPCF7_Submission::get_instance();
        $posted_data = $submission->get_posted_data();

        $data = array();
        foreach( $fields as $i => $f_data ) {
            if ( isset( $posted_data[$f_data['name']] ) && $f_data['state'] == 'active' ) {
                $data[] = $posted_data[$f_data['name']];
            }
        }

        $range = "A1:Z";
        $valueRange= new Google_Service_Sheets_ValueRange();
        $valueRange->setValues( ["values" => $data] );
        $conf = [ "valueInputOption" => "USER_ENTERED" ];
        $ins = ["insertDataOption" => "INSERT_ROWS"];
        $response = self::$service->spreadsheets_values->append( self::$spreadsheetId, self::$sheet . '!A1:Z', $valueRange, $conf, $ins );

    }


    static function save_data_to_google_sheet( $cf7 ) {

        if ( isset( self::$forms_settings[$cf7->id()] ) ) {

            if ( !self::$settings || !self::$settings['access_data'] ) return;
            self::$client = self::get_client();
            $service = new Google_Service_Sheets(self::$client);

            if ($service) {
                self::$service = $service;
                self::$sheet = $cf7->title();
                $response = self::$service->spreadsheets->get(self::$spreadsheetId);
            } else {
                return;
            }
            foreach ($response->getSheets() as $s) {
                $sheets[] = $s['properties']['title'];
            }

            if (!in_array(self::$sheet, $sheets)) {
                self::create_new_sheet( $cf7 );
            }

            self::write_form_to_sheet( $cf7 );


        }

    }


    static function save_extra_settings() {

        if ( isset( $_POST['action'] ) ) {

            $settings = get_option( 'gsc-settings' );
            if ( !$settings )
                $settings = array();
            $settings['extra'][ $_POST['name'] ] = $_POST['value'];
            update_option( 'gsc-settings', $settings );

            echo json_encode( array( 'result' => 1, 'content' => __( 'ok', 'gsc' ), 'value' => $_POST['value'] ) );
            wp_die();

        }

    }

}


GSC::init();


?>