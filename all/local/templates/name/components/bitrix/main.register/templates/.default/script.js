$(document).ready(function() {
	$('body').on('submit', '#reg-form', function() {
		var url = $(this).attr('action'),
			data = $(this).serializeArray(),
			method = $(this).attr('method');
		$.ajax({
			url: url,
			type: method,
			data: data,
			success: function(data) {
				// console.log(data);
				$('#ajax-reg-load').html(data);
			}
		});
		
		return false;
	});
});