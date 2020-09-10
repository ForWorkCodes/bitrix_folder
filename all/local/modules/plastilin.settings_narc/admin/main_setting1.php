<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
if($_SERVER['REQUEST_METHOD'] === 'GET'){
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\HttpApplication;
Loc::loadMessages(__FILE__);
$request = HttpApplication::getInstance()->getContext()->getRequest();

if($_SERVER['REQUEST_METHOD'] === 'GET'){
	$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage("TITLE_PAGE"));
}

$moduleID = 'plastilin.settings';

$RIGHT = $GLOBALS['APPLICATION']->GetGroupRight($moduleID);
if($RIGHT >= 'R') {
	if(CModule::IncludeModule($moduleID)) {

		?>
		<form action="">
			<label>
				<p>Описание сайта</p>
				<input type="text" name="description">
			</label>
		</form>
		<?
	}
}

if($_SERVER['REQUEST_METHOD'] === 'GET'){
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
}
?>