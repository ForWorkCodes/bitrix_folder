<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);
$catalog_id = Option::get($module_id, "Iblock_catalog");
$catalog_offer_id = Option::get($module_id, "Iblock_catalog_offer");
$header_type_spec_id = Option::get($module_id, "header_type_spec_id");

$id_main_iblock = Option::get($module_id, "iblock_main_iblock");
$id_main_iblock_a = Option::get($module_id, "iblock_main_iblock_a");

Loader::includeModule($module_id);
Loader::includeModule('Currency');
Loader::includeModule('catalog');
Loader::includeModule('sale');

for ($i=1; $i < 9; $i++) { 
  $subs_count_limit[$i] = $i;
}

if ($catalog_id)
{
  $arListCatalogSections[] = [];
  $obSection = CIBlockSection::GetList(
    [],
    ['IBLOCK_ID' => $catalog_id, 'ACTIVE' => 'Y', 'DEPTH_LEVEL' => '1'],
    false,
    [],
    []
  );
  while ($arSection = $obSection->GetNext())
  {
    $arListCatalogSections[$arSection['ID']] = $arSection['NAME'];
  }
}

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

/* Загрузка списка цен */
$i = 0;
if ($obTypePriceList = CCatalogGroup::GetList())
{

}
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

/* Загрузка разделов главной страницы незарегистрированные */
if ($id_main_iblock)
{
  $arSectionMainPage[] = [];
  $obSectionsMainPage = CIBlockSection::GetList(
    [],
    ['IBLOCK_ID' => $id_main_iblock],
    false,
    false,
    []
  );
  while ($arSectionsMainPage = $obSectionsMainPage->GetNext())
  {
   $arSectionMainPage[$arSectionsMainPage['ID']] = $arSectionsMainPage['NAME'];
  }
}
/* -Загрузка разделов главной страницы незарегистрированные- */

/* Загрузка разделов главной страницы зарегистрированные */
if ($id_main_iblock_a)
{
  $arSectionMainPage_a[] = [];
  $obSectionsMainPage_a = CIBlockSection::GetList(
    [],
    ['IBLOCK_ID' => $id_main_iblock_a],
    false,
    false,
    []
  );
  while ($arSectionsMainPage_a = $obSectionsMainPage_a->GetNext())
  {
   $arSectionMainPage_a[$arSectionsMainPage_a['ID']] = $arSectionsMainPage_a['NAME'];
  }
}
/* -Загрузка разделов главной страницы зарегистрированные- */

/* Массив для вариантов отображения специального предложения в шапке */
if ($header_type_spec_id)
{
  $arSpecOptions[] = [];
  $obHeader_type_spec = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => $header_type_spec_id, 'ACTIVE' => 'Y'],
    false,
    false,
    []
  );
  $arSpecOptions[] = '';
  while ($arHeader_type_spec = $obHeader_type_spec->GetNext())
  {
    $arSpecOptions[$arHeader_type_spec['ID']] = $arHeader_type_spec['NAME'];
  }
}
/* -Массив для вариантов отображения специального предложения в шапке- */
$tab1 = [
  "DIV" => "edit",
  "TAB" => Loc::getMessage("OPTIONS_TAB_NAME"),
  "TITLE" => Loc::getMessage("OPTIONS_TAB_NAME"),
  "OPTIONS" => array(
      Loc::getMessage("OPTIONS_COMMON"), // Общие
      array(
          "Iblock_catalog",
          Loc::getMessage("OPTIONS_IBLOCK_CATALOG"),
          "5",
          array("selectbox", $arIblocks)
      ),
      array(
          "Iblock_notes",
          Loc::getMessage("IBLOCK_NOTES"),
          "",
          array("selectbox", $arIblocks)
      ),
      array(
          "Iblock_cart_month",
          Loc::getMessage("IBLOCK_CART_MONTH"),
          "17",
          array("selectbox", $arIblocks)
      ),
      array(
          "currency",
          Loc::getMessage("OPTIONS_CURRENCY"),
          $defaultCurrency,
          array("selectbox", $arCurrencyOptions)
      ),
      // array(
      //     "switch_on",
      //     Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_SWITCH_ON"),
      //     "Y",
      //     array("checkbox")
      // ),
      array(
          "link_main_wom",
          Loc::getMessage("LINK_MAIN_WOM"),
          '/main/for-her/',
          array("text")
      ),
      array(
          "link_main_men",
          Loc::getMessage("LINK_MAIN_MEN"),
          '/main/for-him/',
          array("text")
      ),
      array(
          "reg_name",
          Loc::getMessage("REG_NAME"),
          'Юнисент',
          array("text")
      ),
      array(
          "reg_last_name",
          Loc::getMessage("REG_LAST_NAME"),
          'Пользователь',
          array("text")
      ),
      // array(
      //     "catalog_text",
      //     Loc::getMessage("CATALOG_TEXT"),
      //     '',
      //     array("text")
      // ),
      array(
          "catalog_adv",
          Loc::getMessage("CATALOG_ADV"),
          '2150',
          array("selectbox", $arSpecOptions)
      ),
      array(
          "profile_adv",
          Loc::getMessage("PROFILE_ADV"),
          '2149',
          array("selectbox", $arSpecOptions)
      ),
      Loc::getMessage("PRICE_SETTINGS"),
      array(
        "price_buy",
        Loc::getMessage("PRICE_ONE_BUY"),
        $defaultPrice,
        array("multiselectbox", $arPriceOptions)
      ),
      // array(
      //     "payment",
      //     Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_PAY"),
      //     "",
      //     array("text")
      // ),
      // array(
      //     "prop_phone",
      //     Loc::getMessage("PLASTILIN_PROP_PHONE"),
      //     "",
      //     array("text")
      // ),
      // array(
      //     "id_user",
      //     Loc::getMessage("PLASTILIN_ID_USER"),
      //     "",
      //     array("text")
      // ),
      // array(
      //     "description",
      //     Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_DESCRIPTION"),
      //     Loc::getMessage("FALBAR_TOTOP_OPTIONS_TAB_DESCRIPTION_TEXT"),
      //     array("text")
      // )
  )
];
$tab2 = [
    "DIV" => "main_pages_tab",
    "TAB" => Loc::getMessage("MAIN_PAGES_TAB"),
    "TITLE" => Loc::getMessage("MAIN_PAGES_TAB"),
    "OPTIONS" => array(
        Loc::getMessage("OPTIONS_MAIN_PAGE"), // Главная незарегистрированные
        array(
            "iblock_main_iblock",
            Loc::getMessage("OPTIONS_MAIN_IBLOCK"),
            '7',
            array("selectbox", $arIblocks)
        ),
        array(
            "iblock_main_block_1",
            Loc::getMessage("OPTIONS_MAIN_BLOCK_1"),
            '1',
            array("selectbox", $arSectionMainPage)
        ),
        array(
            "iblock_main_block_2",
            Loc::getMessage("OPTIONS_MAIN_BLOCK_2"),
            '2',
            array("selectbox", $arSectionMainPage)
        ),
        array(
            "iblock_main_block_3",
            Loc::getMessage("OPTIONS_MAIN_BLOCK_3"),
            '3',
            array("selectbox", $arSectionMainPage)
        ),
        array(
            "iblock_main_block_4",
            Loc::getMessage("OPTIONS_MAIN_BLOCK_4"),
            '4',
            array("selectbox", $arSectionMainPage)
        ),
        array(
            "iblock_main_block_5",
            Loc::getMessage("OPTIONS_MAIN_BLOCK_5"),
            '5',
            array("selectbox", $arSectionMainPage)
        ),
        array(
            "iblock_main_block_6",
            Loc::getMessage("OPTIONS_MAIN_BLOCK_6"),
            '6',
            array("selectbox", $arSectionMainPage)
        ),
        array(
            "iblock_main_block_7",
            Loc::getMessage("OPTIONS_MAIN_BLOCK_7"),
            '7',
            array("selectbox", $arSectionMainPage)
        ),
        array(
            "id_element_unreg_items",
            Loc::getMessage("ID_ELEMENT_UNREG_ITEMS"),
            '2020',
            array("text")
        ),
        Loc::getMessage("OPTIONS_MAIN_PAGE_A"), // Главная зарегистрированные
        array(
            "iblock_main_iblock_a",
            Loc::getMessage("OPTIONS_MAIN_IBLOCK_A"),
            '10',
            array("selectbox", $arIblocks)
        ),
        array(
            "iblock_main_block_1_a",
            Loc::getMessage("OPTIONS_MAIN_BLOCK_1_A"),
            '8',
            array("selectbox", $arSectionMainPage_a)
        ),
        array(
            "iblock_main_block_new",
            Loc::getMessage("OPTIONS_MAIN_BLOCK_NEW"),
            '125',
            array("selectbox", $arListCatalogSections)
        ),
        array(
            "iblock_main_block_best",
            Loc::getMessage("OPTIONS_MAIN_BLOCK_BEST"),
            '122',
            array("selectbox", $arListCatalogSections)
        ),
        array(
            "iblock_main_block_more",
            Loc::getMessage("OPTIONS_MAIN_BLOCK_MORE"),
            '',
            array("multiselectbox", $arListCatalogSections)
        ),
    )
];
$tab3 = [
  "DIV" => "headers_tab",
  "TAB" => Loc::getMessage("HEADERS_TAB"),
  "TITLE" => Loc::getMessage("HEADERS_TAB"),
  "OPTIONS" => array(
    Loc::getMessage("OPTIONS_HEADER"), // Шапка
    array(
        "header_type_spec_id",
        Loc::getMessage("OPTIONS_HEADER_TYPE_SPEC_IBLOCK"),
        '9',
        array("selectbox", $arIblocks)
    ),
    array(
        "header_type_spec",
        Loc::getMessage("OPTIONS_HEADER_TYPE_SPEC"),
        '489',
        array("selectbox", $arSpecOptions)
    ),
    array(
        "header_type_spec_a",
        Loc::getMessage("OPTIONS_HEADER_TYPE_SPEC_A"),
        '493',
        array("selectbox", $arSpecOptions)
    ),
    array(
        "header_buy_text",
        Loc::getMessage("OPTIONS_HEADER_TYPE_BUY_TEXT"),
        'Подписаться за $14.95 / в месяц',
        array("text")
    ),
    array(
        "header_insta_text",
        Loc::getMessage("OPTIONS_HEADER_TYPE_INSTA_TEXT"),
        'Присоединяйтесь к нашему сообществу в Instagram, в котором   114 000 подписчиков',
        array("text")
    ),
    Loc::getMessage("PERSONAL_POSITION"), // Персональное меню в шапке
    array(
        "personal_menu_first",
        Loc::getMessage("PERSONAL_POSITION_1"),
        '',
        array("text")
    ),
    array(
        "personal_menu_second",
        Loc::getMessage("PERSONAL_POSITION_2"),
        '',
        array("text")
    ),
  )
];
$tab4 = [
  "DIV" => "social_tab",
  "TAB" => Loc::getMessage("SOCIAL_TAB"),
  "TITLE" => Loc::getMessage("SOCIAL_TAB"),
  "OPTIONS" => array(
    Loc::getMessage("CONTACTS_FOOTER"), // Контакты/Подвал
    array(
        "social_vk",
        Loc::getMessage("OPTIONS_SOCIAL_VK"),
        "",
        array("text")
    ),
    array(
        "social_od",
        Loc::getMessage("OPTIONS_SOCIAL_OD"),
        "",
        array("text")
    ),
    array(
        "social_in",
        Loc::getMessage("OPTIONS_SOCIAL_IN"),
        "",
        array("text")
    ),
    array(
        "social_fa",
        Loc::getMessage("OPTIONS_SOCIAL_FA"),
        "",
        array("text")
    ),
    array(
        "social_appstore",
        Loc::getMessage("OPTIONS_SOCIAL_APPSTORE"),
        "",
        array("text")
    ),
    array(
        "social_google",
        Loc::getMessage("OPTIONS_SOCIAL_GOOGLE"),
        "",
        array("text")
    ),
  )
];
$tab5 = [
  "DIV" => "turn_tab",
  "TAB" => Loc::getMessage("TURN_TAB"),
  "TITLE" => Loc::getMessage("TURN_TAB"),
  "OPTIONS" => array(
    Loc::getMessage("OPTIONS_COMMON"), // Контакты/Подвал
    array(
      "turn_order",
      Loc::getMessage("TURN_ORDER"),
      '14',
      array("selectbox", $arIblocks)
    ),
    array(
      "turn_before_date",
      Loc::getMessage("TURN_BEFORE_DATE"),
      '1',
      array("selectbox", ["0" => "В тот же день", "1" => "1", "2" => "2", "3" => "3"])
    ),
    array(
      "turn_order_link",
      Loc::getMessage("TURN_ORDER_LINK"),
      '/catalog/',
      array("text")
    ),
    array(
      "turn_order_change_link",
      Loc::getMessage("TURN_ORDER_CHANGE_LINK"),
      '/upcoming-deliveries/',
      array("text")
    ),
    Loc::getMessage("TURN_HEADER_TITLE"),
    array(
      "turn_header_img",
      Loc::getMessage("TURN_HEADER_IMG"),
      '/local/templates/.default/img/icon.png',
      array("text")
    ),
    array(
      "turn_header_text",
      Loc::getMessage("TURN_HEADER_TEXT"),
      'Получите ежемесячный запас аромата по вашему выбору',
      array("text")
    ),
    array(
      "turn_header_btn_text",
      Loc::getMessage("TURN_HEADER_BTN_TEXT"),
      'Обновите свой план',
      array("text")
    ),
    array(
      "turn_header_btn_link",
      Loc::getMessage("TURN_HEADER_BTN_LINK"),
      '/catalog/',
      array("text")
    ),
  )
];
$tab6 = [
  "DIV" => "subs_tab",
  "TAB" => Loc::getMessage("SUBS_TAB"),
  "TITLE" => Loc::getMessage("SUBS_TAB"),
  "OPTIONS" => array(
    Loc::getMessage("PRICE_SETTINGS"),
    array(
      "price_turn",
      Loc::getMessage("PRICE_TURN"),
      '3',
      array("multiselectbox", $arPriceOptions)
    ),
    Loc::getMessage("OPTIONS_COMMON"),
    array(
      "subs_desc",
      Loc::getMessage("SUBS_DESC"),
      'Подписка',
      array("text")
    ),
    array(
      "subs_payment",
      Loc::getMessage("SUBS_PAYMENT"),
      '1',
      array("selectbox", $arPayList)
    ),
    array(
      "subs_delivery",
      Loc::getMessage("SUBS_DELIVERY"),
      '1',
      array("selectbox", $arDelList)
    ),
    array(
      "subs_count_limit",
      Loc::getMessage("SUBS_COUNT_LIMIT"),
      '7',
      array("selectbox", $subs_count_limit)
    ),
    array(
      "subs_count_day",
      Loc::getMessage("SUBS_COUNT_DAY"),
      '3',
      array("selectbox", $subs_count_limit)
    ),
    array(
      "subs_text_new_user",
      Loc::getMessage("SUBS_TEXT_NEW_USER"),
      Loc::getMessage("SUBS_TEXT_NEW_USER_DESC"),
      array("text")
    ),
    Loc::getMessage("OPTIONS_SYS"),
    array(
      "subs_iblock_id",
      Loc::getMessage("SUBS_IBLOCK_ID"),
      '18',
      array("selectbox", $arIblocks)
    ),
    array(
      "subs_empty_id",
      Loc::getMessage("SUBS_EMPTY_ID"),
      '1621',
      array("text")
    ),
  )
];
$aTabs = array(
    $tab1,
    $tab2,
    $tab3,
    $tab4,
    $tab5,
    $tab6
);

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
      if ($aTab['DIV'] == 'turn_tab')
      {
        ?>
        <tr>
          <td width="50%" class="adm-detail-content-cell-l"><br></td>
          <td width="50%" class="adm-detail-content-cell-r">
            <a href="/bitrix/admin/iblock_list_admin.php?IBLOCK_ID=18&type=subscriptions&lang=ru&find_section_section=0&SECTION_ID=0&apply_filter=Y" target="_blank">Ссылка на редактирование подписок</a>
          </td>
        </tr>
        <?
      }
   }

   $tabControl->Buttons();
  ?>

  <input type="submit" name="apply" value="<? echo(Loc::GetMessage("OPTIONS_TAB_SAVE")); ?>" class="adm-btn-save" />
  <input type="submit" name="default" value="<? echo(Loc::GetMessage("OPTIONS_TAB_DEFAULT")); ?>" />

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
<style>
  select[multiple] {
    height: 300px !important;
  }
  select option[selected] {
    padding: 8px 5px
  }
</style>