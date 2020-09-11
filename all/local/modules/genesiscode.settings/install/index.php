<?
Class genesiscode_settings extends CModule
{
	var $MODULE_ID = "genesiscode_settings";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	function genesiscode_settings()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = "genesiscode_settings";
		$this->MODULE_DESCRIPTION = "Описание";
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/genesiscode_settings/install/components",
		             $_SERVER["DOCUMENT_ROOT"]."/local/components", true, true);
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFilesEx("/local/components/special.custom");
		return true;
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->InstallFiles();
		RegisterModule("genesiscode_settings");
		$APPLICATION->IncludeAdminFile("Установка модуля genesiscode_settings", $DOCUMENT_ROOT."/local/modules/genesiscode_settings/install/step.php");
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->UnInstallFiles();
		UnRegisterModule("genesiscode_settings");
		$APPLICATION->IncludeAdminFile("Деинсталляция модуля genesiscode_settings", $DOCUMENT_ROOT."/local/modules/genesiscode_settings/install/unstep.php");
	}
}
?>