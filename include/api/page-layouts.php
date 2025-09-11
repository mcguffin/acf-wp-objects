<?php

/**
 *	xxx
 *	@param Array $args ACF Field Group data
 */
function acf_add_page_layout( $args = '' ) {
	ACFWPObjects\Compat\ACF\PageLayout::instance()->register( $args );
}

/**
 *	@param String $page_layout
 *	@return Array
 */
function acf_get_page_layout( $page_layout ) {
	return ACFWPObjects\Compat\ACF\PageLayout::instance()->get( $page_layout );
}


/**
 *	@param String $page_layout
 *	@return Array
 */
function acf_get_page_layouts() {
	return ACFWPObjects\Compat\ACF\PageLayout::instance()->get();
}

/**
 *	render layout template
 */
function acf_page_layouts( $page_layout, $post_id = false ) {
	while ( have_rows( $page_layout, $post_id ) ) {
		the_row();

		$args = get_row( true );
		$args = apply_filters( 'acf_page_layout_args/' . $page_layout, $args );
		$args = apply_filters( 'acf_page_layout_args/' . $page_layout . '/layout=' . get_row_layout(), $args );

		get_template_part( 'acf/layout', get_row_layout(), $args );
	}
}
