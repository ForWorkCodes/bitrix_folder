<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var customOrderComponent $component */
?>
<div class="container-uni">
	<br>
	<br>
	<form method="POST" id="ajax_order" style="display: flex;">
		<div style="width: 50%;">
			<? foreach ($arResult['PROPERTIES'] as $arProp): ?>
				<? if ($arProp['CODE'] == 'EMAIL') continue; ?>
				<input type="text" name="<?=$arProp['CODE'] ?>" placeholder="<?=$arProp['NAME'] ?>" value="<?=$arProp['VALUE'][0] ?>"><br><br>
			<? endforeach ?>
			<h2>PAY</h2>
			<? foreach ($arResult['PAY_SYSTEMS'] as $arPay): ?>
				<label>
					<span><?=$arPay['NAME'] ?></span>
					<input class="push_order" type="radio" name="payment" value="<?=$arPay['ID'] ?>" <?=($arResult['SELECTED']['PAYMENT'] == $arPay['ID']) ? 'checked' : '' ?>>
					<br>
					<br>
				</label>
			<? endforeach ?>
			<h2>DEL</h2>
			<? foreach ($arResult['DELIVERIES'] as $arDel): ?>
				<label>
					<span><?=$arDel['NAME'] ?></span>
					<input class="push_order" type="radio" name="delivery" value="<?=$arDel['ID'] ?>" <?=($arResult['SELECTED']['DELIVERY'] == $arDel['ID']) ? 'checked' : '' ?> >
					<br>
					<br>
				</label>
			<? endforeach ?>

			<input type="hidden" name="is_ajax" value="N" id="ajax_on">
			<input type="submit" name="submit" value="push">
		</div>
		<div style="width: 50%">
			<? prr($arResult['ORDER']) ?>
		</div>
	</form>
</div>