$(document).ready(function(){
	setEvents();
	var _mSelect2 = $(".smart-select").select2();
})

function setEvents()
{
	$('.saveOrder').off('click');
	$('.saveOrder').on('click', function(){
		$(this).parents('form').find('input[name=save]').val('Y');
		$(this).parents('form').submit();
	});
	$('.remove-btn').off('click');
	$('.remove-btn').on('click', function(){
		var id = $(this).data('id');
		var data = { 'ID' : id };
		$.ajax({
			url: '/local/ajax/delete_from_basket.php',
			data: data,
			success: function(result){
				var answer = JSON.parse(result);
				if (parseInt(answer.ALL_SUM) == 0)
					location.reload();
				else
				{
					$('#row_'+id).remove();
					$('#row2_'+id).remove();
					$('.total_price').html(answer.ALL_SUM);
					$('.basket-price').html(answer.ALL_SUM);
				}
			},
		});
	})
	$('.up-number .plus').off('click');
	$('.up-number .plus').on('click', function(){
		var count_now = parseInt($(this).parent().parent().find('input.number-input').val());
		var step = parseInt($(this).parent().parent().find('input[data-name=step]').val());
		var all_count = parseInt($(this).parent().parent().find('input[data-name=all_count]').val());
		var this_id = parseInt($(this).parent().parent().find('input[data-name=this_id]').val());
		console.log(count_now + step, all_count);
		if (count_now + step <= all_count)
		{
			$(this).parent().parent().find('input.number-input').val(count_now + step);
			$(this).parent().parent().find('input[data-name=now_count]').val(count_now + step);
			setQuantity(this_id, count_now + step);
		}
	})
	$('.up-number .minus').off('click');
	$('.up-number .minus').on('click', function(){
		var count_now = parseInt($(this).parent().parent().find('input.number-input').val());
		var step = parseInt($(this).parent().parent().find('input[data-name=step]').val());
		var all_count = parseInt($(this).parent().parent().find('input[data-name=all_count]').val());
		var this_id = parseInt($(this).parent().parent().find('input[data-name=this_id]').val());
		console.log(count_now - step);
		if (count_now - step > 0)
		{
			$(this).parent().parent().find('input.number-input').val(count_now - step);
			$(this).parent().parent().find('input[data-name=now_count]').val(count_now - step);
			setQuantity(this_id, count_now - step);
		}
	})
	$('input.number-input').off('change');
	$('input.number-input').on('change', function(){
		var all_count = parseInt($(this).parent().find('input[data-name=all_count]').val());
		var this_id = parseInt($(this).parent().find('input[data-name=this_id]').val());
		var step = parseInt($(this).parent().find('input[data-name=step]').val());
		if ($(this).val() <= all_count && parseInt($(this).val()) > 0)
		{
			$(this).parent().find('input[data-name=now_count]').val(Math.ceil(parseInt($(this).val()) / step) * step);
			$(this).val(Math.ceil(parseInt($(this).val()) / step) * step);
			setQuantity(this_id, parseInt($(this).val()));
		}
		else if (parseInt($(this).val()) > 0)
		{
			$(this).val(Math.ceil(all_count / parseInt($(this).val())) * step);
			$(this).parent().find('input[data-name=now_count]').val(Math.ceil(all_count / parseInt($(this).val())) * step);
			setQuantity(this_id, parseInt($(this).val()));
		}
		else
		{
			$(this).parent().find('input[data-name=now_count]').val(step);
			$(this).parent().find('input[data-name=now_count]').val(step);
			$(this).val(step);	
		}
	})
	$('input[type=radio], input[type=checkbox]').off('change');
	$('input[type=radio], input[type=checkbox]').on('change', function(){
		console.log($(this));
		$('#form_order').submit();
	});
	$('select.select2-city').off('change');
	$('select.select2-city').on('change', function(){
		console.log($(this));
		$('#form_order').submit();
	});
	$('.submit-change').off('click');
	$('.submit-change').on('click', function(){
		var move = $(this).data('type');
		var block = $(this).parents('.grey-block');
		var type = $(block).data('name');
		if (type == 'region_block')
		{
			$('input[name=forceOpen]').val('');
		}
		$('input[name='+type+']').val('N');
		var have = false;
		switch (move)
		{
			case 'forward':
				var blocks = $('.grey-block');
				break;
			case 'back':
				var blocks = $('.grey-block').get().reverse();
				break;
		}
		console.log(move, type);
		$(blocks).each(function(i, elem){
			console.log(i, elem);
			if (have)
			{
				$('input[name='+$(elem).data('name')+']').val('Y');
				have = false;
			}
			if ($(elem).data('name') == type)
			{
				have = true;
			}			
		});
		$('#form_order').submit();
	});
	$('#form_order').off('submit');
	$('#form_order').on('submit', function(e){
		var container = $(this).data('container');
		var data = $(this).serializeArray();
		var url = $(this).attr('action');
		console.log('container', container);
		console.log('url', url);
		console.log('data', data);
		BX.showWait();
		$.ajax({
			url: url,
			data: data,
			success: function(res)
			{
				BX.closeWait();
				$('#'+container).html(res);
				setEvents();
			}
		});
		return false;
	})
	/* Кнопка изменить в корзине */
	$('.grey-block .change').off('click');
	  $('.grey-block .change').on('click', function() {
	  	var name = $(this).data('target');
	  	$('input[name=' + name + ']').val('Y');
	  	if (name == 'region_block')
	  	{
	  		$('input[name=forceOpen]').val('region_block');
	  	}
	  	$('input[name=forceOpen]').val();
	  	$(this).parents('.grey-block').toggleClass('active');
	    $(this).parent().parent().find('.show-before').remove();
	    $(this).parent().parent().find('.show-after').show();
	    $(this).remove();
	    return false;
	  });
	  /* -Кнопка изменить в корзине- */

	$('.select2-city').select2({
		minimumResultsForSearch: -1,
		width: '100%',
		templateResult: formatState
	});
}

function setQuantity(id, quantity)
{
	var data = {
		'ID' : id,
		'QUANTITY' : quantity
	};
	$.ajax({
		url: '/local/ajax/change_quantity.php',
		data: data,
		success: function(result){
			var answer = JSON.parse(result);
			if (parseInt(answer.ALL_SUM) == 0)
				location.reload();
			else
			{
				$('.total_price').html(answer.ALL_SUM);
				$('.basket-price').html(answer.ALL_SUM);
				$('#current-price-'+id).html(answer.CURRENT_SUM);
			}
		},
	});
}