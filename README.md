(dxm) Wordpress Enqueue Class
==============
Quickly and efficiently Enqueue scripts and stylesheets for Wordpress.



ENQUEUE CLASS
-------------
Class Name: **enqueue_handler**



**Function List**

i. function js($scripts)
========================

**$scripts**
Array of scripts to be enqueued



**foreach ($scripts as $script)**

$script 
-------
**$script[params]** = array( *prefix, name, src, file, deps, version, footerLoad, location* )


$script[params]
--------------
**$prefix** = string [*used to prevent clash between other Wordpress / 3rd Party script enqueues*]
**$name** = string [*enqueue pseudoname in Wordpress*]
**$src** = string [*url/path/to/script/folder*]
**$file** = string[*filename.ext*]
**$deps** = string / array [*contains list of script pseudonames required for file load*]
**$version** = int / string
**$footerLoad** = string [*header, footer*]
**$location** = string [*frontend, backend, both*]



ii.	function css($styles)
=========================

**$styles** 
Array of styles to be enqueued



**foreach ($styles as $style)**


$style 
------
**$style[params]** = array( *prefix, name, src, file, deps, version, media, location* )


$style[param]
-------------
**$prefix** = string [*used to prevent clash between other Wordpress / 3rd Party script enqueues*]
**$name** = string [*enqueue pseudoname in Wordpress*]
**$src** = string [*url/path/to/script/folder*]
**$file** = string[*filename.ext*]
**$deps** = string / array [*contains list of script pseudonames required for file load*]
**$version** = int / string
**$media** = string [*all, aural, braille, embossed, handheld, print, projection, screen, tty, tv*]
**$location** = string[*frontend, backend, both*]


iii. Usage
==========

Setup
-----

	function enqueue_js() {

		$scripts = array (
		
			array(
					'prefix' => 'crockford',
					'name' => 'json2',
					'src' => $routing['url']['js'],
					'file' => 'json2.js',
					'deps' => '',
					'version' => '1.0',
					'footerLoad' => true,
					'location' => 'frontend'
				),
		
		);
		
		/* Get file that contains Enqueue class */
		require_once ("path/to/enqueue.php");
		
		$enqueue = new enqueue_handler;
		$enqueue->js($scripts);

	}

Usage
-----

	if (!is_admin()) {

		add_action ('wp_enqueue_scripts', 'enqueue_js');

	} else {

		add_action ('admin_enqueue_scripts', 'enqueue_js');
		
	}