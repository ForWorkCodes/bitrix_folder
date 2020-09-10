<?
namespace plastilin\settings;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;

class MainDealer extends Center
{
	protected $GroupManager;
    protected $GroupDealer;
	protected $iblock_constructor;
    protected $cart_status_code;
    protected $iblock_dealer;
    protected $iblock_dealers_list;
    protected $iblock_dealers_history;

	/*
	Center => 
		$obSections;
	    $obElements;
	    $module_id;
	    $user_id;
        $HBIDLog;
        $catalog_id;
        $currency
        $LIST_ERROR
        $price_id
	*/

	function __construct()
	{
		parent::__construct();

		$this->GroupManager = Option::get($this->module_id, "group_manager");
        $this->GroupDealer = Option::get($this->module_id, "group_dealer");
		$this->iblock_constructor = Option::get($this->module_id, "iblock_constructor");
		$this->iblock_dealer = Option::get($this->module_id, "iblock_dealer");
		$this->iblock_dealers_list = Option::get($this->module_id, "iblock_dealers_list");
        $this->iblock_dealers_history = Option::get($this->module_id, "iblock_dealers_history");
        $this->cart_status_code = 'STATUS_CART';
	}

    public function ThisManager()
    {
        $isset = false;
        $arGroups = \CUser::GetUserGroup($this->user_id);
        if (is_array($arGroups) && count($arGroups) > 0)
        {
            foreach ($arGroups as $arGroup)
            {
                if ($arGroup == $this->GroupManager)
                {
                    $isset = true;
                }
            }
        }

        return $isset;
    }

    public function ThisDealer()
    {
        $isset = false;
        $arGroups = \CUser::GetUserGroup($this->user_id);
        if (is_array($arGroups) && count($arGroups) > 0)
        {
            foreach ($arGroups as $arGroup)
            {
                if ($arGroup == $this->GroupDealer)
                {
                    $isset = true;
                }
            }
        }

        return $isset;
    }

    public function CheckPermissionDealer()
    {
    	$isset = false;
    	$arGroups = \CUser::GetUserGroup($this->user_id);
    	if (is_array($arGroups) && count($arGroups) > 0)
    	{
	    	foreach ($arGroups as $arGroup)
	    	{
	    		if ($arGroup == $this->GroupDealer || $arGroup == 1 || $arGroup == $this->GroupManager)
	    		{
	    			$isset = true;
	    		}
	    	}
    	}

    	return $isset;
    }

    public function GetMainSection($id_user = '')
    {
    	$main_section = false;

        if (empty($id_user))
        {
            $id_user = $this->user_id;
        }

    	$obSections = $this->obSections->GetList(
            [],
            ['ACTIVE' => 'Y', 'IBLOCK_ID' => $this->iblock_dealer, 'UF_ID_USER' => $id_user, 'DEPTH_LEVEL' => 1],
            false,
            ['*', 'UF_*'],
            []
        );
        while ($arSection = $obSections->GetNext())
        {
        	$main_section = $arSection;
        }

        if (!$main_section)
        {
            $is_admin = false;
            $arGroups = \CUser::GetUserGroup($this->user_id); // Если это админ, ничего не пушить
            foreach ($arGroups as $arGroup)
            {
                if ($arGroup == 1)
                {
                    $is_admin = true;
                }
            }

            if (!$is_admin)
            {
                $text = Loc::getMessage("no_folder_dealer");
            	$log = [
            		'UF_ID_USER' => $this->user_id,
            		'UF_TEXT' => $text,
            		'UF_DATA_DEALER' => 'Y'
            	];
            	$this->PushToLog($log);
            }
        }

        return $main_section;
    }

    public function GetSectionWithBouquet($arParent = '')
    {
        $arSection_Bouquet = false;
        if (empty($arParent))
        {
            $getParent = $this->GetMainSection();
            if (empty($getParent)) return;
            $arParent = $getParent;
        }


        $obSection = $this->obSections->GetList(
            [],
            ['IBLOCK_ID' => $this->iblock_dealer, 'ACTIVE' => 'Y', 'SECTION_ID' => $arParent['ID'], 'UF_BOUQUET' => '1'],
            false,
            [],
            []
        );
        while ($arSection = $obSection->GetNext())
        {
            $arSection_Bouquet = $arSection;
        }

        if (!$arSection_Bouquet)
        {
            $is_admin = false;
            $arGroups = \CUser::GetUserGroup($this->user_id); // Если это админ, ничего не пушить
            foreach ($arGroups as $arGroup)
            {
                if ($arGroup == 1)
                {
                    $is_admin = true;
                }
            }

            if (!$is_admin)
            {
                $text = Loc::getMessage("no_bouquet_folder_dealer");
                $log = [
                    'UF_ID_USER' => $this->user_id,
                    'UF_TEXT' => $text,
                    'UF_DATA_DEALER' => 'Y'
                ];
                $this->PushToLog($log);
            }
        }

        return $arSection_Bouquet;
    }

    public function GetSectionWithProducts($arParent = '')
    {
        $section_product = false;
        if (empty($arParent))
        {
            $getParent = $this->GetMainSection();
            if (empty($getParent)) return;
            $arParent = $getParent;
        }


        $obSection = $this->obSections->GetList(
            [],
            ['IBLOCK_ID' => $this->iblock_dealer, 'ACTIVE' => 'Y', 'SECTION_ID' => $arParent['ID'], 'UF_PRODUCT' => '1'],
            false,
            [],
            []
        );
        while ($arSection = $obSection->GetNext())
        {
            $section_product = $arSection;
        }

        if (!$section_product)
        {
            $is_admin = false;
            $arGroups = \CUser::GetUserGroup($this->user_id); // Если это админ, ничего не пушить
            foreach ($arGroups as $arGroup)
            {
                if ($arGroup == 1)
                {
                    $is_admin = true;
                }
            }

            if (!$is_admin)
            {
                $text = Loc::getMessage("no_product_folder_dealer");
                $log = [
                    'UF_ID_USER' => $this->user_id,
                    'UF_TEXT' => $text,
                    'UF_DATA_DEALER' => 'Y'
                ];
                $this->PushToLog($log);
            }
        }

        return $section_product;
    }

    public function GetCartStatusCode()
    {
        return $this->cart_status_code;
    }

    public function GetIblockDealer()
    {
        return $this->iblock_dealer;
    }

    public function GetIblockDealerList()
    {
        return $this->iblock_dealers_list;
    }

    public function GetIblockConstructorId()
    {
        return $this->iblock_constructor;
    }

    public function CheckStatusDealerPrice()
    {

    }

    public function AddHistory($arData)
    {
        $obSection = $this->obSections->GetList(
            [],
            ['IBLOCK_ID' => $this->iblock_dealers_history, 'UF_ID_USER' => $this->user_id, 'ACTIVE' => 'Y'],
            false,
            ['UF_*', '*'],
            []
        );

        while ($arSection = $obSection->GetNext())
        {
            $id_section = $arSection['ID'];
        }


        if ($id_section)
        {
            if ($arData['NEW_ITEM'] != 'Y')
            {
                $obElements = $this->obElements->GetList(
                    [],
                    ['IBLOCK_ID' => $this->iblock_dealer, 'ACTIVE' => 'Y', 'ID' => $arData['ID']],
                    false,
                    false,
                    []
                );
                while ($obElement = $obElements->GetNextElement())
                {
                    $fields = $obElement->GetFields();
                    $fields['PROPERTIES'] = $obElement->GetProperties();
                    $arOldProp = $fields;
                }
            }

            $date = new \DateTime();

            $PROP = [
                'NEW_PRICE' => $arData['NEW_PRICE_CART'],
                'OLD_PRICE' => $arOldProp['PROPERTIES']['PRICE_CART']['VALUE'] ? $arOldProp['PROPERTIES']['PRICE_CART']['VALUE'] : 0,
                'OLD_ISSET' => $arOldProp['PROPERTIES']['ISSET_CART']['VALUE'] ? $arOldProp['PROPERTIES']['ISSET_CART']['VALUE'] : 0,
                'NEW_ISSET' => $arData['ISSET_CART'],
                'ID_CART_DEALER' => $arData['ID'],
                'WHO_CHANGE' => $this->user_id,
                'DATA' => $date->format('d.m.Y H:i:s')
            ];
            $arFields = [
                'MODIFIED_BY' => $this->user_id,
                'IBLOCK_SECTION_ID' => $id_section,
                'IBLOCK_ID' => $this->iblock_dealers_history,
                'PROPERTY_VALUES' => $PROP,
                'ACTIVE' => 'Y',
                'NAME' => $arData['NAME']
            ];

            $this->obElements->Add($arFields);
        }
    }

    public function ReloadBouquetPrice()
    {

    }

    public function ReloadBouquet($id_bouquet, $arFields)
    {
        $bouquet = $this->UpdateBouquet($id_bouquet, $arFields);
        $catalog = $this->UpdateBouquetCatalog($id_bouquet, $arFields);
        
        if ($bouquet == 1 && $catalog == 1)
        {
            return 1;
        }
    }

    public function UpdateBouquet($id_bouquet, $arFields)
    {
        $pre_pic = $arFields['PREVIEW_PICTURE'];
        $det_pic = $arFields['DETAIL_PICTURE'];

        if ($arFields['NAME'])
            $arFieldsUpdate['NAME'] = $arFields['NAME'];

        if ($arFields['CODE'])
            $arFieldsUpdate['CODE'] = $arFields['CODE'];

        if ($arFields['PREVIEW_TEXT'])
            $arFieldsUpdate['PREVIEW_TEXT'] = $arFields['PREVIEW_TEXT'];

        if ($arFields['DETAIL_TEXT'])
            $arFieldsUpdate['DETAIL_TEXT'] = $arFields['DETAIL_TEXT'];

        if ($pre_pic)
            $arFieldsUpdate['PREVIEW_PICTURE'] = $pre_pic;

        if ($det_pic)
            $arFieldsUpdate['DETAIL_PICTURE'] = $det_pic;

        if ($arFieldsUpdate)
            $res = $this->obElements->Update($id_bouquet, $arFieldsUpdate);
        else
            $res = 1;

        if ($arFields['PROPERTY_VALUES']['TOTAL_PRICE'])
        {
            \CIBlockElement::SetPropertyValuesEx($id_bouquet, $this->iblock_dealer, ['PRICE_BOUQ' => $arFields['PROPERTY_VALUES']['TOTAL_PRICE']]);
        }
        if ($arFields['PROPERTY_VALUES']['EXTRA_PRICE'])
        {
            \CIBlockElement::SetPropertyValuesEx($id_bouquet, $this->iblock_dealer, ['EXTRA_PRICE' => $arFields['PROPERTY_VALUES']['EXTRA_PRICE']]);
        }

        if ($arFields['PROPERTY_VALUES']['DEALER_ITEMS'])
        {
            $prop = $this->GetIssetProcentBouquet($arFields['PROPERTY_VALUES']['DEALER_ITEMS']);
            \CIBlockElement::SetPropertyValuesEx($id_bouquet, $this->iblock_dealer, ['ISSET_BOUQ' => $prop]);
        }

        if ($arFields['PROPERTY_VALUES']['SHOW_BOUQ'] == 'true')
            \CIBlockElement::SetPropertyValuesEx($id_bouquet, $this->iblock_dealer, ['SHOW_BOUQ' => 1]);
        else
            \CIBlockElement::SetPropertyValuesEx($id_bouquet, $this->iblock_dealer, ['SHOW_BOUQ' => 0]);


        $this->LIST_ERROR = $this->obElements->LAST_ERROR;

        return $res;
    }

    public function GetIssetProcentBouquet($arItems)
    {
        if (is_array($arItems) && count($arItems) > 0)
        {
            $count_isset = 0;
            $count_total = count($arItems);

            $arMain_section = $this->GetMainSection();

            $obElements = $this->obElements->GetList(
                [],
                ['IBLOCK_ID' => $this->iblock_dealer, 'ACTIVE' => 'Y', 'ID' => $arItems, 'SECTION_ID' => $arMain_section['ID'], 'INCLUDE_SUBSECTIONS' => 'Y'],
                false,
                false,
                []
            );
            while ($obElement = $obElements->GetNextElement())
            {
                $fields = $obElement->GetFields();
                $fields['PROPERTIES'] = $obElement->GetProperties();

                if ($fields['PROPERTIES']['ISSET_CART']['VALUE'] == 'Y')
                {
                    $count_isset++;
                }
            }

        }

        if ($count_isset > 0)
            $percent = ($count_isset / $count_total) * 100;
        else
            $percent = 0;

        return round($percent);
    }

    public function UpdateBouquetCatalog($id_bouquet, $arFields)
    {
        $pre_pic = $arFields['PREVIEW_PICTURE'];
        $det_pic = $arFields['DETAIL_PICTURE'];

        if ($arFields['NAME'])
            $arFieldsUpdate['NAME'] = $arFields['NAME'];

        if ($arFields['CODE'])
            $arFieldsUpdate['CODE'] = $arFields['CODE'];

        if ($arFields['IBLOCK_SECTION'])
            $arFieldsUpdate['IBLOCK_SECTION'] = $arFields['IBLOCK_SECTION'];

        if ($arFields['PREVIEW_TEXT'])
            $arFieldsUpdate['PREVIEW_TEXT'] = $arFields['PREVIEW_TEXT'];

        if ($arFields['DETAIL_TEXT'])
            $arFieldsUpdate['DETAIL_TEXT'] = $arFields['DETAIL_TEXT'];

        $arFieldsUpdate['DETAIL_PICTURE'] = $det_pic;        
        // $arFieldsUpdate['PREVIEW_PICTURE'] = $pre_pic;

        $arMain_section = $this->GetMainSection();

        $obElements = $this->obElements->GetList(
            [],
            ['IBLOCK_ID' => $this->iblock_dealer, 'ACTIVE' => 'Y', 'SECTION' => $arMain_section['ID'], 'INCLUDE_SUBSECTIONS' => 'Y', 'ID' => $id_bouquet],
            false,
            false,
            []
        );
        while ($obElement = $obElements->GetNextElement())
        {
            $prop = $obElement->GetProperties();
            if ($prop['ID_BOUQ']['VALUE'])
            {
                $id_cart = $prop['ID_BOUQ']['VALUE'];
            }
            $arProp = $prop;
        }

        if ($arFieldsUpdate)
            $res = $this->obElements->Update($id_cart, $arFieldsUpdate);
        else
            $res = 1;

        $this->LIST_ERROR = $this->obElements->LAST_ERROR;

        if ($arFields['PROPERTY_VALUES']['TOTAL_PRICE'])
            $this->ChangePrice($id_cart, $arFields);

        if ($arFields['PROPERTY_VALUES']['EXTRA_PRICE'])
            \CIBlockElement::SetPropertyValuesEx($id_cart, $this->catalog_id, ['EXTRA_PRICE' => $arFields['PROPERTY_VALUES']['EXTRA_PRICE']]);

        if ($arFields['PROPERTY_VALUES']['DEALER_ITEMS'])
            \CIBlockElement::SetPropertyValuesEx($id_cart, $this->catalog_id, ['DEALER_ITEMS' => $arFields['PROPERTY_VALUES']['DEALER_ITEMS']]);

        if ($arFields['PROPERTY_VALUES']['DEALER_ITEMS_COUNT'])
            \CIBlockElement::SetPropertyValuesEx($id_cart, $this->catalog_id, ['DEALER_ITEMS_COUNT' => $arFields['PROPERTY_VALUES']['DEALER_ITEMS_COUNT']]);

        if (is_array($arProp['PHOTOS']['VALUE']) && count($arProp['PHOTOS']['VALUE']) > 0)
        {
            \CIBlockElement::SetPropertyValuesEx($id_cart, $this->catalog_id, array('PHOTOS' => Array ("VALUE" => array("del" => "Y"))));
        }

        if (is_array($arFields['PROPERTY_VALUES']['PHOTOS']) && count($arFields['PROPERTY_VALUES']['PHOTOS']) > 0)
        {
            \CIBlockElement::SetPropertyValuesEx($id_cart, $this->catalog_id, ['PHOTOS' => $files]);
        }

        if ($arFields['PROPERTY_VALUES']['WEIGHT'])
        {
            \CIBlockElement::SetPropertyValuesEx($id_cart, $this->catalog_id, ['WEIGHT' => $arFields['PROPERTY_VALUES']['WEIGHT']]);
        }

        if ($arFields['PROPERTY_VALUES']['HEIGHT'])
        {
            \CIBlockElement::SetPropertyValuesEx($id_cart, $this->catalog_id, ['HEIGHT' => $arFields['PROPERTY_VALUES']['HEIGHT']]);
        }



        return $res;
    }

    public function CreateBouquet($arFields)
    {
        $id_element = $this->obElements->Add($arFields);

        if ($id_element)
        {
            $this->AddPrice($id_element, $arFields['PROPERTY_VALUES']['TOTAL_PRICE']);
            $this->AddBouquetToDealer($id_element, $arFields);
            
            return $id_element;
        }
        else
        {
            $this->LIST_ERROR = $this->obElements->LAST_ERROR;
        }
    }

    protected function AddBouquetToDealer($id_element, $arFields)
    {
        $arSectionBouquet = $this->GetSectionWithBouquet();

        $arFields['IBLOCK_ID'] = $this->iblock_dealer;
        $arFields['IBLOCK_SECTION'] = $arSectionBouquet['ID'];
        $arFields['PROPERTY_VALUES']['ID_BOUQ'] = $id_element;
        $arFields['PROPERTY_VALUES']['PRICE_BOUQ'] = $arFields['PROPERTY_VALUES']['TOTAL_PRICE'];
        $arFields['PROPERTY_VALUES']['ISSET_BOUQ'] = 100;    

        $this->obElements->Add($arFields);
    }

    protected function ChangePrice($id_element, $arFields)
    {
        $arFieldsPrice = Array(
            "PRODUCT_ID" => $id_element,
            "CATALOG_GROUP_ID" => $this->price_id,
            "PRICE" => (float)$arFields['PROPERTY_VALUES']['TOTAL_PRICE'],
            "CURRENCY" => $this->currency,
        );

        $dbPrice = \Bitrix\Catalog\Model\Price::getList([
            "filter" => array(
                "PRODUCT_ID" => $id_element,
                "CATALOG_GROUP_ID" => $this->price_id
        )]);

        if ($arPrice = $dbPrice->fetch())
        {
            \Bitrix\Catalog\Model\Price::update($arPrice["ID"], $arFieldsPrice);
        }
    }

    protected function AddPrice($id_element, $price = '')
    {
        $arFieldsCatalog['ID'] = $id_element;

        \CModule::IncludeModule('sale');
        \CModule::IncludeModule('catalog');
        if (\Bitrix\Catalog\Model\Product::add($arFieldsCatalog))
        {
            $arFiledPrice = [
                'PRODUCT_ID' => $id_element,
                'PRICE' => $price,
                'CURRENCY' => $this->currency,
                'CATALOG_GROUP_ID' => $this->price_id
            ];
            \Bitrix\Catalog\Model\Price::add($arFiledPrice);
        }
    }

    public function AddProduct($arFields, $price = '')
    {
        $id_element = $this->obElements->Add($arFields);

        if ($id_element)
        {
            $this->AddPrice($id_element, $price);

            return $id_element;
        }
        else
        {
            $this->LIST_ERROR[] = $this->obElements->LAST_ERROR;
        }
    }

    public function GetListManager()
    {
        $filter = [
            'ACTIVE' => 'Y',
            'GROUPS_ID' => $this->GroupManager
        ];
        $obGroups = \CUser::GetList(($by="personal_country"), ($order="desc"), $filter);
        while ($arGroup = $obGroups->Fetch())
        {
            $arGroups[] = $arGroup;
        }

        return $arGroups;
    }

    public function GetIdElementDealer()
    {
        $obElement = $this->obElements->GetList(
            [],
            ['IBLOCK_ID' => $this->iblock_dealers_list, 'ACTIVE' => 'Y', 'PROPERTY_ID_USER' => $this->user_id],
            false,
            false,
            []
        );
        while ($arElement = $obElement->GetNext())
        {
            return $arElement['ID'];
        }
    }

}
?>