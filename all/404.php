<?
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Страница не найдена");?>
<div class="page-404">
	<img src="<?=SITE_DIR?>images/404-img.png" alt="404 image">
	<p class="name"><b>страница не найдена</b></p>
	<p class="desc">Возможно, Вы пытаетесь загрузить несуществующую или удалённую страницу. <br>
Вы можете <a href="" onclick="history.back(); return false;">вернуться назад</a> или на <a href="/">главную страницу</a></p>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>