<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?$APPLICATION->showTitle();?></title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?
	use \Bitrix\Main\Page\Asset;
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/');

    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/');
	$APPLICATION->ShowHead(); ?>
</head>
<body>
<? $APPLICATION->ShowPanel(); ?>
<header>

</header>