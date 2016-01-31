Wordpress Enqueue Class
==============
Quickly and efficiently Enqueue scripts and stylesheets for Wordpress.



Enqueue Class
-------------
Class Name: **SimplyEnqueue**

**Function List**
==============
1. function register($scripts)
-------------

**$scripts**
Array of scripts or styles to be registered

2. function enqueue($scripts)
-------------

**$scripts**
Array of scripts or styles to be enqueued

3. function get_enqueue_hook()
Determines appropriate enqueue hook and returns it

Usage
==========

Setup
-----

	function enqueue_js() {

		$scripts = array (
		
			array(
					'handle' => 'json2',
					'url' => 'path/to/json2.js',
					'deps' => '',
					'ver' => '1.0',
					'js_in_header' => true,
					'enqueue' => true
				),

		);
		
		/* Get file that contains Enqueue class */
		require_once ("path/to/enqueue.php");
		
		$enqueue = new SimplyEnqueue;
		$enqueue->register($scripts);

	}

Usage
-----

	if (!is_admin()) {

		add_action ('wp_enqueue_scripts', 'enqueue_js');

	} else {

		add_action ('admin_enqueue_scripts', 'enqueue_js');
		
	}
