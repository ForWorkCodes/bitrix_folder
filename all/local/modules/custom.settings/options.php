<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

Loader::includeModule($module_id);
Loader::includeModule('Currency');
Loader::includeModule('catalog');
Loader::includeModule('sale');

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);
$catalog_id = Option::get($module_id, "Iblock_catalog");

/* Список инфоблоков */
$obIblocks = CIBlock::GetList();
$arIblocks[] = [];
while ($iblock = $obIblocks->GetNext())
{
    $arIblocks[$iblock['ID']] = $iblock['NAME'];
}
/* -Список инфоблоков- */

/* Загрузка списка платежных систем */
$obPayList = CSalePaySystem::GetList($arOrder = ["SORT"=>"ASC", "PSA_NAME"=>"ASC"], ["LID"=>SITE_ID, "ACTIVE"=>"Y"]);
while ($arPay = $obPayList->Fetch())
{
    $arPayList[$arPay['ID']] = $arPay['NAME'];
}
/* -Загрузка списка платежных систем- */

/* Загрузка списка доставок */
$obDelList = \Bitrix\Sale\Delivery\Services\Table::getList(array(
    'select'=>['ID','NAME'],
    'filter' => ['ACTIVE' => 'Y']
));
while ($arDel = $obDelList->fetch())
{
    $arDelList[$arDel['ID']] = $arDel['NAME'];
}
/* -Загрузка списка доставок- */

/* Загрузка списка цен */
$i = 0;
$obTypePriceList = CCatalogGroup::GetList();
while ($arTypePrice = $obTypePriceList->GetNext())
{
    $arTypePriceList[] = $arTypePrice;
}

if ($arTypePriceList)
{
    foreach ($arTypePriceList as $arPrice)
    {
        if ($i === 0)
        {
            $defaultPrice = $arPrice['ID'];
            $i++;
        }
        $arPriceOptions[$arPrice['ID']] = $arPrice['NAME_LANG'];
    }
}
/* -Загрузка списка цен- */


/* Список групп пользователей */
$obGroupList = CGroup::GetList($by = "c_sort", $order = "asc");
while ($arGroup = $obGroupList->GetNext())
{
    $arGroups[$arGroup['ID']] = $arGroup['NAME'];
}
/* -Список групп пользователей- */

$aTabs[] = [
    "DIV" => "main_tab",
    "TAB" => Loc::getMessage("main_tab"),
    "TITLE" => Loc::getMessage("main_tab"),
    "OPTIONS" => array(
        [
            "catalog_id",
            Loc::getMessage("catalog_id"),
            "",
            array("selectbox", $arIblocks)
        ],
        [
            "phone_header",
            Loc::getMessage("phone_header"),
            "",
            array("text")
        ],
        [
            "iblock_list",
            Loc::getMessage("iblock_list"),
            "",
            array("multiselectbox", $arIblocks)
        ],
    )
];
?>