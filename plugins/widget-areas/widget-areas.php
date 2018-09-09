<?php
/*
Plugin Name: CA Sidebars
Description: The plugin manages Sidebars in WordPress admin panel
Author: SecretLab
Version: 1.0.1
Author URI: http://secretlab.pw/
*/

if( !class_exists( 'SL_Widget_Areas' ) ) {

    class SL_widget_areas {
	
		public $redux;
        public $widget_areas = array();

        public function __construct( $widget_areas = array() ) {
			
            add_action( 'load-widgets.php', array(&$this, 'register_custom_widget_areas') );
			add_action( 'widgets_init' , array(&$this, 'register_custom_widget_areas') );
			add_action( 'admin_footer', array($this, 'new_widget_area_box') );
            add_action( 'wp_ajax_sl_delete_widget_area', array( $this, 'sl_delete_custom_widget_area') );  			
			add_action( 'wp_ajax_sl_add_widget_area', array( $this, 'sl_add_custom_widget_area') ); 

        }
		
		/* Check of the Name */
		
		
		public function sl_add_custom_widget_area() {
            if (!empty($_REQUEST['name']) ) {
			    $response = array();
			    $name = strip_tags( ( stripslashes( $_REQUEST['name'] ) ) );
				$this->get_widget_areas();
		        $key = array_search($name, $this->widget_areas ); 
		        if( is_numeric($key) ) {
				    $response['code'] = 0;
					$response['text'] = 'Already exists widget with such name - <b>'.$name.'</b>, set another one';
				}
				else { 
				    array_push($this->widget_areas, $name);
					set_theme_mod( 'sl-widget-areas', array_unique( $this->widget_areas ));
					$response['code'] = 1;
				}
            }			
			else {
			    $response['code'] = 0;
				$response['text'] = 'widget name must be at least 1 symbol length';			    
			}
			echo json_encode($response);
			die();
		}
		
		
        /* Display a form */
          
        public function new_widget_area_box() {
		
		    $screen = get_current_screen();
			
			if ($screen->id == 'widgets') {
			    require_once(plugin_dir_path( __FILE__ ).'/new_widget_form.php');
				echo $content;
			}

        }               

        
        /* Register Sidebar */
        
        function register_custom_widget_areas() {
		global $wp_registered_sidebars;

            if ( empty($this->widget_areas) ) {
				$this->get_widget_areas();
            }
			
            $options = array(
              'before_title'  => '<h3 class="widgettitle">', 
              'after_title'   => '</h3>',
              'before_widget' => '<div id="%1$s" class="widget %2$s">',
              'after_widget'  => '</div>'
            );			
            if(is_array($this->widget_areas)) {
                foreach (array_unique($this->widget_areas) as $widget_area) { 
                    $options['id']      = sanitize_key( $widget_area );
                    $options['name']    = $widget_area;
					$options['class']   = 'sl-custom';                 
                    register_sidebar($options);
                }
            }
        	wp_enqueue_style( 'dashicons' );

            wp_enqueue_script('sl-widget_areas-js', plugins_url(false, __FILE__ ).'/js/widget_areas.js', array('jquery'), time(), true);      

            wp_enqueue_style('sl-widget_areas-css', plugins_url(false, __FILE__ ).'/css/widget_areas.css', time(), true);
			
        }

        /* Sidebars array */

		
        public function get_widget_areas($return = false) {

            $saved_areas = get_theme_mod('sl-widget-areas');

            if (!empty($saved_areas)) {
                $this->widget_areas = array_unique(array_merge($this->widget_areas, $saved_areas));
            }

            if ($return) {
			    return $this->widget_areas;
			}

        }
			

        /* Delete Sidebar */
		
		function sl_delete_custom_widget_area() {

		    if(!empty($_REQUEST['name'])) {
		        $name = strip_tags( ( stripslashes( $_REQUEST['name'] ) ) );
				$this->get_widget_areas();
		        $key = array_search($name, $this->widget_areas );
		        if( $key >= 0 ) {
		            unset($this->widget_areas[$key]);
		            set_theme_mod( 'sl-widget-areas', array_unique( $this->widget_areas ));
					echo "widget_area-deleted";
		        }
		    }

		    die();
		}
		
	}
	
	new SL_widget_areas();

}

?>