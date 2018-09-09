<?php
/*
Plugin Name: WP True Google Image Optimizer
Author: CA
Version: 1.0.0
*/

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

class GoogleImageOptimizer {

    private $stat;
    private $current_img;
    private $d;

    function __construct() {
		$upload_dir = wp_upload_dir();
		define( 'GIO_UPLOAD_PATH', $upload_dir['path'] );
        define( 'GIO_UPLOAD_BASE', $upload_dir['basedir'] );
        define( 'GIO_PLUGINS_PATH', WP_PLUGIN_DIR );
        define( 'GIO_THEMES_PATH', get_theme_root() );
		define( 'GIO_TMP', $upload_dir['basedir'] . '/gio_tmp' );
		if(strstr($upload_dir['url'],'http'))
		{
			define('GIO_UPLOAD_URL', $upload_dir['url']);
		}
		else
		{
			define('GIO_UPLOAD_URL', get_site_url() . $upload_dir['url']);
		}
		if(strstr($upload_dir['url'],'http'))
		{
			define('GIO_UPLOAD_BASEURL', $upload_dir['baseurl']);
		}
		else
		{
			define('GIO_UPLOAD_BASEURL', get_site_url() . $upload_dir['baseurl']);
		}
		
		if ( !isset( $_POST['dir'] ) ) $this->dir = GIO_UPLOAD_PATH;
		else $this->dir = $_POST['dir'];

		if ( !is_dir( GIO_TMP ) ) {
			mkdir( GIO_TMP );
		}

		$this->zipResource = fopen( GIO_TMP . '/tmpfile.zip', "w" );

		$this->domain = get_option('siteurl');
		$this->domain = parse_url( $this->domain );
		$this->domain = str_replace('www.','', $this->domain['host']);

		$this->current_img = $this->stat = array();
		$this->level = 0;
		$this->d = '';

	}
	
	
	function init() {

	    add_action('admin_enqueue_scripts', array( $this, 'gio_load_scripts' ));
		add_action( 'admin_init', array( $this, 'gio_init' ) );
		add_action( 'admin_menu', array( $this, 'gio_add_menu_item' ) );
		
	}	
	

	function gio_load_scripts( $hook ) {

		if( 'media_page_true-image-optimizer' == $hook ) {

			wp_register_style( 'gio_css', plugins_url('css/gio_css.css', __FILE__) );
			wp_enqueue_style( 'gio_css' );

			wp_register_script( 'gio_js', plugins_url('js/gio_js.js', __FILE__), array( 'jquery' ) );
			wp_enqueue_script( 'gio_js' );

			add_thickbox();
			
			wp_localize_script( 'gio_js', 'gio_data', 
			    array(
				    'google_hard_reject'     => __( 'google api is busy now, try operation later', 'gio' ),
					'google_soft_reject'     => __( 'google api returned busy state, next request will be send in 5 seconds', 'gio' ),
					'server_reject'          => __( 'server does not answer, next request will be send in 5 seconds', 'gio' ),
					'img_has_invalid_format' => __( ' has invalid format'. 'gio' )					
			    )
			);

		}

	}



	function gio_init() {

        add_action( 'wp_ajax_gio_start_optimize', array( $this, 'start_optimize' ) );
		add_action( 'wp_ajax_gio_start_folder_optimize', array( $this, 'start_folder_optimize' ) );
		add_action( 'wp_ajax_gio_optimize_single_img', array( $this, 'optimize_single_img' ) );
		add_action( 'wp_ajax_gio_cancel_optimize', array( $this, 'gio_cancel_optimize' ) );
		add_action( 'wp_ajax_gio_save_settings', array( $this, 'save_settings' ) );		

	}

	

	function gio_add_menu_item() {
		add_submenu_page(
			'upload.php',
			'True Image Optimizer',
			'True Image Optimizer',
			'manage_options',
			'true-image-optimizer',
			array( $this, 'gio_optimizer_admin_page' ) );
	}


    function listFolderFiles($dir, $level = 0, $result = '')
    {
		if ( $result == '' ) $this->d = '';
        $inner = '';
        $level++;
        $isDirEmpty = !(new \FilesystemIterator($dir))->valid();
        if ( !$isDirEmpty ) $has_childs = ' has-childs ';
        else $has_childs = '';
        $this->d .= '<ul class="dir-box level-' . $level . $has_childs . '" data-dir="' . realpath($dir) . '"><li data-value="' . $dir . '" class="dir-link-box"><a class="dir-link" href="' . $dir . '">' . basename( $dir ) . '</a>';
        foreach (new DirectoryIterator($dir) as $fileInfo) {
            if (!$fileInfo->isDot()) {
                if (is_dir($fileInfo->getPathname())) {
                    $this->listFolderFiles( $fileInfo->getPathname(), $level, $this->d );
                }
				else {
					$inner .= '<li class="file-link-box"><a data-value="' . $fileInfo->getPath()  . '/' . $fileInfo->getFilename() . '" class="file-link" href="' . $fileInfo->getPath()  . '/' . $fileInfo->getFilename() . '">' . basename( $fileInfo->getPathname() ) . '</a></li>';
				}
            }
        }
        if ( $inner != '' )
            $this->d .= '<ul class="files-box">' . $inner . '</ul>';
        $this->d .= '</li></ul>';
        $level--;
		return $this->d;
    }

	

	function gio_optimizer_admin_page() {

        $dirs = array( 'upload files directories' => GIO_UPLOAD_BASE, 'themes' => GIO_THEMES_PATH, 'plugins' => GIO_PLUGINS_PATH );
        $dest_dirs = '';
        
        foreach ( $dirs as $dir ) {
            $dest_dirs .= $this->listFolderFiles( $dir );
        }

        $settings = get_option( 'gio_settings' );
		if ( !$settings ) $settings = array( 'key' => '' );
		if ( $settings['key'] != '' ) { 		
		    $prompt_active = ''; 
			$controls_active = 'visible';			
		}
		else { 
		    $prompt_active = 'visible'; 
			$controls_active = '';		
		}
		
		$out =

			'<div class="admin-gio-box">
				 <div class="controls-box">
					 <div class="settings">
						 <div class="setting-block">
							 <div class="title">' . __( 'Google API key', 'gio' ) . '</div>
							 <input name="gio_key" class="setting-input gio_key" value="' . $settings['key'] . '">
						 </div>
						 <div class="settings-prompt prompt ' . $prompt_active . '">' . __( 'You have not Google PSI API key yet? ', 'gio' ) . '<a href="https://developers.google.com/speed/docs/insights/v4/first-app" target="blank">' . __( 'get it', 'gio' ) . '</a></div>
						 <div class="submit-block"><button class="btn settings-submit">' . __( 'save', 'gio' ) . '</button></div>
						 <!--<div class="ws-block"><button class="btn ws-button">ws</button></div>-->
					 </div>
                     <div class="controls-container ' . $controls_active . '">					 
					 <div class="controls">				 
						 <div class="destination">
							 <div class="destination-dirs">' .
								 $dest_dirs .
							 '</div>
							 <div id="gio-dest" class="gio-dest control" value=""></div>
						 </div>				     
						 <div class="info">
							 <div class="left-side">							 
							 
								 <div class="progress-bar-el">
								     <div class="progress-bar-description">' . __( 'current task progress', 'gio' ) . '</div>
									 <div class="progress-bar-box bar-box">
										 <div class="start-value indicator"><span></span></div>
										 <div class="finish-value indicator"><span></span></div>
										 <div class="progress-bar">
											 <div class="current-progress indicator"><span></span></div>
										 </div>
									 </div>
									 <div class="additional-info">
										 <div class="info-input">   
											 <div class="info-item">
												 <div class="description">' . __( 'performed images', 'gio' ) . '</div>
												 <div class="digit success">0</div>
											 </div> 
										 </div>    
										 <div class="info-input">   
											 <div class="info-item">
												 <div class="description">' . __( 'no needed to perform images', 'gio' ) . '</div>
												 <div class="digit no-need">0</div>
											 </div> 
										 </div>     
										 <div class="info-input">  
											 <div class="info-item">
												 <div class="description">' . __( 'rejected by google API', 'gio' ) . '</div>
												 <div class="digit rejected">0</div>
											 </div>                                          
										 </div>
									 </div> 
									 <div class="rejected-imgs-box">
										 <div class="title">' . __( 'rejected images', 'gio' ) . '</div>
										 <div class="rejected-imgs"></div>
									 </div>									 
								 </div> 

								 <div class="progress-bar-el">
								     <div class="progress-bar-description">' . __( 'saved space in images <span class="bold">compressed</span> by google API', 'gio' ) . '</div>
									 <div class="saving-bar-box bar-box">
										 <div class="start-value indicator"><span></span></div>
										 <div class="finish-value indicator"><span></span></div>
										 <div class="progress-bar">
											 <div class="current-progress indicator"><span></span></div>
										 </div>
									 </div>                                 
								 </div> 								 
								 
								 <!--
								 <div class="info-input">
									 <label class="info-label"><span>' . __( 'current file', 'gio' ) . '</span><div class="current-file">0</div></label>
								 </div>
								 <div class="info-input">
									 <label class="info-label"><span>' . __( 'total', 'gio' ) . '</span><div class="digit total">0</div></label>
								 </div>
								 <div class="info-input">
									 <label class="info-label"><span>' . __( 'progress', 'gio' ) . '</span><div class="digit progress">0</div></label>
								 </div> 
								 <div class="info-input">   
									 <label class="info-label"><span>' . __( 'sucessed images', 'gio' ) . '</span><div class="digit success">0</div></label>
								 </div> 
								 <div class="info-input">   
									 <label class="info-label"><span>' . __( 'no needed to perform images', 'gio' ) . '</span><div class="digit no-need">0</div></label>
								 </div>  
								 <div class="info-input">  
									 <label class="info-label"><span>' . __( 'rejected by google API', 'gio' ) . '</span><div class="digit rejected">0</div></label>
								 </div> 
								  -->
							 </div>
							 <!--							 
							 <div class="right-side time">
								 <div class="saving">
									 <div class="title">' . __( 'Saved Kb', 'gio' ) . '</div>
									 <span class="saved-bytes">0</span>/<span class="general-bytes">0</span>
								 </div>
								 <div class="general-percentage-box">
									 <div class="title">' . __( 'Saved Percentage', 'gio' ) . '</div>
									 <div class="general-percentage">0%</div>
								 </div>
								 <div class="rejected-imgs-box">
									 <div class="title">' . __( 'rejected images', 'gio' ) . '</div>
									 <div class="rejected-imgs"></div>
								 </div>
							 </div>
							 -->
							 <div class="log-title">' . __( 'log', 'gio' ) . '</div>
							 <div class="gio-results"></div>
						 </div>						 
					 </div>
					 <div class="button-box">
					     <button class="gio-start-optimize btn">' . __( 'start' , 'gio' ) . '</button>
						 <div class="tool-tip">' . __( 'it is nessesary to select files or directories to perform. Do <b>Ctrl+click</b> on required item', 'gio' ) . '</div>
				 	 </div>	 
					     <button class="gio-cancel-optimize btn">' . __( 'cancel' , 'gio' ) . '</button>					 
					 </div>
				 </div>
			</div>';
				

		echo $out;

	}
	
	function optimize_single_img ( $img = null ) {
		
		$img = $_POST['img'];
		
		$this->gio_optimize_file( $img );
		wp_die();
		
		
	}


	function gio_optimize_file( $file ) {
		
		$settings = get_option( 'gio_settings' );
		$key = $settings['key'];
		
		$size = @getimagesize( $file );
		if ( is_array( $size ) ) {
			$w = (int)$size[0]; $h = (int)$size[1];
			
			if ( filesize( $file ) < 8*1024*1024 ) {

				//$this->resizeImg( $file );
			
				$url = self::abs_path_to_url( $file ); 
				$url = site_url() . '/wp-json/images/get/' . str_ireplace( site_url() . '/wp-content/', '', $url );
				$url = 'https://www.googleapis.com/pagespeedonline/v3beta1/optimizeContents?key=' . $key . '&url=' . $url . '&strategy=desktop';
				//echo $url;
				$result = $this->gio_google_request( $url, $file );
			
			}
			else {
				$content = file_get_contents( GIO_UPLOAD_BASE . '/gio_log.txt' );
				$content .= $file . ' size=' . filesize( $file ) . ', width = ' . $w . ', height=' . $h . PHP_EOL;
				file_put_contents( GIO_UPLOAD_BASE . '/gio_log.txt', $content );
				$this->current_img = array( 'file' => $file, 'start_size' => 0, 'finish_size' => 0, 'result' => false );
				$result = array ( 'result' => 2, 'content' => __( 'file', 'gio' ) . ' ' . $this->get_front_filename( $file ) . __( 'has size ', 'gio' ) . filesize( $file ) . ', and width ' . $w . __( ' it must be under 8Mb size and 1024px width, to check by Google API', 'gio' ), 'current_img' => $this->current_img );
				
			}
		}
		else {
			
			$result = array ( 'result' => 2, 'content' => __( 'file', 'gio' ) . ' ' . $this->get_front_filename( $file ) . __( 'has size ', 'gio' ) . filesize( $file ) . ', and width ' . 0 . __( ' it must be under 8Mb size and 1024px width, to check by Google API', 'gio' ), 'current_img' => $this->current_img ); 
			
		}
		
		echo json_encode( $result );

	}


	function gio_cancel_optimize() {

        $this->recursiveDelete( GIO_TMP );
		wp_die();

	}

	static function abs_path_to_url( $path = '' ) {
		$url = str_replace(
			wp_normalize_path( untrailingslashit( ABSPATH ) ),
			site_url(),
			wp_normalize_path( $path )
		);
		return esc_url_raw( $url );
	}


	function gio_google_request( $url, $file ) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_REFERER, $this->domain);
		curl_setopt($ch, CURLOPT_FILE, $this->zipResource);
		$r = curl_exec($ch);

		if(curl_errno($ch)){
			return array( 'result' => 0, 'content' => __( 'http request error, try later', 'gio' ) );
		}
		//echo $url;
		curl_close($ch);
		$result = $this->unzip_and_get_filename( $file );
		if ( $result ) {
			if ( is_string( $result ) && $result == 'google_rejected_file' ) {
				$content = file_get_contents( GIO_UPLOAD_BASE . '/gio_log.txt' );
				$content .= $file . PHP_EOL;
				file_put_contents( GIO_UPLOAD_BASE . '/gio_log.txt', $content );
				$this->current_img = array( 'file' => $file, 'start_size' => 0, 'finish_size' => 0, 'result' => false );				
				return array( 'result' => 4, 'content' => __( 'file', 'gio' ) . ' ' . $this->get_front_filename( $file ) . ' ' . __( 'needs to be recompressed manually', 'gio' ), 'current_img' => $this->current_img );
			}
			else {
			    return array( 'result' => 1, 'content' => __( 'file', 'gio' ) . ' ' . $this->get_front_filename( $file ) . ' ' . __( 'compressed succefully', 'gio' ) . ' - ' . $this->current_img['save'] . ' ' . __( 'bytes saved', 'gio' ) .  '(' . round ( $this->current_img['save']/$this->current_img['start']*100, 2 ) . '%). ', 'current_img' => $this->current_img );
			}
		}
		else {
			$start_size = 0;
			$this->set_stat( array( 'file' => $file, 'start_size' => $start_size, 'finish_size' => filesize( $file ), 'result' => true ) );
			return array ( 'result' => 3, 'content' => __( 'no need to be compressed', 'gio' ) . ' ' . $this->get_front_filename( $file ), 'current_img' => $this->current_img ) ;
		}

	}


	function unzip_and_get_filename( $file )
	{

	    $start_size = filesize( $file );
		$s_img_size = getimagesize( $file );
		$sw = (int)$s_img_size[0]; $sh = (int)$s_img_size[1];		
		$zip = new ZipArchive;

		$zip->open( GIO_TMP . '/tmpfile.zip' );
		if( $zip->getNameIndex(0) !== 'MANIFEST' )
		{
			$zip->extractTo( GIO_TMP . '/' );
			$tmp_path = $zip->getNameIndex(0);
			$finish_size = filesize( GIO_TMP . '/' . $tmp_path );
			$f_img_size = getimagesize( GIO_TMP . '/' . $tmp_path );
			$fw = (int)$f_img_size[0]; $fh = (int)$f_img_size[1];			
			
			$ratio = $finish_size/$start_size;
			if ( $sw != $fw || $sh != $fh ) {
				$zip->close();
				$this->set_stat( array( 'file' => $file, 'start_size' => $start_size, 'finish_size' => filesize( $file ), 'result' => false ) );
				return 'google_rejected_file';			
			}
			
			$result = rename( GIO_TMP . '/' . $tmp_path, $file ); 
			$zip->close();
			array_map('unlink', glob( GIO_TMP . '/*.*' ));

			$this->set_stat( array( 'file' => $file, 'start_size' => $start_size, 'finish_size' => $finish_size, 'result' => true ) );

			return $result;
		}
		else
		{
			$zip->close();
            $this->set_stat( array( 'file' => $file, 'start_size' => $start_size, 'finish_size' => filesize( $file ), 'result' => false ) );
			return false;
		}
	}

	function set_stat ( $args = array( 'file' => null, 'start_size' => 0, 'finish_size' => 0, 'result' => false ) ) {

	    $this->current_img = array();
	
        if ( $args['result'] ) {
            $this->current_img['result'] = 1;
			$this->current_img['start'] = $args['start_size'];
			$this->current_img['finish'] = $args['finish_size'];
			$this->current_img['save'] = $args['start_size'] - $args['finish_size'];
			$this->current_img['type'] = 'success';
        }
        else {
            $this->current_img['result'] = 0;
			$this->current_img['start'] = $args['start_size'];
			$this->current_img['finish'] = $args['finish_size'];
			$this->current_img['save'] = 0; 
            $this->current_img['type'] = 'passed';			 
        }

    }

    function recursiveDelete($path, $deleteParent = true){

        if(!empty($path) && is_dir($path) ){
            $dir  = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS); 
            $files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $f) {
                if ( is_file($f->getPathName())) {
                    unlink($f->getPathName());
                }
                else {
                    $empty_dirs[] = $f;
                }
            }
            if (!empty($empty_dirs)) {
                foreach ($empty_dirs as $eachDir) {
                    rmdir($eachDir);
                }
            }
            rmdir($path);
        }

    }
	
	function get_front_filename( $file ) {
		$r = preg_match( '/\/wp-content.+/', $file, $name );
		if ( isset( $name[0] ) ) return $name[0];
		else return '';
	}
	
	
	function save_settings() {
		if ( isset( $_POST['gio_key'] ) && strlen( $_POST['gio_key'] ) > 8 && strlen( $_POST['gio_key'] ) < 50 ) {
			$key = trim( $_POST['gio_key'] );
			$settings = array( 'key' => $key );
		    update_option( 'gio_settings', $settings );
		}
		wp_die();
	}

	
	function resizeImg( $img ) {
	    $image = new SimpleImage( );
		$max_width = 2920; $max_height = 1080;
	    $resize = $image->load( $img, $max_width, $max_height ); 
		if ( is_array( $resize ) ) {
			if ( isset( $resize['width'] ) )
	            $image->resizeToWidth( $max_width );
			if ( isset( $resize['height'] ) )
				$image->resizeToHeight( $max_height );
	        $image->save( $img );		
		}
	}

}


$inst = new GoogleImageOptimizer();
$inst->init();


class SimpleImage {

   var $image;
   var $image_type;

    function load( $filename, $max_width, $max_height ) {
	   
	    $image_info = getimagesize( $filename );
	    if ( $image_info[0] > $max_width ) $params = array( 'width' => 1920 );
	    if ( $image_info[1] > $max_height ) $params = array( 'height' => 1080 );
	    $this->image_type = $image_info[2];
	    if( $this->image_type == IMAGETYPE_JPEG ) {
		    $this->image = imagecreatefromjpeg($filename);
	    } elseif( $this->image_type == IMAGETYPE_GIF ) {
		    $this->image = imagecreatefromgif($filename);
	    } elseif( $this->image_type == IMAGETYPE_PNG ) {
		    $this->image = imagecreatefrompng($filename);
		    imageAlphaBlending($this->image, true);
		    imageSaveAlpha($this->image, true);
	    }
	    return $params;
		
    }
   
    function save( $filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null ) {
	   
        if( $image_type == IMAGETYPE_JPEG ) {
            imagejpeg($this->image,$filename,$compression);
        } elseif( $image_type == IMAGETYPE_GIF ) {
            imagegif($this->image,$filename);
        } elseif( $image_type == IMAGETYPE_PNG ) {
            imagepng($this->image, $filename, 7 );
		    imagedestroy($this->image);
        }
        if( $permissions != null ) {
            chmod($filename,$permissions);
        }
		
    }
   
    function output($image_type=IMAGETYPE_JPEG) {
	   
        if( $image_type == IMAGETYPE_JPEG ) {
            imagejpeg($this->image);
        } elseif( $image_type == IMAGETYPE_GIF ) {
            imagegif($this->image);
        } elseif( $image_type == IMAGETYPE_PNG ) {
            imagepng($this->image);
        }
		
    }
   
    function getWidth() {
	   
      return imagesx($this->image);
	  
    }
   
    function getHeight() {
	   
      return imagesy($this->image);
	  
    }
	
    function resizeToHeight($height) {
		
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width,$height);
		
    }
    function resizeToWidth($width) {
		
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width,$height);
		
    }
    function scale($scale) {
		
        $width = $this->getWidth() * $scale/100;
        $height = $this->getheight() * $scale/100;
        $this->resize($width,$height);
		
    }
	
    function resize($width,$height) {
		
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
		
    }	
   
}

add_action( 'rest_api_init', 'register_api_routes' );

function register_api_routes() {
	
	register_rest_route(
		'images',
		'/get/(?P<img>.+)',
		array(
			'methods' => 'POST, GET',
			'callback' => 'get_img_page'
		)
	);		

} 

function get_img_page( $data ) {

	$file = ABSPATH . 'wp-content/' . $data['img']; 
	$size = getimagesize( $file );
	
	$out =
	'<!DOCTYPE html>
		 <html>
			<head>
				<title>single image ' . $data['img'] . '</title>
				<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
			</head>

		 <body>' . 
			 '<div class="content"><img width="' . $size[0] . '" height="' . $size[1] . '" src="' . GoogleImageOptimizer::abs_path_to_url( $file ) . '"></div>' .
		 '</body>

		 </html>';
	header('Content-type:text/html;charset=utf-8');	 
	echo $out;	
	
}

	