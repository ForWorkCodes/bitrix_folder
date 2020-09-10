<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CModule::IncludeModule("iblock");

$dbBlockType = CIBlock::GetList(array('SORT' => 'ASC', 'ACTIVE' => "Y"));
$arBlockType = array();
while ($dbBlock = $dbBlockType->Fetch()) {
	$arBlockType[$dbBlock['ID']] = "[" . $dbBlock['ID'] . "] " . $dbBlock['NAME'];
}

$arComponentParameters = array(
	"NAME" => 'ASD',
	"TYPE" => "LIST"
);
$arTemplateParameters["FILTER_VIEW_MODE"] = array(
	"PARENT" => "FILTER_SETTINGS",
	"NAME" => GetMessage('CPT_BC_FILTER_VIEW_MODE'),
	"TYPE" => "LIST",
	"VALUES" => $arFilterViewModeList,
	"DEFAULT" => "VERTICAL",
	"HIDDEN" => (!isset($arCurrentValues['USE_FILTER']) || 'N' == $arCurrentValues['USE_FILTER'])
);