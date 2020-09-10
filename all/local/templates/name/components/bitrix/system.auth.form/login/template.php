<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CJSCore::Init();
prr($arResult)?>
<div class="popUp__content">
	<form name="system_auth_form<?=$arResult["RND"]?>" method="post" target="_top" action="<?=$arParams["AUTH_URL"]?>" id="log-form">
		<?if($arResult["BACKURL"] <> ''):?>
			<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
		<?endif?>
		<?foreach ($arResult["POST"] as $key => $value):?>
			<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
		<?endforeach?>
			<input type="hidden" name="AUTH_FORM" value="Y" />
			<input type="hidden" name="TYPE" value="AUTH" />
		<? if ($arResult['ERROR_MESSAGE']): ?>
			<p class="error"><?=$arResult['ERROR_MESSAGE']['MESSAGE'] ?></p>
			<br>
		<? endif ?>
		<? if ($arResult['USER_NAME'] && !$arResult['STORE_PASSWORD']): ?>
			<p class="green">Авторизация успешна</p>
			<br>
			<script>
				location.reload();
			</script>	
		<? endif ?>
		<div class="popUp__form">
			<div class="popUp__block">
				<label class="custom-label">Логин</label>
				<input type="text" name="USER_LOGIN" maxlength="50" value="" size="17" class="custom-input"/>
				<script>
					BX.ready(function() {
						var loginCookie = BX.getCookie("<?=CUtil::JSEscape($arResult["~LOGIN_COOKIE_NAME"])?>");
						if (loginCookie)
						{
							var form = document.forms["system_auth_form<?=$arResult["RND"]?>"];
							var loginInput = form.elements["USER_LOGIN"];
							loginInput.value = loginCookie;
						}
					});
				</script>
			</div>
			<div class="popUp__block">
				<label class="custom-label">Пароль<span>*</span></label>
				<input type="password" name="USER_PASSWORD" maxlength="255" size="17" autocomplete="off" class="custom-input"/>
				<?if($arResult["SECURE_AUTH"]):?>
					<span class="bx-auth-secure" id="bx_auth_secure<?=$arResult["RND"]?>" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
						<div class="bx-auth-secure-icon"></div>
					</span>
					<noscript>
					<span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
						<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
					</span>
					</noscript>
					<script type="text/javascript">
					document.getElementById('bx_auth_secure<?=$arResult["RND"]?>').style.display = 'inline-block';
					</script>
				<?endif?>
			</div>
		</div>
		<div class="formSolve">
			<a class="blue-to-green for-password" href="">Забыли пароль?</a>
		</div>
		<div class="formBtn">
			<input type="submit" name="Login" value="войти" class="greenCustomButton" />
		</div>
	</form>
</div>
<div class="popUp__end">
	<a class="allCateg__link" href="">
		регистрация
		<div class="allCateg__arr">
			<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/img/icons/svg/drop.html', array(), array())?>
		</div>
	</a>
</div>