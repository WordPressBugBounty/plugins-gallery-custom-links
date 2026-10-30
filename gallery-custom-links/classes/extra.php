<?php

class Meow_MGCL_Extra
{
  public function __construct() {
		if ( function_exists( 'kadence_gallery' ) )
			add_filter( 'gallery_custom_links_classes', array( $this, 'kadence_gallery' ), 10, 1 );
		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'gallery_custom_links_classes', array( $this, 'wpbakery' ), 10, 1 );
		}
	}

	function kadence_gallery( $classes ) {
		array_push( $classes, '.kad-wp-gallery' );
		return $classes;
	}

	function wpbakery( $classes ) {
		array_push( $classes, '.w-gallery' );
		return $classes;
	}
}

?>