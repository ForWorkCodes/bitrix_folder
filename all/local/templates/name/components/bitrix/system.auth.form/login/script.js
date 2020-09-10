$(document).ready(function() {
	$('body').on('submit', '#log-form', function() {
		var url = $(this).attr('action'),
			data = $(this).serializeArray(),
			method = $(this).attr('method');
		$.ajax({
			url: url,
			type: method,
			data: data,
			success: function(data) {
				// console.log(data);
				$('#ajax-log-load').html(data);
			}
		});
		
		return false;
	});
});