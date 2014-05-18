jQuery(document).ready(function ($) {
	$('#fast-multisite-switching-search').keyup(function (event) {
		var input = $(this);
		var search = input.val();
		var sites = $('table').eq(input.parents('table').first().index() + 1).find('td')


		sites.each(function (i, site) {
			site = $(site);
			var matched = match(search, site);

			if (matched && site.hasClass('hidden')) {
				site.removeClass('hidden');
			} else if (!matched && !site.hasClass('hidden')) {
				site.addClass('hidden');
			}
		});

		if (event.which == 13 && sites.filter(':visible').length == 1) {
			window.location.href = sites.filter(':visible').first().find("a:contains('Dashboard')").attr("href");
		} else {
			event.preventDefault();
			return;
		}

		function match(search, site) {
			return $(site).find('h3, i').text().toLowerCase().indexOf(search.toLowerCase()) != -1;
		}
	});
});