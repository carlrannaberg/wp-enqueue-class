<?php

/*
ENQUEUE CLASS

class enqueue_handler;

->	function js($scripts) 
	$scripts = array of scripts to be enqueued. 
		foreach ($scripts as $script) 
		$script = array(prefix,name,src,file,deps,version,footerLoad,location) 
		$footerLoad = string[header,footer]
		$location = string[frontend,backend,both]

->	function css($styles): 
	$style = array of styles to be enqueued.
		foreach ($styles as $style)
		$style = array(prefix,name,src,file,deps,version,media,location)
		$location = string[frontend,backend,both]
			
*/

/* Instantiate Wordpress Environment */
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/wp-blog-header.php');

class enqueue_handler {

	function js($scripts) {
	
		$frontendEnqueue = array();
		$backendEnqueue = array();
				
		foreach ($scripts as $script) {
		
		wp_register_script (
			$script['prefix'].'-'.$script['name'],
			$script['src'] . $script['file'],
			$script['deps'],
			$script['version'],
			$script['footerLoad']
		);
		
		switch ($script['location']) {
			
				case 'frontend':
					
					// Add to Frontend Enqueue List
					$frontendEnqueue[] = $script['prefix'].'-'.$script['name'];
															
				break;
				
				case 'backend': 
				
					// Add to Backend Enqueue List
					$backendEnqueue[] = $script['prefix'].'-'.$script['name'];
					
				break;
				
				case 'both':
				default:
				
					// Add to Both Enqueue Lists
					$frontendEnqueue[] = $backendEnqueue[] = $script['prefix'].'-'.$script['name'];
					
				break;
			}
		
		}
		
		switch (is_admin()) {
		
			case true:
			
				if (!empty($backendEnqueue)) {
					
					foreach ($backendEnqueue as $script) {
						
						wp_enqueue_script($script);
						
					}
					
				}
			
			break;
			
			case false:
			
				if (!empty($frontendEnqueue)) {
				
					foreach ($frontendEnqueue as $script) {
					
						wp_enqueue_script($script);
						
					}
				
				}
			
			break;
		}
		
	}
	
	function css($styles) {
	
		$frontendEnqueue = array();
		$backendEnqueue = array();
	
		foreach ($styles as $style) {
			
			wp_register_style( 
				$style['prefix'] . '-' . $style['name'], 
				$style['src'] . $style['file'], 
				$style['deps'], 
				$style['version'], 
				$style['media'] 
			);
			
			switch ($style['location']) {
			
				case 'frontend':
					
					// Add to Frontend Enqueue List
					$frontendEnqueue[] = $style['prefix'].'-'.$style['name'];
															
				break;
				
				case 'backend': 
				
					// Add to Backend Enqueue List
					$backendEnqueue[] = $style['prefix'].'-'.$style['name'];
					
				break;
				
				case 'all':
				default:
				
					// Add to Both Enqueue Lists
					$frontendEnqueue[] = $backendEnqueue[] = $style['prefix'].'-'.$style['name'];
					
				break;
			}
		
		}
		
		switch (is_admin()) {
		
			case true:
			
				if (!empty($backendEnqueue)) {
					
					foreach ($backendEnqueue as $style) {
						
						wp_enqueue_style($style);
						
					}
					
				}
			
			break;
			
			case false:
			
				if (!empty($frontendEnqueue)) {
				
					foreach ($frontendEnqueue as $style) {
					
						wp_enqueue_style($style);
						
					}
				
				}
			
			break;
		
		}
							
	}
	
}



?>