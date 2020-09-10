$(document).ready(function() {
	$('body').on('click', '.more-btn-show', function() {
		var url = $('.link.active').next('.numeral__cubiic').attr('href');
		if ($('.link-box .link:last-child').attr('href')) { // Если не последняя страница
			$.ajax({
				url: url,
				success: function(data) {
					html = $($.parseHTML(data));
					$('#ajax-reload').append(html.find('#ajax-reload').html());
					$('.link-box').empty().html(html.find('.link-box').html());
				}
			});
		}
	});
});