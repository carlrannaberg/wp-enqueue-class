(dxm) Wordpress Enqueue Class
==============
Quickly and efficiently Enqueue scripts and stylesheets for Wordpress.



ENQUEUE CLASS
-------------

Class Name: enqueue_handler

Functions:

i. function js($scripts)
	$scripts = array of scripts to be enqueued. 
		foreach ($scripts as $script) 
		$script = array(prefix,name,src,file,deps,version,footerLoad,location) 
		$footerLoad = string[header,footer]
		$location = string[frontend,backend,both]

ii.	function css($styles): 
	$style = array of styles to be enqueued.
		foreach ($styles as $style)
		$style = array(prefix,name,src,file,deps,version,media,location)
		$location = string[frontend,backend,both]
