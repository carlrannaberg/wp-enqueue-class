<?php

/*

SIMPLY ENQUEUE
It shouldn't be complicated to add files in wordpress in a neat fashion.
Team Automattic built a nice and neat way to do it (via enqueues).
This is meant to be a wrapper that simplifies enqueues.
All you need to do is $implyEnqueue->this
Enjoy! 
o/


class SimplyEnqueue;

// ADD A SINGLE FILE
|- Stylesheet or Javascript

->	function this($file)
		$file = array(

			// REQUIRED PARAMS
			"handle" => "handle",
			"url" => "url/to/file.css",
				|- CSS or JS
			
			// OPTIONAL PARAMS
			"deps" => "handle" || "url/to/file.js" || array("handle", "url/to/file.js"),
			
			"ver" => "version number", 
				|- will default to 0.0.1 if unset
			
			"usecase" => "frontend" || "backend" || "template.php" || array("home-page.php", "author.php, "backend") || "all", 
				|- will default to all if unset
			
			"js_in_header" => true || false
				|- only for JS files
				|- will default to false if unset
				|- will be overridden if a dependency specifies false for this property
			
			"media" => "all" || "print" || screen"
				|- only for CSS files
				|- will default to all if unset
			);

----- or -----

// ADD MULTIPLE FILES

->	function these($files) 
		$scripts = array (
			
			$file,
			$another_file,
			$one_more_file
		);
			
*/

	class SimplyEnqueue {

		var $enqueue_hook,
			$unvetted,
			$unregistered,
			$unenqueued,
			$wpenqueues;

		// DETERMINE APPROPRIATE ENQUEUE HOOK AND RETURN IT		
		private function get_enqueue_hook() {

			// check if login / register page
			if ( in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {

				$correct_hook = 'login_enqueue_scripts';
			
			} else {

				// check if backend page
				// if not, assume frontend
				$correct_hook = ( true == is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts' );
			} 

			return $correct_hook;
		}


		// CHECK IF URL IS STYLESHEET OR JAVASCRIPT
		// returns css || js || unknown
		private function get_filetype($url){

			// set default filetype
			$filetype = 'unknown';

			// if url var is the right datatype, try to get filetype
			if ( is_string($url) ) {
				
				$fractalUrl = explode('/', $url);
				
				$filename = $fractalUrl[count($fractalUrl) - 1];
				$fractalFilename = explode('.', $filename);

				$fileExtension = $fractalFilename[1];
				$fileExtension = strtolower($fileExtension);

				if (strpos($fileExtension, "js") !== false) {

					$filetype = "js";
				}
				
				if (strpos($fileExtension, "css") !== false) {

					$filetype = "css";
				} 
			}

			return $filetype;
		}

		private function is_usecase($usecase){

			switch ($usecase) {

				case 'frontend': 

					if ( !is_admin() && !in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' )) ){

						return true;
					} 

					else {

						return false;
					}

				break;

				case 'backend':

					if ( is_admin() && !in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' )) ){

						return true;
					}

					else {

						return false;
					}

				break;

				case 'all':

					return true;
				break;


			}
		}

		// SKIP BROKEN FILES
		// set defaults for missing variables
		private function vet($file) {

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

			$filetype = $this->get_filetype($file['url']);

			if ( $filetype == 'unknown') {

				echo "'" . $file['handle'] . "' doesn't seem to be a stylesheet or javascript. Doublecheck the url. <br />";
				return;
			}

			// normalize and move it to the next step
			
			// set version number
			$file['ver'] = ($file['ver'] ? $file['ver'] : "0.0.1");
			
			// set usecase
			$file['usecase'] = ($file['usecase'] ? $file['usecase'] : "all");

			// javascript specific vetting
			if ($filetype == "js") {

				$file['js_in_header'] = ($file['js_in_header'] ? $file['js_in_header'] : false);
			}

			// CSS specific vetting
			if ($filetype = "css") {

				$file['media'] = ($file['media'] ? $file['media'] : "all");
			} 

			$this->unregistered[] = $file;
		}

		// Start vetting process
		private function processUnvetted() {

			foreach ($this->unvetted as $file) {

				$this->vet($file);
			}
		}

		private function processUnregistered() {

			foreach ($this->unregistered as $file) {

				$filetype = $this->get_filetype($file['url']);

				if ( $filetype == "js") {
				
					wp_register_script(	$file['handle'], $file['url'], $file['deps'], $file['ver'], !$file['js_in_header'] );
				}

				if ( $filetype == "css") {

					wp_register_style( $file['handle'], $file['url'], $file['deps'], $file['ver'], $file['media'] );
				}

				$this->unenqueued[] = array(
					"type" => $filetype,
					"handle" => $file['handle'],
					"usecase" => $file['usecase']
				);
			}
		}

		private function processUnenqueued() {

			foreach ($this->unenqueued as $file) {

				/*
				if ($file['usecase']) {

					if ( !is_array($file['usecase']) ){

						if ( $this->is_usecase($file['usecase']) == false ) {

							continue;
						}
					}

					if ( is_array($file['usecase']) ){

						foreach ( $file['usecase'] as $usecase ) {

							if ( $this->is_usecase($usecase) == false ) {

								continue;
							}
						}
					}
				}

				*/

				if ($file['type'] == 'css') { 

					wp_enqueue_style($file['handle']); 
				} else 

				if ($file['type'] == 'js') { 

					wp_enqueue_script($file['handle']); 
				}
			}
		}

		public function startEnqueue(){

			$this->processUnenqueued();
		}

		// ENQUEUE A SINGLE FILE
		public function this($file){

			$this->unvetted[] = $file;

			// if start-up trigger isn't in place, put it there
			if ( !has_action('simplyEnqueue', array($this, 'startEnqueue')) ) {

				add_action('simplyEnqueue', array($this, 'startEnqueue'));
			}

			$this->processUnvetted();
			$this->processUnregistered();
		}

		// ENQUEUE MULTIPLE FILES
		public function these($files){

			foreach ($files as $file) {

				$this->this($file);
			}

			$this->processUnvetted();
			$this->processUnregistered();
		}

		// ENQUEUE PROCESS ACTIVATION 
		public function simplyEnqueue() {

			do_action('simplyEnqueue');
		}

		// CONSTRUCTOR
		function __construct() {

			// determine hook and set to variable
			$this->enqueue_hook = $this->get_enqueue_hook();

			// if action hook hasn't been declared, do it
			if ( !has_action($this->enqueue_hook, 'simplyEnqueue') ) {

				add_action($this->enqueue_hook, array($this, 'simplyEnqueue'));
			}
		}

	}

?>