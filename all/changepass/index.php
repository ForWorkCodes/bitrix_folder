<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Изменить пароль");?>
<main>
	<? if ( $USER->isAuthorized() ): ?>
		<? header('Location: /'); ?>
	<? elseif ($_GET['change_password'] != 'yes' || $_GET['USER_CHECKWORD'] == '' || $_GET['USER_LOGIN'] == ''): ?>
		<? header('Location: /'); ?>
	<? endif ?>
	<div class="container">
		<?$APPLICATION->IncludeComponent(
			"bitrix:breadcrumb",
			"",
			Array(
				"PATH" => "",
				"SITE_ID" => "s1",
				"START_FROM" => "0"
			)
		);?>
	</div>
	<div class="container">
		<h1><?=$APPLICATION->ShowTitle() ?></h1>
		<?if ($_GET['change_password'] == 'yes' && $_GET['USER_CHECKWORD'] != '' && $_GET['USER_LOGIN'] != '') :?>
			<?$APPLICATION->IncludeComponent(
				"bitrix:system.auth.changepasswd",
				"bank",
				Array()
			);?>
		<?endif?>
	</div>
</main>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>