import $ from 'jquery';



if ( !! document.location.hash ) {
	const tabTitle = decodeURIComponent(document.location.hash.substr(1));
	if ( !! tabTitle ) {
		acf.addAction('ready_field/type=tab', f => {
			//const $selectedTab = $(`.acf-tab-group a[data-endpoint]:contains("${tabTitle}")`);
			if ( f.$el.find('.acf-label').text().trim() === tabTitle ) {
				const btn = $(`li a[data-key="${f.findTab().attr('data-key')}"]`)
				const idx = btn.closest('ul').find('li').index(btn.closest('li'));
				console.log(idx)
				if ( idx !== -1 ) {
					f.tabs.openTab( f.tabs.tabs[idx] )
					// @see https://stackoverflow.com/a/52466271/1983694
					history.replaceState(null, null, ' ');
				}
			}
		});
	}

}
