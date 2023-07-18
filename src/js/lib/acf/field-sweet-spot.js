import $ from 'jquery';

/**
 *	Test Cases
 *	- [x] Image Library modal
 *	- [x] Image Library single view
 *	- [ ] Image select modal
 *	- [ ] Image Thumbnail field
 *
 *
 *
 */
const SweetSpotField = acf.Field.extend({

	type: 'image_sweet_spot',

	events: {
		'input input[type="range"]': 'onChange',
		'change input': 'onChange'
	},
//*/
	$control: function(){
		return this.$('.acf-input-wrap');
	},
	$input: function(){
		return this.$('input');
	},
//*/
	$inputX: function() {
		return this.$('input[type="range"].-sweet-spot-x');
	},
	$inputY: function() {
		return this.$('input[type="range"].-sweet-spot-y');
	},
	$inputAltX: function() {
		return this.$inputX().next('[type="number"]');
	},
	$inputAltY: function() {
		return this.$inputY().next('[type="number"]');
	},
	$image: function() {
		const selectors = [
			'.media-modal img.details-image',
			'.media-modal .thumbnail-image img', // media library modal
			'#post-body-content .wp_attachment_image img', // media library single edit
		];
		return $( selectors.join(',') ).first();
	},

	initialize: function() {
		this.clickedContainer = this.clickedContainer.bind(this)
		this.setupImage = this.setupImage.bind(this);
		this.$marker = false;
		this.$markerContainer = false;
		if ( this.$image().length ) {
			if ( this.$image().get(0).complete ) {
				this.setupImage();
			} else {
				this.$image().on('load',this.setupImage );
			}
		}

		//this.setupImage()
	},
	setupImage: function() {
		let $img = this.$image();
		if ( ! $img.length ) {
			return;
		}
		//$img.wrap();
		// if ( $img.parent().css('position') === 'static' ) {
		// 	$img.parent().css('position', 'relative' );
		// }
		this.$markerContainer = $('<div class="sweet-spot-container"></div>')
			.css({
				'left':$img.get(0).offsetLeft + 'px',
				'top':$img.get(0).offsetTop + 'px',
				'width':$img.width() + 'px',
				'height':$img.height() + 'px',
			})
			.insertAfter($img)
			.on('mouseup',this.clickedContainer );
		this.$marker = $('<span class="sweet-spot-marker"></span>').appendTo( this.$markerContainer );
		this.setValue({})
	},
	clickedContainer: function(e) {

		this.setValue({
			x: Math.round( 100 * e.offsetX / this.$markerContainer.width() ),
			y: Math.round( 100 * e.offsetY / this.$markerContainer.height() ),
		});

	},
	getValue: function() {
		return {
			'x': this.$inputX().val(),
			'y': this.$inputY().val(),
		};
	},
	setValue: function( val ){
		this.busy = true;

		// update input
		if ( !! val.x ) {
			acf.val( this.$inputX(), val.x );
			acf.val( this.$inputAltX(), this.$inputX().val(), true );
		}
		if ( !! val.y ) {
			acf.val( this.$inputY(), val.y );
			acf.val( this.$inputAltY(), this.$inputY().val(), true );
		}

		// update image marker
		this.$marker && this.$marker.css({
			'left':this.$inputX().val() + '%',
			'top':this.$inputY().val() + '%',
		})

		this.busy = false;
	},

	onChange: function( event, $input ) {
		if ( ! this.busy ) {
			if ( $input.get(0) === this.$inputX().get(0) || $input.get(0) === this.$inputAltX().get(0) ) {
				this.setValue( { x: $input.val() } )
			}
			if ( $input.get(0) === this.$inputY().get(0) || $input.get(0) === this.$inputAltY().get(0) ) {
				this.setValue( { y: $input.val() } )
			}
		}
	},


});

acf.registerFieldType(SweetSpotField)
