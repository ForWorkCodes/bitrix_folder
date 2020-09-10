<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponentTemplate $this
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?if (count($arResult["ERRORS"]) <= 0 && $arResult["USE_EMAIL_CONFIRMATION"] !== "Y" && count($arResult["VALUES"]) > 0):?>
	<div class="popUp__content">
		<h3><?echo GetMessage("MAIN_REGISTER_AUTH")?></h3>
	</div>
	<script>
		location.reload();	
	</script>
<?else:?>
	<?
	if (count($arResult["ERRORS"]) > 0):?>
		<div class="popUp__content">
			<?
			foreach ($arResult["ERRORS"] as $key => $error) {
				if (intval($key) == 0 && $key !== 0) {
					?>
					<script>
						$('input[name="REGISTER[<?=$key?>]"]').css('border', '2px solid red');
					</script>
					<?
					$arResult["ERRORS"][$key] = str_replace("#FIELD_NAME#", "&quot;".GetMessage("REGISTER_FIELD_".$key)."&quot;", $error);
				}else {
					?><p class="error"><?=$error ?></p><?
				}
			}
			?>
		</div>
		<?
	elseif($arResult["USE_EMAIL_CONFIRMATION"] === "Y"):?>
		<div class="popUp__content">
			<p><?echo GetMessage("REGISTER_EMAIL_WILL_BE_SENT")?></p>
		</div>
	<?endif?>

	<?if($arResult["SHOW_SMS_FIELD"] == false):?>
		<script>
			$('.phonemask').mask('+7 (999) 999 99 99');
		</script>
		<div class="popUp__content">
			<form method="post" action="<?=$arParams['ACTION_URL'] ?>" name="regform" enctype="multipart/form-data" id="reg-form">
				<?
				if($arResult["BACKURL"] <> ''):
				?>
					<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
				<?
				endif;
				?>

				<div class="popUp__form">
					<?foreach ($arResult["SHOW_FIELDS"] as $FIELD):?>
						<div class="popUp__block">
							<label class="custom-label"><?=GetMessage("REGISTER_FIELD_".$FIELD)?>:<?if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y"):?><span>*</span><?endif?></label>
							<?
							switch ($FIELD)
							{
								case "PASSWORD":
									?>
									<input size="30" type="password" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>" autocomplete="off" class="<?=($FIELD == 'PERSONAL_PHONE') ? 'phonemask ' : '' ?>custom-input" />
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
									<?
									break;
								case "CONFIRM_PASSWORD":
									?>
									<input size="30" type="password" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>" autocomplete="off" class="custom-input"/>
									<?
									break;
								default:
									?>
									<input size="30" type="text" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>"  class="<?=($FIELD == 'PERSONAL_PHONE') ? 'phonemask ' : '' ?>custom-input"/>
									<?
										
							}?>
						</div>
					<?endforeach?>
				</div>
				<div class="formSolve">
					<input type="text" class="phone2" name="name" value="<?=$_POST['name'] ?>">
					<input class="customCheckbox" type="checkbox" id="de3asd" name="ok" checked value="Y">
					<label class="customLabel" for="de3asd">
						<div>
							Подтверждаю своё согласие с <a class="blue-to-green" href="/policy/">Политикой обработки персональных данных</a> и <a class="blue-to-green" href="/agreement/">Пользовательским соглашением</a>.
						</div>
					</label>
				</div>
				<p>Поля, обязательные для заполнения, помечены символом <span>*</span></p>
				<div class="formBtn">
					<input type="hidden" name="register_submit_button" value="Y">
					<input class="greenCustomButton" type="submit" name="submit" value="Зарегистрироваться">		
				</div>
			</form>
		</div>
		<div class="popUp__end">
			<a class="allCateg__link" href="">
				войти
				<div class="allCateg__arr">
					<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/img/icons/svg/drop.html', array(), array())?>
				</div>
			</a>
		</div>

	<?endif //$arResult["SHOW_SMS_FIELD"] == false ?>

<?endif?>