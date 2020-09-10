<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arResult["PHONE_REGISTRATION"])
{
	CJSCore::Init('phone_auth');
}
?>
<br>
<div class="bx-auth">

<?
ShowMessage($arParams["~AUTH_RESULT"]);
$message = $APPLICATION->arAuthResult;
?>
<? if ($message): ?>
	<? if ($message['TYPE'] == 'OK'): ?>
		<div class="green">
			<? prr($message['MESSAGE']) ?>
		</div>
		<script>
			$('[name="bform"]').remove();
		</script>
	<? elseif($message['TYPE'] == 'ERROR'): ?>
		<div class="red">
			<? prr($message['MESSAGE']) ?>
		</div>
	<? endif ?>
<? endif ?>
<?if($arResult["SHOW_FORM"]):?>
<form method="post" action="<?=$arResult["AUTH_FORM"]?>" name="bform" class="change-pas-form">
	<?if (strlen($arResult["BACKURL"]) > 0): ?>
		<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
	<? endif ?>
	<input type="hidden" name="AUTH_FORM" value="Y">
	<input type="hidden" name="TYPE" value="CHANGE_PWD">
	<table class="data-table bx-changepass-table">
		<?if($arResult["PHONE_REGISTRATION"]):?>
			<div>
				<label>
					<?echo GetMessage("sys_auth_chpass_phone_number")?>
					<input type="text" value="<?=htmlspecialcharsbx($arResult["USER_PHONE_NUMBER"])?>" class="bx-auth-input custom-input" disabled="disabled" id="pp1"/>
					<input type="hidden" name="USER_PHONE_NUMBER" value="<?=htmlspecialcharsbx($arResult["USER_PHONE_NUMBER"])?>" />
				</label>
			</div>
			<div>
				<label>
					<?echo GetMessage("sys_auth_chpass_code")?><span class="starrequired">*</span>
					<input type="text" name="USER_CHECKWORD" maxlength="50" value="<?=$arResult["USER_CHECKWORD"]?>" class="bx-auth-input custom-input" autocomplete="off" />
				</label>
			</div>
		<?else:?>
			<div>
				<label>
					<?=GetMessage("AUTH_LOGIN")?><span class="starrequired">*</span>
					<input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" class="bx-auth-input custom-input" />
				</label>
			</div>
			<div>
				<label>
					<span class="starrequired">*</span><?=GetMessage("AUTH_CHECKWORD")?>
					<input type="text" name="USER_CHECKWORD" maxlength="50" value="<?=$arResult["USER_CHECKWORD"]?>" class="bx-auth-input custom-input" autocomplete="off" />
				</label>
			</div>
		<?endif?>
		<div>
			<label>
				<?=GetMessage("AUTH_NEW_PASSWORD_REQ")?><span class="starrequired">*</span>
				<input type="password" name="USER_PASSWORD" maxlength="50" value="<?=$arResult["USER_PASSWORD"]?>" class="bx-auth-input custom-input" autocomplete="off" />
				<?if($arResult["SECURE_AUTH"]):?>
					<span class="bx-auth-secure" id="bx_auth_secure" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
						<div class="bx-auth-secure-icon"></div>
					</span>
					<noscript>
					<span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
						<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
					</span>
					</noscript>
					<script type="text/javascript">
					document.getElementById('bx_auth_secure').style.display = 'inline-block';
					</script>
				<?endif?>
			</label>
		</div>
		<div>
			<label>
				<?=GetMessage("AUTH_NEW_PASSWORD_CONFIRM")?><span class="starrequired">*</span>
				<input type="password" name="USER_CONFIRM_PASSWORD" maxlength="50" value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>" class="bx-auth-input custom-input" autocomplete="off" />
			</label>
		</div>
		<?if($arResult["USE_CAPTCHA"]):?>
			<tr>
				
				
					<input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
					<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
				
			</tr>
			<tr>
				<span class="starrequired">*</span><?echo GetMessage("system_auth_captcha")?>
				<input type="text" name="captcha_word" maxlength="50" value="" />
			</tr>
		<?endif?>
		<div class="btnBox">				
			<input type="submit" name="change_pwd" value="<?=GetMessage("AUTH_CHANGE")?>" class="custom-submit"/>
		</div>
	</table>
</form>

<p><?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?></p>
<p><span class="starrequired">*</span><?=GetMessage("AUTH_REQ")?></p>

<?if($arResult["PHONE_REGISTRATION"]):?>

<script type="text/javascript">
new BX.PhoneAuth({
	containerId: 'bx_chpass_resend',
	errorContainerId: 'bx_chpass_error',
	interval: <?=$arResult["PHONE_CODE_RESEND_INTERVAL"]?>,
	data:
		<?=CUtil::PhpToJSObject([
			'signedData' => $arResult["SIGNED_DATA"]
		])?>,
	onError:
		function(response)
		{
			var errorDiv = BX('bx_chpass_error');
			var errorNode = BX.findChildByClassName(errorDiv, 'errortext');
			errorNode.innerHTML = '';
			for(var i = 0; i < response.errors.length; i++)
			{
				errorNode.innerHTML = errorNode.innerHTML + BX.util.htmlspecialchars(response.errors[i].message) + '<br>';
			}
			errorDiv.style.display = '';
		}
});
</script>

<div id="bx_chpass_error" style="display:none"><?ShowError("error")?></div>

<div id="bx_chpass_resend"></div>

<?endif?>

<?endif?>
<br>
<a class="transitionBtn w200 logIn"><b><?=GetMessage("AUTH_AUTH")?></b></a>

</div>