const postTypeCache   = {}
const postCache       = {}

const requests = {}

const fetchOrGet = async path => {
	if ( ! requests[path] ) {
		requests[path] = wp.apiFetch( { path } )
	}
	return requests[path]
}

const cachePost = async (postId,postType) => {
	if ( !! postId && !! postType && ! postCache[postId] ) {
		if ( !! postType && ! postTypeCache[postType] ) {
			postTypeCache[postType] = await fetchOrGet(`/wp/v2/types/${postType}`) // wp.apiFetch({path: `/wp/v2/types/${postType}`})
		}
		postCache[postId] = await fetchOrGet(`/${postTypeCache[postType].rest_namespace}/${postTypeCache[postType].rest_base}/${postId}`) // wp.apiFetch({path: `/${postTypeCache[postType].rest_namespace}/${postTypeCache[postType].rest_base}/${postId}`})
	}
	return postCache[postId]
}
const cacheField = async field => {
	const postId   = parseInt(field.val())
	const postType = field.get('post_type')

	await cachePost(postId,postType)
}

// TODO check if needed ... postacf/-obhject-message-template.js should do this task
acf.addAction( 'ready_field/type=post_object', async (field) => {
	cacheField(field)
	field.$el.on('change select',() => cacheField(field) )
} );

const getPostAcf = async (postId,postType) => {
	const post = await cachePost(postId,postType)
	return post.acf
}

export {
	cachePost,
	getPostAcf
}
