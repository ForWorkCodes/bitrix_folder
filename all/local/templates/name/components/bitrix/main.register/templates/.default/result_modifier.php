<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
foreach ($arResult['SHOW_FIELDS'] as $key => $arItem) {
	if ($arItem == 'PASSWORD') {
		unset($arResult['SHOW_FIELDS'][$key]);
	}
	if ($arItem == 'CONFIRM_PASSWORD') {
		unset($arResult['SHOW_FIELDS'][$key]);
	}
}
$arResult['SHOW_FIELDS'][] = 'PASSWORD';
$arResult['SHOW_FIELDS'][] = 'CONFIRM_PASSWORD';