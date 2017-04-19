$(function() {
	$(document).on('mousedown', '.c1Tabs > h3', function() {
		var h = $(this);
		h.addClass('c1TabActive').siblings().removeClass('c1TabActive');
	});
	$('.c1Tabs > h3:first-child').addClass('c1TabActive');
});
