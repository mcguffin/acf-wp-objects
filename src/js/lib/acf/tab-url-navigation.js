import $ from 'jquery';



if ( !! document.location.hash ) {
	const tabTitle = decodeURIComponent(document.location.hash.substr(1));
	const parentTab = tab => {
		let $el = tab.$el.parent().closest('.acf-field')
		while ( ! $el.prevAll('.acf-field-tab:first').length && $el.is('.acf-field') ) {
			$el = $el.parent().closest('.acf-field')
		}
		if ( ! $el.is('.acf-field') ) {
			return false;
		}
		return acf.getField( $el.prevAll('.acf-field-tab:first') )
	}
	if ( !! tabTitle ) {
		acf.addAction('ready_field/type=tab', myTab => {
			let btn, idx, tab = myTab
			//const $selectedTab = $(`.acf-tab-group a[data-endpoint]:contains("${tabTitle}")`);
			if ( tab.$el.find('.acf-label').text().trim() === tabTitle ) {
				while ( !! tab && ! myTab.$el.next().is(':visible') ) {
					btn = $(`li a[data-key="${tab.findTab().attr('data-key')}"]`)
					idx = btn.closest('ul').find('li').index(btn.closest('li'));
					tab.tabs.openTab( tab.tabs.tabs[idx] )
					tab = parentTab( tab )
				}
				history.replaceState(null, null, ' ');
			}
		});
	}

}
