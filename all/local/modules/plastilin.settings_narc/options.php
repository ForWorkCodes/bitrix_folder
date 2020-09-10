<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

Loader::includeModule($module_id);
Loader::includeModule('Currency');
Loader::includeModule('sale');

/* Список инфоблоков */
$obIblocks = CIBlock::GetList();
$arIblocks[] = [];
while ($iblock = $obIblocks->GetNext())
{
  $arIblocks[$iblock['ID']] = $iblock['NAME'];
}
/* -Список инфоблоков- */

/* Список групп пользователей */
$obGroupList = CGroup::GetList($by = "c_sort", $order = "asc");
while ($arGroup = $obGroupList->GetNext())
{
  $arGroups[$arGroup['ID']] = $arGroup['NAME'];
}
/* -Список групп пользователей- */

/* Загрузка списка цен */
$i = 0;
if ($obTypePriceList = CCatalogGroup::GetList())
{
  while ($arTypePrice = $obTypePriceList->GetNext())
  {
    $arTypePriceList[] = $arTypePrice;
  }
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

/* Загрузка списка валют */
$obCurrencyList = CCurrency::GetList(($by="name"), ($order="asc"), LANGUAGE_ID);

while ($arCurrency = $obCurrencyList->Fetch())
{
  $arCurrencyList[] = $arCurrency;
}
if ($arCurrencyList)
{
  $i = 0;
  foreach ($arCurrencyList as $arOption)
  {
    if ($i === 0) {
      $defaultCurrency = $arOption['CURRENCY'];
      $i++;
    }
    $arCurrencyOptions[$arOption['CURRENCY']] = $arOption['FULL_NAME'];
  }

}
/* -Загрузка списка валют- */

$aTabs[] = array(
  "DIV" => "edit",
  "TAB" => Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_NAME"),
  "TITLE" => Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_NAME"),
  "OPTIONS" => array(
      Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_COMMON"),
      array(
          "Iblock_catalog",
          Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_IBLOCK_CATALOG"),
          "",
          array("text")
      ),
      array(
          "Iblock_contact",
          Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_IBLOCK_CONTACT"),
          "",
          array("text")
      ),
      Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_SETTINGS_BUY"),
      array(
          "delivery",
          Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_DELIVERY"),
          "",
          array("text")
      ),
      array(
          "payment",
          Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_PAY"),
          "",
          array("text")
      ),
      array(
          "prop_phone",
          Loc::getMessage("PLASTILIN_PROP_PHONE"),
          "",
          array("text")
      ),
      array(
          "id_user",
          Loc::getMessage("PLASTILIN_ID_USER"),
          "",
          array("text")
      ),
      array(
          "currency",
          Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_CURRENCY"),
          $defaultCurrency,
          array("selectbox", $arCurrencyOptions)
      ),
      array(
          "description",
          Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_DESCRIPTION"),
          Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_DESCRIPTION_TEXT"),
          array("text")
      ),
      array(
          "text_fast_buy",
          Loc::getMessage("FAST_BUY"),
          Loc::getMessage("FAST_BUY_TEXT"),
          array("text")
      ),
      Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_SETTINGS_BUY"),
      array(
          "text_btn_buy",
          Loc::getMessage("BUY_BTN"),
          Loc::getMessage("BUY_BTN_TEXT"),
          array("text")
      ),
      array(
          "text_cant_buy",
          Loc::getMessage("CANT_BUY"),
          Loc::getMessage("CANT_BUY_TEXT"),
          array("text")
      ),
      [
        "price_id",
        Loc::getMessage("price_id"),
        "",
        array("selectbox", $arPriceOptions)
      ],
  )
);

$aTabs[] = [
  "DIV" => "constructor_tab",
  "TAB" => Loc::getMessage("CONSTRUCTOR_TAB"),
  "TITLE" => Loc::getMessage("CONSTRUCTOR_TAB"),
  "OPTIONS" => array(
    Loc::getMessage("CONSTRUCTOR_MAIN"),
    [
      "iblock_constructor",
      Loc::getMessage("iblock_constructor"),
      "",
      array("selectbox", $arIblocks)
    ],
    [
      "iblock_dealer",
      Loc::getMessage("iblock_dealer"),
      "",
      array("selectbox", $arIblocks)
    ],
    [
      "iblock_dealers_list",
      Loc::getMessage("iblock_dealers_list"),
      "",
      array("selectbox", $arIblocks)
    ],
    [
      "iblock_dealers_history",
      Loc::getMessage("iblock_dealers_history"),
      "",
      array("selectbox", $arIblocks)
    ],
    [
      "group_dealer",
      Loc::getMessage("group_dealer"),
      "",
      array("selectbox", $arGroups)
    ],
    [
      "group_manager",
      Loc::getMessage("group_manager"),
      "",
      array("selectbox", $arGroups)
    ],
  )
];

$aTabs[] = [
  "DIV" => "settings_tab",
  "TAB" => Loc::getMessage("settings_tab"),
  "TITLE" => Loc::getMessage("settings_tab"),
  "OPTIONS" => array(
    [
      "id_log",
      Loc::getMessage("id_log"),
      "",
      array("text")
    ],
  )
];

// $tab3 = [
//   "DIV" => "edit",
//   "TAB" => Loc::getMessage(""),
//   "TITLE" => Loc::getMessage(""),
//   "OPTIONS" => array(
//     Loc::getMessage(""),
//     [
//       "",
//       Loc::getMessage(""),
//       "",
//       array("text")
//     ],
//   )
// ];

$tabControl = new CAdminTabControl(
  "tabControl",
  $aTabs
);

$tabControl->Begin();
?>
<form action="<? echo($APPLICATION->GetCurPage()); ?>?mid=<? echo($module_id); ?>&lang=<? echo(LANG); ?>" method="post">

  <?
   foreach($aTabs as $aTab){

       if($aTab["OPTIONS"]){

         $tabControl->BeginNextTab();

         __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
      }
   }

   $tabControl->Buttons();
  ?>

  <input type="submit" name="apply" value="<? echo(Loc::GetMessage("FALBAR_TOTOP_OPTIONS_INPUT_APPLY")); ?>" class="adm-btn-save" />
  <input type="submit" name="default" value="<? echo(Loc::GetMessage("FALBAR_TOTOP_OPTIONS_INPUT_DEFAULT")); ?>" />

   <?
   echo(bitrix_sessid_post());
 ?>

</form>
<?
$tabControl->End();

if($request->isPost() && check_bitrix_sessid()){

    foreach($aTabs as $aTab){

       foreach($aTab["OPTIONS"] as $arOption){

           if(!is_array($arOption)){

               continue;
           }

           if($arOption["note"]){

                continue;
           }

           if($request["apply"]){

                $optionValue = $request->getPost($arOption[0]);

              if($arOption[0] == "switch_on"){

                  if($optionValue == ""){

                       $optionValue = "N";
                   }
               }

               Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
            }elseif($request["default"]){

             Option::set($module_id, $arOption[0], $arOption[2]);
            }
       }
   }

   LocalRedirect($APPLICATION->GetCurPage()."?mid=".$module_id."&lang=".LANG);
}
?>
