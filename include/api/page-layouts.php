<?php

/**
 *	xxx
 *	@param Array $args ACF Field Group data
 */
function acf_add_page_layout( $args = '' ) {
	ACFWPObjects\Compat\ACF\PageLayout::instance()->register( $args );
}

/**
 *	render layout template
 */
function acf_page_layouts( $page_layout, $post_id = false ) {
	while ( have_rows( $page_layout, $post_id ) ) {
		the_row();

		get_template_part( 'acf/layout', get_row_layout() );
	}
}
