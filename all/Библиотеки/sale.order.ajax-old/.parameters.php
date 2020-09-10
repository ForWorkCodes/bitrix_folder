<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CModule::IncludeModule("iblock");

$dbBlockType = CIBlock::GetList(array('SORT' => 'ASC', 'ACTIVE' => "Y"));
$arBlockType = array();
while ($dbBlock = $dbBlockType->Fetch()) {
	$arBlockType[$dbBlock['ID']] = "[" . $dbBlock['ID'] . "] " . $dbBlock['NAME'];
}

$arComponentParameters = array(
	
);