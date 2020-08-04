<?php

/**
 *	Get a ACFCustomizer instance
 *
 *	@return ACFCustomizer\Compat\ACF\Customize
 */
function acf_add_page_layout( $args = '' ) {
	ACFWPObjects\Compat\ACF\PageLayout::instance()->register( $args );
}

/**
 *	render layout template
 */
function acf_page_layouts( $page_layout ) {
	while ( have_rows( $page_layout ) ) {
		the_row();

		get_template_part( 'acf/layout', get_row_layout() );
	}
}
