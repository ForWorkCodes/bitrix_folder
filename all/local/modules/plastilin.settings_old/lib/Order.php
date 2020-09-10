<?
namespace plastilin\settings;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;


class Order
{
    private $price_id;
    private $person_type;
    private $user_id;
    private $currency;
    private $is_new;
    private $arItems;
    private $deliveryId;
    private $description;
    private $paymentId;
    private $Prop;
    private $USER;

    public function __construct($params, $arItems)
    {
    	if (!is_array($arItems) && count($arItems) == 0)
    	{
    		return 'Need Items';
    	}

    	global $USER;
    	\Cmodule::includeModule('catalog');
    	\CModule::IncludeModule('sale');

    	if ($params['PROPS'])
    	{
    		$this->Prop = $params['PROPS'];
    	}
    	if ( $pr = Option::get('plastilin.settings', "price_turn") )
    		$push_price = $pr;
    	elseif ( $pr = Option::get('plastilin.settings', "price_id") )
    		$push_price = $pr;

		if ($params['CURRENCY'])
			$currency = $params['CURRENCY'];
		elseif ( $temp = Option::get('plastilin.settings', "currency") )
			$currency = $temp;
		else
			$currency = \CCurrency::GetBaseCurrency();

		if ($params['DELIVERY'])
			$deliveryId = $params['DELIVERY'];
		elseif ( $temp = Option::get('plastilin.settings', "delivery") )
			$deliveryId = $temp;
		else
			$deliveryId = 1;

		if ($params['PAYMENT'])
			$paymentId = $params['PAYMENT'];
		elseif ($temp = Option::get('plastilin.settings', "payment") )
			$paymentId = $temp;
		else
			$paymentId = 1;

		if ($params['DESCRIPTION'])
			$description = $params['DESCRIPTION'];
		elseif ( $temp = Option::get('plastilin.settings', "description_order") )
			$description = $temp;
		else
			$description = 'Быстрый заказ';

		if ($params['USER_ID'])
			$user_id = $params['USER_ID'];
		elseif ( $temp = Option::get('plastilin.settings', "user_order") )
			$user_id = $temp;
		elseif ( $temp = $USER->GetID() )
			$user_id = $temp;
		else
			$user_id = 1;

		if ($params['PERSON_TYPE'])
			$person_type = $params['PERSON_TYPE'];
		elseif ( $temp = Option::get('plastilin.settings', "order_person_type") )
			$person_type = $temp;
		else
			$person_type = 1;
		$CheckPerson = $this->CheckPerson($person_type);
		if (!$CheckPerson)
			return 'Incorrect person_type_id';

		if ($params['PRICE_ID'])
			$price_id = $params['PRICE_ID'];
		elseif ( $temp = $push_price )
			$price_id = $temp;
		else
			$price_id = '';

		if ($params['IS_NEW'])
		{
			$this->is_new = $params['IS_NEW'];
		}

		$this->price_id = $price_id;
		$this->person_type = $person_type;
		$this->user_id = $user_id;
		$this->arItems = $arItems;
		$this->description = $description;
		$this->paymentId = $paymentId;
		$this->deliveryId = $deliveryId;
		$this->currency = $currency;
    	$this->USER = new \CUser($user_id);
    }

    public function CheckPerson($person_type)
    {
    	$db_ptype = \CSalePersonType::GetList([], ["LID"=>SITE_ID, 'ID' => $person_type]);
		while ($ptype = $db_ptype->Fetch())
		{
		   return $ptype['ID'];
		}

    }

    public function CreateOrder()
    {

    	\CModule::IncludeModule('sale');
    	\CModule::IncludeModule('catalog');
    	$basket = \Bitrix\Sale\Basket::create(SITE_ID);
    	foreach ($this->arItems as $arItem)
	    {
	    	if ($this->price_id && !$this->is_new)
	    	{
	    		$arItem['PRICE'] = $this->GetFullPriceFromPriceType($arItem['PRODUCT_ID']);
	    		$arItem['CUSTOM_PRICE'] = 'Y';
	    	}
	    	elseif ($this->is_new)
	    	{
	    		$price = (float)$this->GetFullPriceFromPriceType($arItem['PRODUCT_ID']);
	    		$priceFromSubs = (float)$this->GetPriceForOneCartInSubs();

	    		if ($price > $priceFromSubs)
	    		{
	    			$finalPrice = $price - $priceFromSubs;
	    			$isset_expensive = 1;
	    		}
	    		else
	    		{
	    			$finalPrice = 0;
	    		}

	    		$arItem['PRICE'] = $finalPrice;
	    		$arItem['CUSTOM_PRICE'] = 'Y';
	    	}
	    	$arItem['PRODUCT_PROVIDER_CLASS'] = \Bitrix\Catalog\Product\Basket::getDefaultProviderName();
	        $item = $basket->createItem("catalog", $arItem["PRODUCT_ID"]);
	        unset($arItem["PRODUCT_ID"]);
	        $item->setFields($arItem);
	    }

	    $order = \Bitrix\Sale\Order::create(SITE_ID, $this->user_id);
		$order->setPersonTypeId($this->person_type);
		$order->setBasket($basket);

		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->createItem(
	        \Bitrix\Sale\Delivery\Services\Manager::getObjectById($this->deliveryId)
	    );	
	    $shipmentItemCollection = $shipment->getShipmentItemCollection();
	    foreach ($basket as $basketItem)
	    {
	        $item = $shipmentItemCollection->createItem($basketItem);
	        $item->setQuantity($basketItem->getQuantity());
	    }
	    $paymentCollection = $order->getPaymentCollection();
		$payment = $paymentCollection->createItem(
	        \Bitrix\Sale\PaySystem\Manager::getObjectById($this->paymentId)
	    );
	    $payment->setField("SUM", $order->getPrice());
		$payment->setField("CURRENCY", $order->getCurrency());

		if ($this->Prop)
		{
			$propertyCollection = $order->getPropertyCollection();

			foreach ($this->Prop as $key_id => $propVal)
			{
				$somePropValue = $propertyCollection->getItemByOrderPropertyId($key_id);
				$somePropValue->setValue($propVal);
			}
		}

	    if ($this->is_new)
	    {
			if ($isset_expensive != 1)
			{
				$order->setField('STATUS_ID', 'P');
				$payment->setPaid('Y');
			}
	    }

	    if ($this->price_id)
	    {
			$payment->setField('MARKED', 'N'); // Попытка убрать старую цену
	    }

		$order->setField('USER_DESCRIPTION', $this->description);

		$result = $order->save();
		if (!$result->isSuccess())
			return $result->getErrors();
        else
        	return $result->getID();

    }

    private function GetFullPriceFromPriceType($id)
    {
    	$arPrices = \Bitrix\Catalog\PriceTable::getList([
          "select" => ["*"],
          "filter" => [
               "PRODUCT_ID" => $id,
               "CATALOG_GROUP_ID" => $this->price_id
          ]
        ])->fetchAll();

        if ($arPrices)
        {
            foreach ($arPrices as &$arPrice)
            {
                $arDiscounts =  \CCatalogDiscount::GetDiscountByPrice(
                    $arPrice['ID'],
                    $this->USER->GetUserGroupArray()
                );

                $discountPrice = \CCatalogProduct::CountPriceWithDiscount(
                   $arPrice["PRICE"],
                   $arPrice["CURRENCY"],
                   $arDiscounts
                );
                return $discountPrice;
            }
        }
    }

    private function GetBasePriceFromPriceType($id)
    {
    	$arPrices = \Bitrix\Catalog\PriceTable::getList([
          "select" => ["*"],
          "filter" => [
               "PRODUCT_ID" => $id,
               "CATALOG_GROUP_ID" => $this->price_id
          ]
        ])->fetchAll();

        if ($arPrices)
        {
            foreach ($arPrices as $arPrice)
            {
            	/* Добавить проверку, если цена товара меньше чем цена подписки, тогда установить цену как у подписки */
                return $arPrice['PRICE'];
            }
        }
    }

    private function GetPriceForOneCartInSubs()
    {
    	$arFilter = array("ID" => $this->user_id, "!UF_SUBSCRIBE" => ['', Option::get('plastilin.settings', "subs_empty_id")]);
		$arParams["SELECT"] = array("*", "UF_SUBSCRIBE");

		$obRes = \CUser::GetList($by,$desc,$arFilter, $arParams);
		while ($arRes = $obRes->Fetch())
		{
			$id_subs = $arRes['UF_SUBSCRIBE'];
		}

		if ($id_subs)
		{
			$obElements = \CIBlockElement::GetList(
				[],
				['IBLOCK_ID' => Option::get('plastilin.settings', "subs_iblock_id"), 'ID' => $id_subs],
				false,
				false,
				[]
			);
			while ($obElement = $obElements->GetNextElement())
			{
				$prop = $obElement->GetProperties();
				if ($prop['PRICE_FOR_ONE']['VALUE'])
				{
					return $prop['PRICE_FOR_ONE']['VALUE'];
				}
			}
		}
    }

}
?>