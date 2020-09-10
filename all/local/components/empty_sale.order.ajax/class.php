<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
 
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
 
class customOrderComponent extends CBitrixComponent
{
	/**
	 * @var \Bitrix\Sale\Order
	 */
	private $order;
	private $delivery_on = true;
	private $delivery_change = false;
	private $payment_change = false;
	private $payment_on = true;
	private $default_payment = 2;
	private $default_delivery = 4;
	public $arResult;
	public $propMap = [];
 
	protected $errors = [];
 
	function __construct($component = null)
	{
		parent::__construct($component);
 
		if(!Loader::includeModule('sale')){
			$this->errors[] = 'No sale module';
		};
 
		if(!Loader::includeModule('catalog')){
			$this->errors[] = 'No catalog module';
		};
	}
 
	function onPrepareComponentParams($arParams)
	{
		if (isset($arParams['PERSON_TYPE_ID']) && intval($arParams['PERSON_TYPE_ID']) > 0) {
			$arParams['PERSON_TYPE_ID'] = intval($arParams['PERSON_TYPE_ID']);
		} else {
			if (intval($this->request['payer']['person_type_id']) > 0) {
				$arParams['PERSON_TYPE_ID'] = intval($this->request['payer']['person_type_id']);
			} else {
				$arParams['PERSON_TYPE_ID'] = 1;
			}
		}
 
		return $arParams;
	}
 
	protected function createVirtualOrder()
	{
		global $USER;
 
		try {
 			/* Формирование корзины */
			$siteId = \Bitrix\Main\Context::getCurrent()->getSite();
			$basketItems = \Bitrix\Sale\Basket::loadItemsForFUser(
				\CSaleBasket::GetBasketUserID(), 
				$siteId
			)
				->getOrderableItems();
 			/* -Формирование корзины- */
 			
 			/* Редирект если корзина пуста */
			if (count($basketItems) == 0)
			{
				LocalRedirect('/');
			}
 			/* -Редирект если корзина пуста- */
 	
 			/* Запись корзины в заказ */
			$this->order = \Bitrix\Sale\Order::create($siteId, $USER->GetID());
			$this->order->setPersonTypeId($this->arParams['PERSON_TYPE_ID']);
			$this->order->setBasket($basketItems);
 			/* -Запись корзины в заказ- */

			/* Формирование свойств */
			$this->setOrderProps();
			/* -Формирование свойств- */

			/* Формирование служб доставок/отгрузок */
			$this->CreateShipment();
			/* -Формирование служб доставок/отгрузок- */
			
 			/* Формирование способов оплаты */
 			$this->CreatePayment();
 			/* -Формирование способов оплаты- */


		}
		catch (\Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}

	private function CreatePayment()
	{
		if (!$this->payment_on) return;

		if (intval($this->request['payment']) > 0)
			$payment_id = intval($this->request['payment']);
		elseif ($this->default_payment)
			$payment_id = intval($this->default_payment);
		else
			$payment_id = false;

		if ($payment_id)
		{
			$paymentCollection = $this->order->getPaymentCollection();
			if ($this->payment_change)
			{
				$payment = $paymentCollection->createItem(
					Bitrix\Sale\PaySystem\Manager::getObjectById(
						$payment_id
					)
				);
				
			}
			else
			{
				$payment = $paymentCollection->createItem(
					Bitrix\Sale\PaySystem\Manager::getObjectById(
						intval($this->default_payment)
					)
				);
			}
			$id = $payment->getPaymentSystemId();

			if ($id == '1' || $id == '0' && $this->default_payment)
			{
				$payment = $paymentCollection->createItem(
					Bitrix\Sale\PaySystem\Manager::getObjectById(
						intval($this->default_payment)
					)
				);
			}

			$payment->setField("SUM", $this->order->getPrice());
			$payment->setField("CURRENCY", $this->order->getCurrency());
		}
		
	}

	private function CreateShipment()
	{
		/* @var $shipmentCollection \Bitrix\Sale\ShipmentCollection */
		$shipmentCollection = $this->order->getShipmentCollection();
		
		if (intval($this->request['delivery']) > 0 && $this->delivery_on)
		{
			if ($this->delivery_change)
			{
				$shipment = $shipmentCollection->createItem(
					Bitrix\Sale\Delivery\Services\Manager::getObjectById(
						intval($this->request['delivery'])
					)
				);

				if ($shipment->getDelivery()->getId() == '1' || $shipment->getDelivery()->getId() == '' && intval($this->default_delivery) > 0)
				{
					$shipment = $shipmentCollection->createItem(
						Bitrix\Sale\Delivery\Services\Manager::getObjectById(
							intval($this->default_delivery)
						)
					);
				}
			}
			else
			{
				$shipment = $shipmentCollection->createItem(
					Bitrix\Sale\Delivery\Services\Manager::getObjectById(
						intval($this->default_delivery)
					)
				);
			}
		}
		elseif (intval($this->default_delivery) > 0)
		{
			$shipment = $shipmentCollection->createItem(
				Bitrix\Sale\Delivery\Services\Manager::getObjectById(
					intval($this->default_delivery)
				)
			);
		}
		else
		{
			$shipment = $shipmentCollection->createItem();
		}

		/** @var $shipmentItemCollection \Bitrix\Sale\ShipmentItemCollection */
		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		$shipment->setField('CURRENCY', $this->order->getCurrency());

		foreach ($this->order->getBasket()->getOrderableItems() as $item)
		{
			/**
			 * @var $item \Bitrix\Sale\BasketItem
			 * @var $shipmentItem \Bitrix\Sale\ShipmentItem
			 * @var $item \Bitrix\Sale\BasketItem
			 */
			$shipmentItem = $shipmentItemCollection->createItem($item);
			$shipmentItem->setQuantity($item->getQuantity());
		}
	}

	protected function setOrderProps()
	{
		global $USER;
		$arUser = $USER->GetByID(intval($USER->GetID()))
			->Fetch();
 
		if (is_array($arUser))
		{
			$fio = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
			$fio = trim($fio);
			$arUser['FIO'] = $fio;
		}
 
		foreach ($this->order->getPropertyCollection() as $prop)
		{
			/** @var \Bitrix\Sale\PropertyValue $prop */
			$this->propMap[$prop->getField('CODE')] = $prop->getPropertyId();

			$value = $default = '';
			if ($prop->isUtil()) continue;
 
			switch ($prop->getField('CODE'))
			{
				case 'FIO':
					$value = $this->request['contact']['family'];
					$value .= ' ' . $this->request['contact']['name'];
					$value .= ' ' . $this->request['contact']['second_name'];
 
					$value = trim($value);
					if (empty($value))
					{
						$value = $arUser['FIO'];
					}
					break;

				case 'NAME':
					$default = $arUser['NAME'];
					break;

				case 'LAST_NAME':
					$default = $arUser['LAST_NAME'];
					break;

				case 'EMAIL':
					$default = $arUser['EMAIL'];
					break;

				case 'COUNTRY':
					$arCountries = GetCountryArray();
					foreach ($arCountries['reference_id'] as $key => $data)
					{
						if ($data == $arUser['PERSONAL_COUNTRY'])
						{
							$id = $key;
						}
					}
					$default = $arCountries['reference'][$id];
					break;

				case 'CITY':
					$default = $arUser['PERSONAL_CITY'];
					break;

				case 'ADDRESS':
					$default = $arUser['PERSONAL_STREET'];
					break;

				case 'INDEX':
					$default = $arUser['PERSONAL_ZIP'];
					break;
 
				default:
			}
			if (empty($value))
			{
				foreach ($this->request as $key => $val)
				{
					if (strtolower($key) == strtolower($prop->getField('CODE')))
					{
						if (empty($val)) $val = $default;
						$value = $val;
					}
				}
			}
 
			if (empty($value))
			{
				if ($default)
					$value = $default;
				else
					$value = $prop->getProperty()['DEFAULT_VALUE'];
			}
 
			if (!empty($value))
			{
				$prop->setValue($value);
			}
		}
	}

	private function GetDeliveries()
	{
		$shipment = false;
		/** @var \Bitrix\Sale\Shipment $shipmentItem */
		foreach ($this->order->getShipmentCollection() as $shipmentItem)
		{
			if (!$shipmentItem->isSystem())
			{
				$shipment = $shipmentItem;
				break;
			}
		}
 
		$availableDeliveries = [];
		if (!empty($shipment)) {
			$availableDeliveries = Delivery\Services\Manager::getRestrictedObjectsList($shipment);
		}
 		
 		if ($availableDeliveries)
 		{
 			foreach ($availableDeliveries as $obDel)
 			{
 				$arDel[$obDel->getId()] = [
 					'ID' => $obDel->getId(),
 					'NAME' => $obDel->getName(),
 					'CURRENCY' => $obDel->getCurrency(),
 					'DESCRIPTION' => $obDel->getDescription(),
 					'CONFIG' => $obDel->getConfig(),
 					'extraServices' => $obDel->getExtraServices()->getItems()
 				];
 			}
 		}
		return $arDel;
	}

	private function GetPaySystems()
	{
		$obPay = CSalePaySystem::GetList(
			$arOrder = Array("SORT" => "ASC", "PSA_NAME" => "ASC"),
			Array("LID" => SITE_ID, "CURRENCY" => $currency, "ACTIVE" => "Y", "PERSON_TYPE_ID" => $this->arParams['PERSON_TYPE_ID'])
		);
		while ($arPay = $obPay->Fetch())
		{
			if ($arPay['ID'] == 1) continue;
			if ($arPay['PSA_PARAMS'])
			{
				$param = unserialize($arPay['PSA_PARAMS']);
				$arPay['PSA_PARAMS'] = $param;
			}
			$list[$arPay['ID']] = $arPay;
		}

		return $list;
	}

	private function GetSelectedDelivery()
	{
		/** @var \Bitrix\Sale\Shipment $shipmentItem */
		foreach ($this->order->getShipmentCollection() as $shipmentItem)
		{
			if (!$shipmentItem->isSystem())
			{
				$shipment = $shipmentItem;
				break;
			}
		}

		if ($shipment->getDelivery())
		{
			$id = $shipment->getDelivery()->getId();
			return $id;
		}
	}

	private function GetSelectedPayment()
	{
		foreach ($this->order->getPaymentCollection() as $payment)
			return $payment->getPaymentSystemId();
	}

	private function CreateResultArray()
	{
		$currency = $this->order->getCurrency();
		/* Массив цен */
		$this->arResult['ORDER']['BASKET_PRICE'] = $this->order->getBasket()->getPrice();
		$this->arResult['ORDER']['BASKET_PRICE_TEXT'] = \SaleFormatCurrency($this->order->getBasket()->getPrice(), $currency);

		$this->arResult['ORDER']['BASKET_BASE_PRICE'] = $this->order->getBasket()->getBasePrice();
		$this->arResult['ORDER']['BASKET_BASE_PRICE_TEXT'] = \SaleFormatCurrency($this->order->getBasket()->getBasePrice(), $currency);

		$this->arResult['ORDER']['FINAL_PRICE'] = $this->order->getPrice();
		$this->arResult['ORDER']['CURRENCY'] = $currency;
		$this->arResult['ORDER']['FINAL_PRICE_TEXT'] = \SaleFormatCurrency($this->arResult['ORDER']['FINAL_PRICE'], $currency);
		/* -Массив цен- */

		if ($this->delivery_on)
		{
			/* Массив доставок */
			$this->arResult['DELIVERIES'] = $this->GetDeliveries();
			/* -Массив доставок- */

			/* Выбранная служба доставки */
			$this->arResult['SELECTED']['DELIVERY'] = $this->GetSelectedDelivery();
			/* -Выбранная служба доставки- */

			$this->arResult['ORDER']['DELIVERY_PRICE'] = $this->order->getDeliveryPrice();
			$this->arResult['ORDER']['DELIVERY_PRICE_TEXT'] = \SaleFormatCurrency($this->arResult['ORDER']['DELIVERY_PRICE'], $currency);
		}

		if ($this->payment_on)
		{
			/* Массив платежных систем */
			$this->arResult['PAY_SYSTEMS'] = $this->GetPaySystems();
			/* -Массив платежных систем- */

			/* Выбранный способ оплаты */
			$this->arResult['SELECTED']['PAYMENT'] = $this->GetSelectedPayment();
			/* -Выбранный способ оплаты- */
		}

		/* Массив свойств */
		foreach ($this->order->getPropertyCollection()->getArray()['properties'] as $arProp)
		{
			if ($arProp['UTIL'] == 'Y') continue;
            $this->arResult['PROPERTIES'][$arProp['ID']] = $arProp;
        }
		/* -Массив свойств- */
	}
 
	function executeComponent()
	{
		if ($this->request['is_ajax'] == 'Y')
 		{
 			global $APPLICATION;
 			$APPLICATION->RestartBuffer();
 		}

		$this->createVirtualOrder();

		$this->CreateResultArray();

		if (isset($this->request['save']) && $this->request['save'] == 'Y') {
			$this->order->save();
		}
 		
 		if ($this->request['is_ajax'] == 'Y')
 		{
			$this->includeComponentTemplate();
 			die();
 		}
 		else
 		{
 			echo "<div id='order-ajax'>";
			$this->includeComponentTemplate();
 			echo "</div>";
 		}
	}
 
}