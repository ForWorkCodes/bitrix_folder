<?
namespace plastilin\settings;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

class MainPlastilin
{

    public function GetSections($idParent = '', $iblock = '', $show_count = '')
    {
        if (empty($idParent) && empty($iblock)) return;
        
    	Loader::includeModule('iblock');
        $arFilter = [];
        $arFilter['ACTIVE'] = 'Y';
        $arNav['nTopCount'] = '10000';

        if ($idParent)
        {
            $arFilter['SECTION_ID'] = $idParent;
        }
        if ($iblock)
        {
            $arFilter['IBLOCK_ID'] = $iblock;
        }
        if ($show_count)
        {
            $arNav['nTopCount'] = $show_count;
        }

        $obSection = new \CIBlockSection();
        $obSections = $obSection->GetList(
            [],
            $arFilter,
            false,
            ['*', 'UF_*'],
            $arNav
        );
        while ($arSection = $obSections->GetNext())
        {
            $result[$arSection['ID']] = $arSection;
        }
        return $result;
    }

    public function GetElements($idParent = '', $iblock = '')
    {
        if (empty($idParent) && empty($iblock)) return;
        Loader::includeModule('iblock');
        $arFilter = [];
        $arFilter['ACTIVE'] = 'Y';

        if ($idParent)
        {
            $arFilter['SECTION_ID'] = $idParent;
        }
        if ($iblock)
        {
            $arFilter['IBLOCK_ID'] = $iblock;
        }

        $obElement = new \CIBlockElement();
        $obElements = $obElement->GetList(
            [],
            $arFilter,
            false,
            ["nTopCount" => 99999],
            []
        );
        while ($arElement = $obElements->GetNextElement())
        {
            $i++;
            $fields = $arElement->GetFields();
            $fields['PROPERTIES'] = $arElement->GetProperties();
            $result[$fields['ID']] = $fields;
        }
        return $result;
    }

    public function GetElement($id = '', $iblock = '', $id_section = '')
    {
        if (empty($id_section) && empty($iblock) && empty($id)) return;
        Loader::includeModule('iblock');
        $arFilter = [];
        $arFilter['ACTIVE'] = 'Y';

        if ($id)
        {
            $arFilter['ID'] = $id;
        }
        if ($iblock)
        {
            $arFilter['IBLOCK_ID'] = $iblock;
        }
        if ($id_section)
        {
            $arFilter['SECTION_ID'] = $id_section;
            $arFilter['INCLUDE_SUBSECTIONS'] = 'Y';
        }

        $obElement = new \CIBlockElement();
        $obElements = $obElement->GetList(
            [],
            $arFilter,
            false,
            ["nTopCount" => false],
            []
        );
        while ($arElement = $obElements->GetNextElement())
        {
            $fields = $arElement->GetFields();
            $fields['PROPERTIES'] = $arElement->GetProperties();
            $result = $fields;
        }
        return $result;
    }

    public function GetUserData($id_user, $arParam = [])
    {
        $filter = ['ID' => $id_user];
        $obUser = \CUser::GetList(($by = "NAME"), ($order = "desc"), $filter, $arParam);
        while ($fieldUser = $obUser->GetNext())
        {
            $fieldUsers = $fieldUser;
        }
        return $fieldUsers;
    }

    public function GetFullPriceItems($arId, $price_id)
    {
        if (!is_array($arId))
            $arIds[] = $arId;
        else
            $arIds = $arId;

        $arPrices = \Bitrix\Catalog\PriceTable::getList([
          "select" => ["*"],
          "filter" => [
               "PRODUCT_ID" => $arIds,
               "CATALOG_GROUP_ID" => $price_id
          ]
        ])->fetchAll();

        if ($arPrices)
        {
            global $USER;
            $currency = $this->GetCurrencyData();
            foreach ($arPrices as &$arPrice)
            {
                $arDiscounts =  \CCatalogDiscount::GetDiscountByPrice(
                    $arPrice['ID'],
                    $USER->GetUserGroupArray()
                );

                $discountPrice = \CCatalogProduct::CountPriceWithDiscount(
                   $arPrice["PRICE"],
                   $arPrice["CURRENCY"],
                   $arDiscounts
                );
                $arPrice['DISCOUNT_PRICE'] = $discountPrice;
                $arPrice['CURRENCY_TEXT'] = $currency;
                $returnPrices[$arPrice['PRODUCT_ID']] = $arPrice;
            }
            
        }

        return $returnPrices;
    }

    public function GetCurrencyData()
    {
        $currency = Option::get('plastilin.settings', "currency");

        if (!$currency) return;
        $lcur = \CCurrency::GetList(($by="name"), ($order="asc"), LANGUAGE_ID);
        while($lcur_res = $lcur->Fetch())
        {
            if ($lcur_res["CURRENCY"] == $currency)
            {
                $currencyText = str_replace('# ', '', $lcur_res["FORMAT_STRING"]);
            }
        }

        return $currencyText;
    }

    public function GetPercentMarkup($id)
    {
        $price_id = Option::get('plastilin.settings', "price_turn");

        $arPrice = $this->GetFullPriceItems($id, $price_id)[$id];
        $price_subs_for_one_cart = $this->GetPriceForOne();

        $result = (int)( ((float)$arPrice['DISCOUNT_PRICE'] / (float)$price_subs_for_one_cart) * 100 ) - 100;

        return $result;
    }

    public function GetPercentMarkups($ids)
    {
        $price_id = Option::get('plastilin.settings', "price_turn");

        $arPrices = $this->GetFullPriceItems($ids, $price_id);
        $price_subs_for_one_cart = $this->GetPriceForOne();
        if (count($arPrices) > 0)
        {
            foreach ($arPrices as $arPrice)
            {
				$price = (int)( ((float)$arPrice['DISCOUNT_PRICE'] / (float)$price_subs_for_one_cart) * 100 ) - 100;
                $result[$arPrice['PRODUCT_ID']] = $price;
            }
        }

        return $result;
    }

    public function GetPriceDifference($ids)
    {
        $price_id = Option::get('plastilin.settings', "price_turn");

        $arPrices = $this->GetFullPriceItems($ids, $price_id);
        $price_subs_for_one_cart = $this->GetPriceForOne();
        if (count($arPrices) > 0)
        {
            foreach ($arPrices as $arPrice)
            {
    			if (1==2)
    			{
    				/* Раньше отдавал процент, теперь будет разница */
    				$price = (int)( ((float)$arPrice['DISCOUNT_PRICE'] / (float)$price_subs_for_one_cart) * 100 ) - 100;
	                $result[$arPrice['PRODUCT_ID']] = $price;
    			}
    			$price = (float)$arPrice['DISCOUNT_PRICE'] - (float)$price_subs_for_one_cart;
	            $result[$arPrice['PRODUCT_ID']] = $price;

            }
        }

        return $result;
    }

    public function GetPriceForOne()
    {
        $obElements = \CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => Option::get('plastilin.settings', "subs_iblock_id"), 'ACTIVE' => 'Y', '!ID' => Option::get('plastilin.settings', "subs_empty_id")],
            false,
            ['nTopCount' => 1],
            []
        );
        while ($obElement = $obElements->GetNextElement())
        {
            $prop = $obElement->GetProperties();
            $price_for_one = $prop['PRICE_FOR_ONE']['VALUE'];
        }

        if (1==2)
        {
            global $USER;
            $arFilter = array("ID" => $USER->GetID());
            $arParams["SELECT"] = array("*", "UF_SUBSCRIBE");

            $obRes = \CUser::GetList($by,$desc,$arFilter, $arParams);
            while ($arRes = $obRes->Fetch())
            {
                $subsID = $arRes['UF_SUBSCRIBE'];
                $obElement = \CIBlockElement::GetList(
                    [],
                    ['ID' => $subsID],
                    false,
                    false,
                    []
                );
                while ($arElement = $obElement->GetNextElement())
                {
                    $prop = $arElement->GetProperties();
                    $price_for_one = $prop['PRICE_FOR_ONE']['VALUE'];
                }
            }
        }

        return (float)$price_for_one;
    }
}
?>