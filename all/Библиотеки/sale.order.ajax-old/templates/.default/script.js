$(document).ready(function(){
	setEvents();
})

function setEvents()
{
	$('input[data-field=quantity]').on('change', function(){
		var form = $('#form_order');
		var form_container = '#'+$(form).data('container');
		var action = $(this).data('action');
		var container = $(this).parents('.item-container');
		var id = $(container).find('input[data-id]').data('id');
		$('input[name=basket_action]').val('Y');
		$('input[name=basket_action_type]').val(action);
		$('input[name=basket_action_item_id]').val(id);
		$('input[name=set_quantity]').val(parseInt($(this).val()));
		BX.showWait('body');
		var data = $(form).serialize();
		console.log(data);
		$.ajax({
			url: $(form).attr('action'),
			data: data,
			context: form_container,
			success: function(res){
				$(form_container).html(res);
				BX.closeWait('body');
				setEvents();
				updateCart();
			}
		})
	})
	$('.basket-action').on('click', function(){
		var form = $('#form_order');
		var form_container = '#'+$(form).data('container');
		var action = $(this).data('action');
		var container = $(this).parents('.item-container');
		var id = $(container).find('input[data-id]').data('id');
		$('input[name=basket_action]').val('Y');
		$('input[name=basket_action_type]').val(action);
		$('input[name=basket_action_item_id]').val(id);
		BX.showWait('body');
		$.ajax({
			url: $(form).attr('action'),
			data: $(form).serialize(),
			context: form_container,
			success: function(res){
				$(form_container).html(res);
				BX.closeWait('body');
				setEvents();
				updateCart();
			}
		})
	})
	$('.customer-select-2.select2').select2({
	    minimumResultsForSearch: -1,
	    theme: 'people-choise select2-container--default',
	    width: ''
	});
	$('.customer-select-3.select2').select2({
	    minimumResultsForSearch: -1,
	    theme: 'contact-data select2-container--default',
	    width: ''
	});
	/* Переключение способов доставки */
	$('.customer-info.deliv .one-img').on('click', function(){
		$('.customer-info.deliv .one-img').removeClass('da');
		$(this).addClass('da');
	});
	/* -Переключение способов доставки- */
	/* Переключение способов оплаты */
	$('.customer-info.pay .one-img').on('click', function(){
		$('.customer-info.pay .one-img').removeClass('da');
		$(this).addClass('da');
	});
	/* -Переключение способов доставки- */
	/* сворачивание по кнопке "свернуть" */
	$('.roll').on('click', function(){
		$(this).toggleClass('da');
		if ($(this).hasClass('da'))
	    {
	      $(this).html('Развернуть <i class="fas fa-angle-up"></i>');
	    }
	    else
	    {
	        $(this).html('Свернуть <i class="fas fa-angle-down"></i>');
	    }
		$(this).parent().parent().find('.customer-form').toggle(300);
	});
	/* -сворачивание по кнопке "свернуть"- */
	$('.send-order').off('click');
	$('.send-order').on('click', function(){
		$('input[name=check]').val('Y');
		$('input[name=save]').val('Y');
	});
	$('.location-select').off('change');
	$('.location-select').on('change', function(){
		$('#form_order').submit();
	});
	$('.invision').off('change');
	$('.invision').on('change', function(){
		$('#form_order').submit();
	});
	$('select[name=user_profile_id]').off('change');
	$('select[name=user_profile_id]').on('change', function(){
		console.log('change?');
		$('input[name=set_profile_id]').val('Y');
		$('#form_order').submit();
	});
	/* Обновление формы */
	$('#form_order').off('submit');
	$('#form_order').on('submit', function(e){
		var container = $(this).data('container');
		var data = $(this).serializeArray();
		console.log(data);
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
	/* -обновление формы */

	/* Удаление купона из списка */
	$('.delete_coupon').off('click');
	$('.delete_coupon').on('click', function(){
		$('input[name=DEL_COUPON]').val($(this).data('code'));
		$('#form_order').submit();
	})
	/* -Удаление купона из списка- */
	/* Отправка формы при изменении КМ от МКАД */
	$('input[name=FROM_MKAD]').on('input', function(){
 		var preg = $(this).val().replace(/[^.\d]+/g,"").replace( /^([^\.]*\.)|\./g, '$1' );
  		$(this).val(preg);
	})
	$('input[name=FROM_MKAD]').on('blur', function(){
		$('#form_order').submit();
	})
	/* -Отправка формы при изменении КМ от МКАД- */
	$('.customer-select-4.select2').select2({
	    minimumResultsForSearch: -1,
	    theme: 'contact-data select2-container--default',
	    width: ''
	});
}