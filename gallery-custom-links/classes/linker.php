<?php

class Meow_MGCL_Linker {

  private $core;

	public function __construct( $core ) {
    $this->core = $core;
    //add_filter( 'mgcl_linkers', array( $this, 'linker' ), 100, 6 );
	}

	// XXXX: Custom code with $aria, Christoph Letmaier, 14.01.2020
	function linker( $element, $parent, $mediaId, $url, $rel, $aria, $target ) {
    // Let's look for the closest link tag enclosing the image
    $url = $this->core->sanitize_url( $url );
    $media = get_post( $mediaId );
    $title = the_title_attribute(  array( 'echo' => false, 'post' => $media )  );
    $potentialLinkNode = $parent;
    $maxDepth = 5;
    do {
      if ( !empty( $potentialLinkNode ) && !empty( $potentialLinkNode->tag ) && $potentialLinkNode->tag === 'a' ) {

        if ( $this->core->enableLogs ) {
          error_log( 'Linker: The current link (' . $potentialLinkNode->{'href'} . ') will be replaced.' );
        }

        if ( $this->core->parsingEngine === 'HtmlDomParser' ) {
          $potentialLinkNode->{'href'} = esc_url( $url );
          $class = $potentialLinkNode->{'class'};
          $class = empty( $class ) ? 'custom-link no-lightbox' : ( $class . ' custom-link no-lightbox' );
          $potentialLinkNode->{'class'} = $class;
          $potentialLinkNode->{'title'} = $title;
          $potentialLinkNode->{'onclick'} = 'event.stopPropagation()';
          if ( !empty( $target ) )
            $potentialLinkNode->{'target'} = esc_attr( $target );
          if ( !empty( $rel ) )
            $potentialLinkNode->{'rel'} = esc_attr( $rel );
          if ( !empty( $aria ) )
            $potentialLinkNode->{'aria-label'} = esc_attr( $aria );
        }
        else {
          $potentialLinkNode->attr( 'href', esc_url( $url ) );
          $class = $potentialLinkNode->attr( 'class' );
          $class = empty( $class ) ? 'custom-link no-lightbox' : ( $class . ' custom-link no-lightbox' );
          $potentialLinkNode->attr( 'class', $class );
          $potentialLinkNode->attr( 'title', $title );
          $potentialLinkNode->attr( 'onclick', 'event.stopPropagation()' );
          if ( !empty( $target ) )
            $potentialLinkNode->attr( 'target', esc_attr( $target ) );
          if ( !empty( $rel ) )
            $potentialLinkNode->attr( 'rel', esc_attr( $rel ) );
          if ( !empty( $aria ) )
            $potentialLinkNode->attr( 'aria-label', esc_attr( $aria ) );
        }
        return true;
      }
      if ( method_exists( $potentialLinkNode, 'parent' ) )
        $potentialLinkNode = $potentialLinkNode->parent();
      else
        break;
    }
    while ( $potentialLinkNode && $maxDepth-- >= 0 );

    // There is no link tag, so we add one and move the image under it
    if ( $this->core->enableLogs ) {
      error_log( 'Linker: Will embed the IMG tag.' );
    }
    if ( $this->core->parsingEngine === 'HtmlDomParser' ) {
	// XXXX: Custom code with $aria, Christoph Letmaier, 22.01.2020
	$element->outertext = '<a href="' . esc_attr( $url ) . '" class="custom-link no-lightbox" title="' . esc_attr( $title ) . '" aria-label="' . esc_attr( $aria ) . '" onclick="event.stopPropagation()" target="' . esc_attr( $target ) . '" rel="' . esc_attr( $rel ) . '">' . $element . '</a>';
    }
    else {
      // For DiDom, we need to wrap the element in an anchor tag
      if ( isset( $parent->tag ) && $parent->tag === 'figure' )
        $parent = $parent->parent();

      // Check if parent is a valid Element (not Document)
      if ( !isset( $parent->tag ) || !method_exists( $parent, 'children' ) ) {
        // If parent is Document or invalid, just wrap the element itself
        $a = new DiDom\Element('a');
        $a->attr( 'href', esc_url( $url ) );
        $a->attr( 'class', 'custom-link no-lightbox' );
        $a->attr( 'onclick', 'event.stopPropagation()' );
        $a->attr( 'target', esc_attr( $target ) );
        $a->attr( 'rel', esc_attr( $rel ) );
        $a->attr( 'aria-label', esc_attr( $aria ) );
        $a->appendChild( $element );
        $element->replace( $a );
      }
      else {
        // Parent is a valid element, wrap its children
        $a = new DiDom\Element('a');
        $a->attr( 'href', esc_url( $url ) );
        $a->attr( 'class', 'custom-link no-lightbox' );
        $a->attr( 'onclick', 'event.stopPropagation()' );
        $a->attr( 'target', esc_attr( $target ) );
        $a->attr( 'rel', esc_attr( $rel ) );
        // XXXX: Custom code with $aria, Christoph Letmaier, 22.01.2020
        $a->attr( 'aria-label', esc_attr( $aria ) );

        // Move all children from parent to the new anchor element
        foreach( $parent->children() as $child ) {
          $a->appendChild( $child );
        }

        $parent->appendChild( $a );
      }
    }
    return true;
	}
}

?>