import $ from 'jquery';
import { findClosestField } from '../find-field-lib';
import { getPostAcf, cachePost } from '../post-cache';
import { fillTemplate } from '../template';

const updateTarget = async field => {
	const template    = field.$inputWrap().get(0).querySelector('template')
	const postType    = field.get('post_type')
	const postId      = field.val()
	const targetField = findClosestField(field, field.get('target'));
	if ( postId ) {
		const vals = await getPostAcf(postId, postType)
		targetField.$el.find('.acf-input').html(fillTemplate(vals, template.innerHTML))
	} else {
		targetField.$el.find('.acf-input').html('')
	}
}

const setupField = field => {
	if ( field.get('_template_inited') ) {
		return;
	}
	const template  = field.$inputWrap().get(0).querySelector('template')
	const targetKey = field.get('target')
	const postType  = field.get('post_type')
	if ( ! template || ! targetKey || ! postType ) {
		return
	}

	field.set('_template_inited',true)
	field.$input().on('change',e => {
		updateTarget(field)
	})
	updateTarget(field)
}

acf.addAction( 'ready_field/type=post_object', setupField );
acf.addAction( 'append_field/type=post_object', setupField );
