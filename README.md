Wordpress Enqueue Class
==============
Quickly and efficiently Enqueue scripts and stylesheets for Wordpress.

**Requires:** 4.2.0 or higher

**Compatible up to:** 4.4.1


Enqueue Class
-------------
Class Name: **SimplyEnqueue**

**Function List**
==============
1. function register($assets)
-------------

**$assets**
Array of scripts or styles to be registered

2. function enqueue($assets)
-------------

**$assets**
Array of scripts or styles to be enqueued

3. function get_enqueue_hook()
-------------
Determines appropriate enqueue hook and returns it


Options
==========

	// Required parameters
	"handle" => "handle",
	"src" => "url/to/file.css", // CSS or JS
	
	// Optional parameters
	"deps" => "handle" || "url/to/file.js" || array("handle", "url/to/file.js"),
	"ver" => "version number", // will default to 0.0.0 if unset
	"usecase" => "frontend" || "backend" || "template.php" || array("home-page.php", "author.php, "backend") || "login" || "all", // will default to frontend if unset
	"js_in_header" => true || false
	// only for JS files
	// will default to false if unset
	// will be overridden if a dependency specifies false for this property
	"media" => "all" || "print" || screen"
	// only for CSS files
	// will default to all if unset
	"enqueue" => true || false || function
	// determines if file should be enqueued
	// function must return true or false
	// will default to false if unset
	);


Usage
==========

Setup
-----

	function enqueue_js() {

		$assets = array (
		
			array(
					'handle' => 'json2',
					'src' => 'path/to/json2.js',
					'deps' => '',
					'ver' => '1.0',
					'js_in_header' => true,
					'enqueue' => true
				),
			array(
					'handle' => 'site-style',
					'src' => 'path/to/style.css',
					'ver' => '1.0',
					'media' => 'all',
					'enqueue' => true
				),
			array(
					'handle' => 'old-ie-style',
					'src' => 'path/to/old-ie-style.css',
					'ver' => '1.0',
					'media' => 'all',
					'conditional' => 'lt IE 9',
					'enqueue' => true
				),

		);
		
		/* Get file that contains Enqueue class */
		require_once ("path/to/enqueue.php");
		
		$enqueue = new SimplyEnqueue;
		$enqueue->register($assets);

	}

Usage
-----

	if (!is_admin()) {

		add_action ('wp_enqueue_scripts', 'enqueue_js');

	} else {

		add_action ('admin_enqueue_scripts', 'enqueue_js');
		
	}
