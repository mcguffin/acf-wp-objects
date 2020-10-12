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
	if ( ! is_admin() && ACFWPObjects\Compat\ACF\PageLayout::instance()->get( $page_layout, 'save_post_content' ) ) {
		if ( $content = get_the_content( null, false, $post_id ) ) {
			$content = wptexturize( $content );
			$content = prepend_attachment($content);
			$content = wp_filter_content_tags($content);
			$content = do_shortcode( $content ); // 11
			$content = convert_smilies( $content );
			echo $content;
			return;
		};
	}

	while ( have_rows( $page_layout, $post_id ) ) {
		the_row();

		get_template_part( 'acf/layout', get_row_layout() );
	}
}
