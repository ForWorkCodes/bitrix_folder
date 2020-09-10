$(document).ready(function(){
	/* Добавление в корзину */
	$('.add_basket').on('click', function(){
		btn = $(this);
		$.ajax({
		  type: 'POST',
		  url: '/ajax/add_bask.php',
		  data: $(this).attr('href'),
		  success: function(data){
		    if (data == 1) {
		    	if (btn.hasClass('mathsig')) {}else {
		    		btn.html('Добавлено');
		    	}
		    }else {
		    	if (btn.hasClass('mathsig')) {}else {
		    		btn.html('Ошибка');
		    	}
		    }
		  }
		});
		$.ajax({
			type: "POST",
			url: "/ajax/new_bask.php",
			success: function(data) {
				$('.grid-part').html('');
				$('.grid-part').html(data);
			}
		});
		return false;
	});
	/* -Добавление в корзину- */
});