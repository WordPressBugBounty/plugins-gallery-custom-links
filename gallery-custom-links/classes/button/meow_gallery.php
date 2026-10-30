<?php

class Meow_MGCL_Core_Button_Meow_Gallery {

  private $core;
  private $in_link_style_printed = false;

	public function __construct( $core ) {
    $this->core = $core;
    add_filter( 'mgcl_linkers', array( $this, 'linker' ), 100, 8 );
    add_filter( 'mgcl_button_linker', array( $this, 'button_linker' ), 101, 6 );
	}

  function get_style( $id ) {
    return "<style>
      #mgcl-$id {
      }
      #mgcl-$id a {
        position: absolute; 
        bottom: 8px; left: 8px;
        height: auto !important;
        width: calc(100% - 16px);
        flex: none !important;
        box-sizing: border-box;
      }
      #mgcl-$id a {
        font-size: 15px;
        text-decoration: none;
        padding: 2px 10px;
        box-shadow: 0px 0px 1px 0px rgba(0, 0, 0, 0.5);
        border-radius: 10px;
        text-align: center;
        background: rgba(15, 115, 239, 0.80);
        color: white;
        z-index: 100;
      }
      #mgcl-$id a:hover { 
        background: rgba(15, 115, 239, 0.9);
      }
    </style>";
  }
  function get_button_style( $id ) {
    return "<style>
      #mgcl-$id a {
        position: absolute; 
        bottom: 8px; left: 8px;
        height: auto !important;
        width: calc(100% - 16px);
        flex: none !important;
        box-sizing: border-box;
        font-size: 15px;
        text-decoration: none;
        padding: 2px 10px;
        box-shadow: 0px 0px 1px 0px rgba(0, 0, 0, 0.5);
        border-radius: 10px;
        text-align: center;
        background: rgba(15, 115, 239, 0.80);
        color: white;
        z-index: 100;
      }
      #mgcl-$id a:hover {
        background: rgba(15, 115, 239, 0.9);
      }
    </style>";
  }

	function linker( $handled, $element, $parent, $mediaId, $url, $rel, $aria, $target ) {
    if ( $handled || $this->core->parsingEngine !== 'HtmlDomParser' ) {
      return $handled;
    }

    if ( $this->is_meow_gallery_link( $parent ) ) {
      return $this->add_button_in_link( $element, $parent, $mediaId, $url, $rel, $aria, $target );
    }

    // Meow Gallery before 5.0 rendered its layouts in PHP, with the image under .mgl-icon.
    $anotherParent = $parent->parent();
    $url = $this->core->sanitize_url( $url );

    if ( !$anotherParent || $anotherParent->class !== 'mgl-icon' ) {
      return $handled;
    }

    $id = uniqid();
    $style = $this->get_style( $id );

    if ( $this->core->enableLogs ) {
      error_log( 'Linker: Will embed the IMG tag.' );
    }
    $label = get_option( 'mgcl_button_label', "Click here" );
    $element->outertext = $style . '<div id="mgcl-' . esc_attr( $id ) . '">' . $element . '<a href="' . esc_attr( $url ) .
      '" class="custom-link-button no-lightbox" onclick="event.stopPropagation()" target="' . esc_attr( $target ) . '" rel="' . esc_attr( $rel ) . '">
      ' . esc_html( $label ) . '</a></div>';
    return true;
	}

  // Meow Gallery 5+ prints each image under .mgl-gallery-images, wrapped in its own link when it has
  // one, and its script then moves that exact element (only an img, a or picture) into the layout.
  // Anything added next to it is left behind, so the button has to live inside that link.
  function is_meow_gallery_link( $parent ) {
    if ( !$parent || $parent->tag !== 'a' ) {
      return false;
    }
    $container = $parent->parent();
    return $container && in_array( 'mgl-gallery-images', explode( ' ', (string)$container->class ) );
  }

  // A link can't contain another link, so the button is a span: the whole image already leads to
  // the same URL. The default linker still runs, to stop the lightbox on the image.
  function add_button_in_link( $element, $parent, $mediaId, $url, $rel, $aria, $target ) {
    $linker = new Meow_MGCL_Linker( $this->core );
    $linker->linker( $element, $parent, $mediaId, $url, $rel, $aria, $target );
    $label = get_option( 'mgcl_button_label', "Click here" );
    $parent->innertext = $parent->innertext . $this->get_in_link_style() .
      '<span class="custom-link-button mgcl-mgl-button">' . esc_html( $label ) . '</span>';
    return true;
  }

  function get_in_link_style() {
    if ( $this->in_link_style_printed ) {
      return '';
    }
    $this->in_link_style_printed = true;
    return "<style>
      .mgl-gallery .mgcl-mgl-button {
        position: absolute;
        bottom: 8px; left: 8px; right: 8px;
        box-sizing: border-box;
        max-width: calc(100% - 16px);
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        font-size: 15px;
        line-height: 1.5;
        padding: 2px 10px;
        box-shadow: 0px 0px 1px 0px rgba(0, 0, 0, 0.5);
        border-radius: 10px;
        text-align: center;
        background: rgba(15, 115, 239, 0.80);
        color: white;
        z-index: 100;
      }
      .mgl-gallery a:hover .mgcl-mgl-button {
        background: rgba(15, 115, 239, 0.9);
      }
      .mgl-gallery-images .mgcl-mgl-button {
        display: none;
      }
    </style>";
  }

  function button_linker( $html, $class_names, $url, $label, $rel, $target ) {
    $classes = explode( ' ', $class_names );
    if ( !in_array( 'mgl-gallery-images', $classes ) ) {
      return $html;
    }

    $id = uniqid();
    $style = $this->get_button_style( $id );
    return $style . '<div id="mgcl-' . esc_attr( $id ) . '"><a href="' . esc_attr( $url ) . '" class="custom-link-button no-lightbox" onclick="event.stopPropagation()" target="' . esc_attr( $target ) . '" rel="' . esc_attr( $rel ) . '">
      ' . esc_html( $label ) . '</a></div>';
  }
}
