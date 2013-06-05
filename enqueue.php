<?php 
	
	/*

	SIMPLY ENQUEUE
	It shouldn't be complicated to add files in wordpress in a neat fashion.
	Team Automattic built a nice and neat way to do it (via enqueues).
	This is only a wrapper that simplifies their work.

	Enjoy! 
	o/
	

	$about_this_class = array(	
		"name" => "(dxm) Simply Enqueue",
		"class" => "SimplyEnqueue",
		"version" => "0.0.1",
		"github" => "https://github.com/Akamaozu/dxm-wp-enqueue"
	)

	// REQUIRED PARAMS
	"handle" => "handle",
	"url" => "url/to/file.css",
		|- CSS or JS

	// OPTIONAL PARAMS
	"deps" => "handle" || "url/to/file.js" || array("handle", "url/to/file.js"),

	"ver" => "version number", 
		|- will default to 0.0.0 if unset

	"usecase" => "frontend" || "backend" || "template.php" || array("home-page.php", "author.php, "backend") || "login" || "all", 
		|- will default to frontend if unset

	"js_in_header" => true || false
		|- only for JS files
		|- will default to false if unset
		|- will be overridden if a dependency specifies false for this property

	"media" => "all" || "print" || screen"
		|- only for CSS files
		|- will default to all if unset

	"enqueue" => true || false || function
		|- determines if file should be enqueued
		|- function must return true or false
		|- will default to false if unset
	);
				
	*/


	class SimplyEnqueue {

		private $items_to_register = array(),
				$items_to_enqueue = array(),
				$instance_params = array(),
				$process = array(),
				$enqueue_hook;

		private function _is_register_data($data){

			if ( isset($data) && isset($data["handle"]) && isset($data["url"]) ) {

				return true;
			}

			return false;
		}

		private function _contains_register_data($wrapper) {

			if ( !is_array($wrapper) && !is_object($wrapper) ){

				return false;
			}

			foreach ($wrapper as $data){

				if ( $this->_is_register_data($data) ){

					return true;
				}
			}

			return false;
		}

		private function _is_enqueue_data($data){

			if ( isset($data) && ( is_array($data) || is_object($data) ) && isset($data["handle"]) ){

				return true;
			}

			return false;
		}

		private function _contains_enqueue_data($wrapper){

			if ( !is_array($wrapper) && !is_object($wrapper) ){

				return false;
			}

			foreach ($wrapper as $data){

				if ( $this->_is_enqueue_data($data) ){

					return true;
				}
			}

			return false;
		}

		// CHECK IF URL IS STYLESHEET OR JAVASCRIPT
		// @return string - "css" || "js" || "unknown"
		private function _get_filetype($url){

			// set default filetype
			$filetype = 'unknown';

			// if url var is the right datatype, try to get filetype
			if ( is_string($url) ) {
				
				$fractalUrl = explode('/', $url);
				
				$filename = $fractalUrl[count($fractalUrl) - 1];
				$fractalFilename = explode('.', $filename);

				$fileExtension = strtolower($fractalFilename[1]);

				if (strpos($fileExtension, "js") !== false) {

					$filetype = "js";
				}
				
				if (strpos($fileExtension, "css") !== false) {

					$filetype = "css";
				} 
			}

			return $filetype;
		}

		private function _normalize_for_registration($file){

			// error handling
			if ( !$file['handle'] || !$file['url'] ) {

				if (!$file['handle'] && !$file['url']) {

					echo "Enqueuing a file without a handle AND url? GO HOME, DEVELOPER. YOU'RE DRUNK. <br />";
					return;
				}

				if (!$file['handle'] && $file['url']) {

					echo "No handle set for '" . $file['url'] . "' <br />";
					return;
				}

				if (!$file['url'] && $file['handle']) {

					echo "No url given for '" . $file['handle'] . "' <br />";
					return;
				}
			}

			$filetype = $this->_get_filetype($file['url']);

			if ( $filetype == 'unknown') {

				echo "'" . $file['handle'] . "' doesn't seem to be a stylesheet or javascript. Doublecheck the url. <br />";
				return;
			}

			$file["filetype"] = $filetype;
			
			// set version number
			$file['ver'] = ($file['ver'] ? $file['ver'] : "0.0.0");
			
			// set usecase
			$file['usecase'] = ($file['usecase'] ? $file['usecase'] : "all");

			$file['enqueue'] = ($file['enqueue'] ? $file['enqueue'] : false);

			// javascript specific vetting
			if ($filetype == "js") {

				$file['js_in_header'] = ($file['js_in_header'] ? $file['js_in_header'] : false);
			}

			// CSS specific vetting
			if ($filetype = "css") {

				$file['media'] = ($file['media'] ? $file['media'] : "all");
			} 

			return $file;
		}

		private function _normalize_for_enqueue($data){

			$item_to_enqueue = array();

			$item_to_enqueue['handle'] = $data["handle"];
			$item_to_enqueue['usecase'] = ($data["usecase"] ? $data['usecase'] : 'frontend' );
			$item_to_enqueue['filetype'] = ($data['filetype'] ? $data['filetype'] : ($data['url'] ? $this->_get_filetype($data['url']) : "unknown") );

			return $item_to_enqueue;
		}

		private function _add_to_register_list($data){

			$this->items_to_register[] = $this->_normalize_for_registration($data);

			if ($this->process["register"] != true){

				$this->process["register"] = true;
			}
		}

		private function _add_to_enqueue_list($data){

			$this->items_to_enqueue[] = $this->_normalize_for_enqueue($data);

			if ($this->process["enqueue"] != true){

				$this->process["enqueue"] = true;
			}
		}

		private function _register_items(){

			foreach ($this->items_to_register as $file){

				if ( $file["filetype"] == "js") {
				
					wp_register_script(	$file['handle'], $file['url'], $file['deps'], $file['ver'], !$file['js_in_header'] );
				}

				if ( $file["filetype"] == "css") {

					wp_register_style( $file['handle'], $file['url'], $file['deps'], $file['ver'], $file['media'] );
				}

				if ( (isset($file['enqueue'])) && (($file['enqueue'] === true) || (is_callable($file['enqueue']) && $file['enqueue']() === true)) ){

					$this->_add_to_enqueue_list($file);
				}
			} 
		}

		private function _enqueue_items(){

			foreach ($this->items_to_enqueue as $file){

				$filetype = $file["filetype"] || ( $file['url'] ? $this->_get_filetype($file['url']) : 'unknown' );

				if ( $filetype == "js") {
				
					wp_enqueue_script(	$file['handle'] );
				}

				if ( $filetype == "css") {

					wp_enqueue_style( $file['handle'] );
				}

				if ( $filetype == "unknown") {

				 	// check if handle is a style or script
					// enqueue accordingly
					
					// for now, fire blindly
					wp_enqueue_style( $file['handle'] );
					wp_enqueue_script(	$file['handle'] );
				}
			} 
		}

		/* 
			PUBLIC FUNCTIONS 
			* Accessible from outside the Class

			|- Class Instance
				|-> get_enqueue_hook
				|-> register
				|-> init_instance
		*/


		// DETERMINE APPROPRIATE ENQUEUE HOOK AND RETURN IT
		// @returns: string	
		public function get_enqueue_hook() {

			if ( isset($this->enqueue_hook) && in_array(strtolower($this->enqueue_hook), array( "wp_enqueue_scripts", "admin_enqueue_scripts", "login_enqueue_scripts") )  ){

				return $this->enqueue_hook;
			}

			else {

				// check if login / register page
				if ( in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {

					$this->enqueue_hook = 'login_enqueue_scripts';
				
				} else {

					// check if backend page
					// if not, assume frontend
					$this->enqueue_hook = ( true == is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts' );
				} 

				return $this->enqueue_hook;
			}
		}

		// SET FILE(S) FOR REGISTRATION
		public function register( $params = array() ){

			// improper $params
			if ( !$this->_is_register_data($params) && !$this->_contains_register_data($params) ){

				return $this;
			}

			// $params = single enqueue file
			if ( $this->_is_register_data($params) ){

				$this->_add_to_register_list($params);

				echo $this->_is_register_data($params);
			}

			// $params = multiple enqueue files
			if( $this->_contains_register_data($params) ){

				foreach ($params as $data){

					if ( $this->_is_register_data($data) ){

						$this->_add_to_register_list($data);
					}
				}
			}

			return $this;
		}

		// ENQUEUE FILE(S)
		public function enqueue( $params = array() ){

			// improper $params
			if ( !$this->_is_enqueue_data($params) && !$this->_contains_enqueue_data($params) ){

				return $this;
			}

			// $params = single enqueue file
			if ( $this->_is_enqueue_data($params) ){

				$this->_add_to_enqueue_list($params);
			}

			// $params = multiple enqueue files
			if( $this->_contains_enqueue_data($params) ){

				foreach ($params as $data){

					if ( $this->_is_enqueue_data($data) ){

						$this->_add_to_enqueue_list($data);
					}
				}
			}

			return $this;
		}

		// PROCESS INSTANCE
		public function init_instance(){

			if ($this->process["register"] == true) {

				$this->_register_items();
			}

			if ($this->process["enqueue"] == true) {

				$this->_enqueue_items();
			}
		}		

		function __construct( $params = NULL ){

			$this->instance_params = $params;


			if ( !has_action($this->get_enqueue_hook(), array($this, 'init_instance')) ){

				add_action( $this->get_enqueue_hook(), array($this, 'init_instance') );
			}
		}
	}
?>