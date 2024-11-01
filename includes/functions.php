<?php
function script_already_loaded( $script_name ){
    global $wp_scripts;
    $queue = $wp_scripts->queue;
    $exists = false;
    
    foreach ($queue as $script ){
       if( preg_match( "/$script_name/i", $script ) ){
            $exists = true;
       }
    }
    return $exists;
}

function wplb_initialize_scripts(){
    wp_register_script( 'wplb-modernizr', plugins_url('wp-lightpics/js/modernizr.custom.js'), array('jquery'), '1.0', true );
    wp_register_script( 'wplb-lightbox',plugins_url('wp-lightpics/js/lightbox.min.js'), array('jquery', 'wplb-modernizr'), '2.6', true );
    
    //enqueue
    wp_enqueue_script('jquery');
    
    if( !script_already_loaded("lightbox") )
        wp_enqueue_script('wplb-lightbox');
    
    if( !script_already_loaded("modernizr") )
        wp_enqueue_script('wplb-modernizr');
}

function wplb_initialize_styles(){
    wp_register_style('wplb-lightbox-css', plugins_url('wp-lightpics/css/lightbox.css'), array(), false, 'screen' );
    wp_register_style('wplb-screen-css', plugins_url('wp-lightpics/css/screen.css'), array(), false, 'screen' );
    
    //enqueue
    wp_enqueue_style( 'wplb-lightbox-css' );
    //wp_enqueue_style( 'wplb-screen-css' );
}

function wplb_get( $text, $pattern ){
    $matches = array();
    preg_match_all($pattern, $text, $matches );	
    return $matches[0];
}

function wplb_get_images( $text ){  
    return wplb_get( $text, '/\<img[a-zA-Z0-9\"\- =:\/\.]+\>/' );    
}

function wplb_get_links( $text ){
    //$link_image_pattern = '/\<a\shref="([^"]*)\.(png|jpg|jpeg|gif)"\>\<img\s.+\><\/a\>/i';
	$link_image_pattern = '/\<a\shref=(^\'|\"[a-zA-Z0-9\/\/\\-_\!\@\#\$\%\^\&\(\)\:\.]+\"|\')><img[a-zA-Z0-9\/\/\\-_\!\@\#\$\%\^\&\(\)\:\. \"=-]+\>\<\/a\>/i';
    //return wplb_get( $text, "/\<a.+\<\/a\>/i" ); 
    return wplb_get( $text, $link_image_pattern ); 
}

function wplb_prepare_html( $html ){
    $htmldoc = new DOMDocument();
    $htmldoc->loadHTML( $html );
    
    //Remove html wrapper
    $htmldoc->removeChild( $htmldoc->firstChild);
    $htmldoc->replaceChild( $htmldoc->firstChild->firstChild->firstChild,  $htmldoc->firstChild);
    
    return $htmldoc;
}

function wplb_get_image_attributes( $image_dom_object, array $attributes = array( 'height', 'width' ) ){
    $_attributes = array();
    foreach( $attributes as $value ){
        if( $image_dom_object->getAttribute($value) ){
            $_attributes[$value] = $image_dom_object->getAttribute($value);
        }
    }
    return $_attributes;
}

function wplb_prepare_link( $html, $imageGroup, array $forbitten_classes = array() ){
    
    $htmldoc = wplb_prepare_html( $html );
    $links = $htmldoc->getElementsByTagName('a');
    $lightboxAttr = $htmldoc->createAttribute('data-lightbox');
    $lightboxAttr->value = $imageGroup;
    
    foreach ($links as $lnk ){
        $hasForbittenClass = $lnk->getAttribute('class') && in_array( $lnk->getAttribute('class'), $forbitten_classes);
        
        if( !$hasForbittenClass ){
            $lnk->appendChild( $lightboxAttr );
        }
    }
    return $htmldoc->saveHTML();
}

function wplb_prepare_image( $html, $imageGroup, array $forbitten_classes = array() ){
     $htmldoc = wplb_prepare_html( $html );
     $images = $htmldoc->getElementsByTagName('img');    
     
     $lightboxAttr = $htmldoc->createAttribute('data-lightbox');
     $lightboxAttr->value = $imageGroup;
     
    
     foreach ( $images as $image ){         
         
         if( $image->getAttribute('class') && !in_array( $image->getAttribute('class'), $forbitten_classes) ){
            $attrs = wplb_get_image_attributes( $image, array('src') );
            $lnk = $htmldoc->createElement('a');
            $lnk->appendChild( $lightboxAttr );
            $lnk->setAttribute('href', $attrs['src'] );
            
            $htmldoc->removeChild($image);
            $lnk->appendChild($image);
            $htmldoc->appendChild($lnk);
         }
     }
     
     return $htmldoc->saveHTML();
}

function wplb_modify_content( $content ){
		
    $links =  wplb_get_links( $content );
    $replacement = array(); 
   
    foreach( $links as $lnk ){       
        $newLnk = wplb_prepare_link( $lnk, 'lightbox' );        
        $content = str_replace($lnk, $newLnk, $content);
    }
	
	return $content;
}

function wplb_prepare_for_lightbox( $content ){
    global $post;
		
    $content = wplb_modify_content( $content );	
    if( $post )	{
            $post->post_content =  wplb_modify_content( $post->post_content );
    }	   
    return $content;
}
