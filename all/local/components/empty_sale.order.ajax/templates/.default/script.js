$(document).ready(function() {
	$('body').on('submit', '#ajax_order', function(event) {
		$('#ajax_on').val('Y');

		var form = $(this),
			url = $(this).attr('action'),
			data = $(this).serialize();


		$.ajax({
			url: url,
			type: 'POST',
			data: data,
			success: function(e)
			{
				$('#order-ajax').html(e);
			}
		});
		
		return false;
	});
	$('body').on('change', '.push_order', function(event) {
		$('#ajax_on').val('Y');
		
		var btn = $(this),
			form = btn.closest('#ajax_order'),
			data = form.serialize(),
			url = form.attr('action'),
			method = form.attr('method');

		$.ajax({
			url: url,
			type: method,
			data: data,
			success: function(e)
			{
				$('#order-ajax').html(e);
			}
		});
		
	});
});

function cl(e)
{
	console.log(e);
}