<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="popupView">
	<div class="close-popup">
		<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/img/svg/close.html', array(), array())?>
	</div>
	<div class="popupView__title">
		Смена пароля
	</div>
	<div class="changepass-body">
		<pre><?print_r($arParams)?></pre>
		<span class="error-span" style="color: red;"></span>
		<?
		$strAction = substr($arResult['AUTH_URL'], 1);
		$strAction = $arParams["ACTION_URL"].$strAction;
		?>
	<form data-container="<?= $arParams['CONTAINER'] ?>" name="bform" method="post" action="<?=$arParams["ACTION_URL"]?>">
		<?

		if ($arResult['SHOW_ERRORS'] == 'Y' && $arResult['ERROR'] ) {
			?><span class="error-span" style="color: red;"><?echo $arResult["ERROR_MESSAGE"]["MESSAGE"];?></span><?
		} elseif ($arResult['USER_LOGIN'] != '') {
			//LocalRedirect($APPLICATION->GetCurDir());
		}	
		?>
		<?if (strlen($arResult["BACKURL"]) > 0): ?>
		<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
		<? endif ?>
		<input type="hidden" name="AUTH_FORM" value="Y">
		<input type="hidden" name="TYPE" value="CHANGE_PWD">
		<div class="buildForm">
			<div class="buildForm__col">
				<label class="custom-label" for="1227">
					Логин 
				</label>
				<input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" class="custom-input" />
			</div>
			<div class="buildForm__col">
				<label class="custom-label" for="12111">
					Контрольная строка
				</label>
				<input type="text" name="USER_CHECKWORD" maxlength="50" value="<?=$arResult["USER_CHECKWORD"]?>" class="custom-input" autocomplete="off" />
			</div>
			<div class="buildForm__col">
				<label class="custom-label" for="12111">
					Пароль
				</label>
				<input type="password" name="USER_PASSWORD" maxlength="50" value="<?=$arResult["USER_PASSWORD"]?>" class="custom-input" autocomplete="off" />
			</div>
			<div class="buildForm__col">
				<label class="custom-label" for="12111">
					Подтверждение пароля
				</label>
				<input type="password" name="USER_CONFIRM_PASSWORD" maxlength="50" value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>" class="custom-input" autocomplete="off" />
			</div>
		</div>
		<div class="btnBox">
			<input class="custom-submit" type="submit" name="change_pwd" value="<?=GetMessage("AUTH_CHANGE")?>" />
		</div>
	</form>
	</div>
</div>

<script type="text/javascript">
	$('.changepass-body').find('form[name=bform]').on('submit', function(e){
		e.preventDefault();
		var data = $(this).serializeArray();
		var url = $(this).attr('action');
		var container = $(this).data('container');
		//BX.showWait('.' + container);
		$('.changepass-body').html('<div class="white-center"><i class="fas fa-spinner fa-spin big"></i></div>');
		$.ajax({
			url: url,
			data: data,
			success: function(html){
				//BX.closeWait('.' + container);
				console.log('OK');
				//$('.changepass-body').find('.error-span').html(html);
				$('.' + container).html(html);
			}
		});
	})
	modal_forms();
</script>


