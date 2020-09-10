<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

 
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Bitrix\Sale\Location\GeoIp;
 
class customOrderComponent extends CBitrixComponent
{
	/**
	 * @var \Bitrix\Sale\Order
	 */
	public $order;
	public $saved_order;
	public $saved_order_id;
 	public $services = '';
 	public $default_service = '';
 	public $priceVal;
 	public $debug = [];
 	public $paySystems = [];
 	public $default_paySystem = '';
 	public $locations = [];
 	public $currentGeo = [];
 	public $blocks = [];
 	public $checkers = [];
 	public $forceOpen = '';
 	public $user = '';
 	public $profile = [];
    public $couponList = [];
    public $canSaveOrder = true;
    public $agree = true;
    public $currentProfile = '';
    public $currendDeliveries = [];
    public $storages = [];

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
 	
	function getSavedOrder()
	{
		$this->saved_order = \Bitrix\Sale\Order::load($this->saved_order_id);
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

	/**
	 * @var array
	 */
	public $propMap = [];
 
	public function getPropByCode($code)
	{
		$result = false;
 
		$propId = 0;
		if (isset($this->propMap[$code])) {
			$propId = $this->propMap[$code];
		}
 
		if ($propId > 0) {
			$result = $this->order->getPropertyCollection()->getItemByOrderPropertyId($propId);
		}
 
		return $result;
	}
 
	public function getPropDataByCode($code)
	{
		$result = [];
 
		$propId = 0;
		if (isset($this->propMap[$code])) {
			$propId = $this->propMap[$code];
		}
 
		if ($propId > 0) {
			$result = $this->order
				->getPropertyCollection()
				->getItemByOrderPropertyId($propId)
				->getFieldValues();
		}
 
		return $result;
	}

	protected function testField($field, $type)
	{
		switch($type)
		{
			case 'INDEX':
				$n = preg_replace("/[^0-9]/", '', $field);
				if (strlen($n) != 6)
				{
					return array(
						'success' => 'N',
						'reason' => '6 цифр'
					);
				}
				else
				{
					return array(
						'success' => 'Y',
						'reason' => 'Все ок'	
					);
				}
				break;
			case 'FIO':
			case 'ADDRESS':
            case 'DELIVERY_ADDRESS':
				if (strlen($field) < 3)
				{
					return array(
						'success' => 'N',
						'reason' => 'Не менее 2 букв'
					);
				}
				else
				{
					return array(
						'success' => 'Y',
						'reason' => 'Все ок'
					);
				}
				break;
			case 'EMAIL':
				if (filter_var($field, FILTER_VALIDATE_EMAIL))
				{
					return array(
						'success' => 'Y',
						'reason' => 'Все ок'	
					);
				}
				else
				{
					return array(
						'success' => 'N',
						'reason' => 'Некорректный email'
					);
				}
			case 'PHONE':
				if (strlen($field) == 0)
				{
					return array(
						'success' => 'N',
						'reason' => 'Не введен телефон'
					);
				}
				else
				{
					return array(
						'success' => 'Y',
						'reason' => 'Все ок'
					);
				}
				break;
			default:
				return array(
						'success' => 'Y',
						'reason' => 'Нет проверки еще'	
					);
				break;
		}
	}

	protected function settingsBlocks()
	{
		$arr = $this->order->getPropertyCollection()->getArray()['properties'];
		$this->checkers = [];
		$this->blocks = array(
			'delivery_block'	=> 'Y',
			'payment_block'		=> 'Y',
			'basket_block'		=> 'Y'
		);
		foreach($arr as $key => $value)
		{
			if ($value['REQUIRED'] == 'Y' && isset($this->request[$value['CODE']]))
			{
				// if (isset($this->request['save']))
                if (isset($this->request['check']) && $this->request['check'] == 'Y')
                {
					$this->checkers[$value['CODE']] = $this->testField($value['VALUE'][0], $value['CODE']);
                }
				else
					$this->checkers[$value['CODE']] = array(
						'success'	=> 'Y',
						'reason'	=> 'Начало пути'
					);
			}
			else
			{
				$this->checkers[$value['CODE']] = 'Y';	
			}
		}
		foreach ($this->checkers as $key => $value) {
            if ($value['success'] == 'N')
            {
                $this->canSaveOrder = false;
            }
		}
	}
 
	protected function createVirtualOrder()
	{
		global $USER;

		try {
			$siteId = \Bitrix\Main\Context::getCurrent()->getSite();
			$basketItems = \Bitrix\Sale\Basket::loadItemsForFUser(
				\CSaleBasket::GetBasketUserID(), 
				$siteId
			)
				->getOrderableItems();
            
			if (count($basketItems) == 0) {
				//LocalRedirect(PATH_TO_BASKET);
			}
			$this->getStorages();
            foreach ($basketItems as $arItem)
            {
                $res = CIBlockElement::GetByID($arItem->getProductId());
                if ($r = $res->Fetch())
                {
                    // $this->debug[] = $r;
                    $basketPropertyCollection = $arItem->getPropertyCollection();
                    if (!empty($r['DETAIL_PICTURE']))
                        $img = $r['DETAIL_PICTURE'];
                    elseif (!empty($r['PREVIEW_PICTURE']))
                        $img = $r['PREVIEW_PICTURE'];
                    else
                        $img = SITE_TEMPLATE_PATH . '/images/wom.png';
                    //получение товаров на складах
                    $arStoreIDs = array();
					foreach ($component->storages as $key => $value) {
					    $arStoreIDs[] = $value['ID'];
					}
					$arStoreAmo = array();
					$rsStores = CCatalogStoreProduct::GetList(array(), array('PRODUCT_ID' => $r['ID'], 'STORE_ID' => $arStoreIDs), false, false, array(/*'ID', 'TITLE', 'AMOUNT'*/));
					$i = 0; 
                    while ($arStore = $rsStores->GetNext()){
                    	$arStoreAmo[$i] = $arStore;
                        /*$arStoreAmo[$i]['ID'] = $arStore['ID'];
                        $arStoreAmo[$i]['NAME'] = $arStore['TITLE'];
                        $arStoreAmo[$i]['AMOUNT'] = $arStore['AMOUNT'];*/
                        $i++;
                    }
                    //запись в склады
                    foreach ($this->storages as $key => $arStore) {
                    	$bFind = false;
                    	foreach ($arStoreAmo as $k => $arStoreA) {
	                    	if ($arStore["ID"] == $arStoreA["STORE_ID"]) {
	                    		if (intval($arItem->getQuantity()) <= intval($arStoreA["AMOUNT"])) {
	                    			if ($this->storages[$key]['ALL_BASKET'] == '') {
	                    				$this->storages[$key]['ALL_BASKET'] = 'Y';
	                    			}	                    			
	                    		} else {
	                    			$this->storages[$key]['ALL_BASKET'] = 'N';
	                    		}
	                    		$bFind = true;
	                    	}
	                    }
	                    if (!$bFind) {
	                    	$this->storages[$key]['ALL_BASKET'] = 'N';
	                    }
                    }
                    //
                    $basketPropertyCollection->setProperty(
                        array(0 =>
                            array(
                                'NAME' => 'Картинка',
                                'CODE' => 'IMAGE',
                                'VALUE' => CFile::ResizeImageGet($img, Array("width" => 100, "height" => 100), BX_RESIZE_IMAGE_EXACT)['src']
                            ), 
                            1 => array(
                            	'NAME' => 'Количество на складах',
                            	'CODE' => 'STORES_AMOUNT',
                            	'VALUE' => $arStoreAmo
                            )
                        )
                    );
                    $basketPropertyCollection->save();
                }
            }
            //$this->getStorages();

			$this->order = \Bitrix\Sale\Order::create($siteId, $USER->GetID());
			$this->order->setPersonTypeId($this->arParams['PERSON_TYPE_ID']);
			$this->order->setBasket($basketItems);

			/* @var $shipmentCollection \Bitrix\Sale\ShipmentCollection */
			$shipmentCollection = $this->order->getShipmentCollection();
 
			$this->getServices();

			if (intval($this->request['delivery_type']) > 0) { // Если пришло с формы
				$this->default_service = intval($this->request['delivery_type']);
			}

			$deliv = Bitrix\Sale\Delivery\Services\Manager::getObjectById(intval($this->default_service));
			$shipment = $shipmentCollection->createItem(
				$deliv
			);

			/** @var $shipmentItemCollection \Bitrix\Sale\ShipmentItemCollection */
			$shipment->setField('CURRENCY', $this->order->getCurrency());
			$shipmentItemCollection = $shipment->getShipmentItemCollection();
 
			foreach ($this->order->getBasket()->getOrderableItems() as $item) {
				/**
				 * @var $item \Bitrix\Sale\BasketItem
				 * @var $shipmentItem \Bitrix\Sale\ShipmentItem
				 * @var $item \Bitrix\Sale\BasketItem
				 */
				$shipmentItem = $shipmentItemCollection->createItem($item);
				$shipmentItem->setQuantity($item->getQuantity());
			}

			$this->getUser();
			$this->setUserLocation();
			$this->getLocation();
			// $this->debug['GEO'] = $this->currentGeo;
			// $this->debug['ITEM'] = \Bitrix\Sale\Location\LocationTable::getById($this->currentGeo['CITY']['ID'])->fetch();
			$propertyCollection = $this->order->getPropertyCollection();//получаем коллекцию свойств заказа
			$property = $propertyCollection->getDeliveryLocation();//выбираем ту что отвечает за местоположение
			if (!isset($this->currentGeo['CITY']['NAME']))
			{
				$id = $this->currentGeo['REGION']['ID'];
			}
			else
			{
				$id = $this->currentGeo['CITY']['ID'];	
			}
			$property->setValue(\Bitrix\Sale\Location\LocationTable::getById($id)->fetch()['CODE']);//передаем местоположение
			$deliveries = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);
			$arDeliveries = array();
			$count = 0;
			foreach ($deliveries as $key => $deliveryObj)
			{				
				$ids = [6, 7]; // Экспресс доставки до 19:00
				if (in_array($key, $ids))
				{
					$time = date('Hi');
					if ($time > 1900) continue;
				}
				$ids = [9, 10]; // Доставки за мкад
				if (isset($this->request['FROM_MKAD']))
				{
					if ((int)$this->request['FROM_MKAD'] <= 15 && $key == 10) // До 15 км доставка 600р
					{
						continue;
					}
					if ((int)$this->request['FROM_MKAD'] > 15 && $key == 9) // До 15 км доставка 1200р
					{
						continue;
					}
				}
				else
				{
					if ($key == 10)
					{
						continue; 
					}
				}
				$clonedOrder = $this->order->createClone();//клонируем заказ
				$clonedShipment = $clonedOrder->getShipmentCollection()->createItem(Bitrix\Sale\Delivery\Services\Manager::getObjectById(intval($key)));
				$clonedShipment->setField('CUSTOM_PRICE_DELIVERY', 'N');				
				$calcResult = false;
				$calcOrder = false;
				$arDelivery = array();
				$clonedShipment->setField('DELIVERY_ID', $deliveryObj->getId());
				$clonedOrder->getShipmentCollection()->calculateDelivery();
				$calcResult = $deliveryObj->calculate($clonedShipment);
				$calcOrder = $clonedOrder;
				// $this->debug['ASD'][] = $this->request;
				if ($calcResult->isSuccess())
				{
					if($deliveryObj->getId()!="1"){
						if ($count == 0)
						{
							$this->default_service = $deliveryObj->getId();
						}
						$this->debug['DEL'][] = array(
							$deliveryObj->getId(),
							$this->request['delivery_type']
						);
						if ($this->request['delivery_type'] == $deliveryObj->getId())
						{
							$this->default_service = $deliveryObj->getId();
						}
						$count++;
						$arDelivery['id'] = $deliveryObj->getId();//получаем ИД доставки
						$arDelivery['logo_path'] = $deliveryObj->getLogotipPath();//получаем логотип
						$arDelivery['price'] = \Bitrix\Sale\PriceMaths::roundByFormatCurrency($calcResult->getPrice(), $calcOrder->getCurrency());//получаем стоимость доставки
						$arDelivery['price_formated'] = \SaleFormatCurrency($arDelivery['price'], $calcOrder->getCurrency());//форматируем стоимость в формат сайта
									
						$currentCalcDeliveryPrice = \Bitrix\Sale\PriceMaths::roundByFormatCurrency($calcOrder->getDeliveryPrice(), $calcOrder->getCurrency());
						if ($currentCalcDeliveryPrice >= 0 && $arDelivery['price'] != $currentCalcDeliveryPrice)
							{
							$arDelivery['discount_price'] = $currentCalcDeliveryPrice; //стоимость со скидкой
							$arDelivery['discount_price_formated'] = \SaleFormatCurrency($arDelivery['DELIVERY_DISCOUNT_PRICE'], $calcOrder->getCurrency());//стоимость со скидкой в нужной валюте
							}
									
							if (strlen($calcResult->getPeriodDescription()) > 0)
							{
								$arDelivery['period_text'] = $calcResult->getPeriodDescription();//время доставки
							}else{
								$arDelivery['period_text'] = "";
							}
									
							$arDelivery["name"] = $deliveryObj->getNameWithParent();	//Название доставки
							$arDeliveries[] = $arDelivery;//итоговый массив
						}
				}
			}
			$this->currendDeliveries = $arDeliveries;
			// Пересчет доставки
			$this->debug['SHIP'] = $shipment->getFieldValues();
			$shipment->setField('DELIVERY_ID', $this->default_service);
			$this->order->getShipmentCollection()->calculateDelivery();

			//$this->getPaySystems();
			$paymentCollection = $this->order->getPaymentCollection();
			$payment = $paymentCollection->createItem(
				Bitrix\Sale\PaySystem\Manager::getObjectById(
						intval($this->request['payment_type'])
					)
			);
			$payment->setField("SUM", $this->order->getPrice());

			$this->paySystems = Bitrix\Sale\PaySystem\Manager::getListWithRestrictions($payment);
			$payment->setField("CURRENCY", $this->order->getCurrency());
			if (isset($this->request['payment_type'])) {
				foreach ($this->paySystems as $key => $value)
				{
					if ($this->request['payment_type'] == $key)
					{
						$this->default_paySystem = $this->request['payment_type'];
						break;
					}
				}
			}
			if (empty($this->default_paySystem))
			{
				$this->default_paySystem = current($this->paySystems)['ID'];
			}
			$payment->setField('PAY_SYSTEM_ID', $this->default_paySystem);
			$this->order->getShipmentCollection()->calculateDelivery();

			$this->setOrderProps();
			$this->settingsBlocks();
            $this->setCoupon();
            $this->getCouponList();
            if (isset($this->request['COMMENT']))
            {
                $this->order->setField('USER_DESCRIPTION', $this->request['COMMENT']);
            }
            if (!isset($this->request['AGREE']) && isset($this->request['save']))
            {
                $this->agree = false;
                $this->canSaveOrder = false;
            }
            if (isset($this->request['user_profile_id']))
            {
                $this->currentProfile = $this->request['user_profile_id'];
            }
            $this->selectProfile();
		} catch (\Exception $e) {
			$this->errors[] = $e->getMessage();
		}
	}
	protected function getStorages()
	{
		$res = CCatalogStore::GetList(array(), array("ACTIVE"=>"Y"), false, false, array());
		while ($r = $res->Fetch())
		{
			$this->debug[] = $r;
			$this->storages[] = array(
				'ID' => $r['ID'],
				'NAME' => $r['TITLE'],
				'ADDRESS' => $r['ADDRESS'],
				'PHONE' => $r['PHONE'],
				'SCHEDULE' => $r['SCHEDULE'],
				'GPS_N' => $r['GPS_N'],
				'GPS_S' => $r['GPS_S'],
				'SELECTED' => (isset($this->request['PVZ']) && $this->request['PVZ'] == $r['TITLE']) ? 'Y' : 'N',
				'ALL_BASKET' => ''
			);
		}
	}

    protected function getCouponList()
    {
        $this->couponList = \Bitrix\Sale\DiscountCouponsManager::get(true, array(), true, true);
    }

    protected function selectProfile()
    {
        if (isset($this->request['set_profile_id']) && $this->request['set_profile_id'] == 'Y')
        {
            foreach ($this->profile[$this->currentProfile]['PROPS'] as $key => $value)
            {
                foreach ($this->order->getPropertyCollection() as $p)
                { 
                    if ($p->getField('CODE') == $value['PROP_CODE'])
                    {
                        $p->setValue($value['VALUE']);
                    }
                }
            }
        }
    }

    protected function setCoupon()
    {
        if ($this->request['coupon'])
        {
            $coupon = $this->request['coupon'];
            \Bitrix\Sale\DiscountCouponsManager::add($coupon);
            $discounts = $this->order->getDiscount();
            $discounts->setOrderRefresh(true);
            $discounts->calculate();
            $this->order->getBasket()->save();
            $this->order->refreshData();
            $this->order->doFinalAction();
        }
        if ($this->request['DEL_COUPON'] && !empty($this->request['DEL_COUPON']))
        {
            $coupon = $this->request['DEL_COUPON'];
            \Bitrix\Sale\DiscountCouponsManager::delete($coupon);
            $discounts = $this->order->getDiscount();
            $discounts->setOrderRefresh(true);
            $discounts->calculate();
            $this->order->getBasket()->save();   
            $this->order->refreshData();
            $this->order->doFinalAction();
        }
    }
 	
	protected function getServices() // Получение списка доставок
	{
		$this->services = Bitrix\Sale\Delivery\Services\Manager::getActiveList(); // Список доступных служб доставки
		foreach ($this->services as $key => $shipmentItem) { // Выбор дефолтной службы доставки
			if ($shipmentItem['ID'] != 1) {
				$this->default_service = $shipmentItem['ID'];
				break;
			}
		}
	}

	protected function getPaySystems()
	{
		$db_ptype = CSalePaySystem::GetList($arOrder = Array("SORT"=>"ASC", "PSA_NAME"=>"ASC"), Array("LID"=>SITE_ID, "CURRENCY"=>"RUB", "ACTIVE"=>"Y", "PERSON_TYPE_ID" => 1));
		$first = true;
		while ($ptype = $db_ptype->Fetch())
		{
			$this->debug['PSYS'][] = $ptype;
			if ($first)
			{
				$this->default_paySystem = $ptype['ID'];
			}
			$this->paySystems[] = $ptype;
			$first = false;
		}
	}

	protected function setUserLocation ()
	{
		$this->currentGeo = array(
			'COUNTRY_DISTRICT' => array(
				'ID' => 1
			),
		);
		if (!isset($this->request['save']))
		{
			$ipAddress = \Bitrix\Main\Service\GeoIp\Manager::getRealIp();
			$locCode = GeoIp::getLocationCode($ipAddress, LANGUAGE_ID);
			// $this->debug[] = array(
			// 	'ip' => $ipAddress,
			// 	'loc' => $locCode,
			// );
			$res = \Bitrix\Sale\Location\LocationTable::getList(array(
			    'filter' => array(
			        '=CODE' => $locCode, 
			        '=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
			        '=PARENTS.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
			    ),
			    'select' => array(
			        'I_ID' => 'PARENTS.ID',
			        'I_NAME_RU' => 'PARENTS.NAME.NAME',
			        'I_TYPE_CODE' => 'PARENTS.TYPE.CODE',
			        'I_TYPE_NAME_RU' => 'PARENTS.TYPE.NAME.NAME'
			    ),
			    'order' => array(
			        'PARENTS.DEPTH_LEVEL' => 'asc'
			    )
			));
			$tmp = 1;
			while($item = $res->fetch())
			{
				if ($item['I_TYPE_CODE'] != 'COUNTRY')
				{
					$this->currentGeo[$item['I_TYPE_CODE']]['ID'] = $item['I_ID'];
					$this->currentGeo[$item['I_TYPE_CODE']]['NAME'] = $item['I_NAME_RU'];
					$this->currentGeo[$item['I_TYPE_CODE']]['PARENT_ID'] = $tmp;
					$tmp = $item['I_ID'];
				}
			}
		}
	}

	protected function getLocation()
	{
		if (isset($this->request['LOCATION_COUNTRY_DISTRICT']))
		{
			$this->currentGeo['COUNTRY_DISTRICT']['ID'] = $this->request['LOCATION_COUNTRY_DISTRICT'];
		}
		if (isset($this->request['LOCATION_REGION']))
		{
			$item = \Bitrix\Sale\Location\LocationTable::getById($this->request['LOCATION_REGION'])->fetch();
			if ($item['PARENT_ID'] == $this->currentGeo['COUNTRY_DISTRICT']['ID'])
			{
				$this->currentGeo['REGION']['ID'] = $this->request['LOCATION_REGION'];
				$this->currentGeo['REGION']['PARENT_ID'] = $this->currentGeo['COUNTRY_DISTRICT']['ID'];
			}
		}
		if (isset($this->request['LOCATION_CITY']))
		{
			$item = \Bitrix\Sale\Location\LocationTable::getById($this->request['LOCATION_CITY'])->fetch();
			if ($item['REGION_ID'] == $this->currentGeo['REGION']['ID'] || $this->currentGeo['REGION']['ID'] == 92)
			{
				$this->currentGeo['CITY']['ID'] = $this->request['LOCATION_CITY'];
				$this->currentGeo['CITY']['PARENT_ID'] = $this->currentGeo['REGION']['ID'];
			}
		}

		$res = \Bitrix\Sale\Location\LocationTable::getList(array(
		    'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID),
		    'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
		));
		$items = [];
		while($item = $res->fetch())
		{
			if ($item['ID'] == 92)
			{
				$item2 = $item;
				$item2['TYPE_CODE'] = 'REGION';
				$item['PARENT_ID'] = $item['ID'];
				$items[] = $item2;
			}
			$items[] = $item;
		}
		foreach($items as $item)
		{
			switch($item['TYPE_CODE'])
			{
				case 'COUNTRY_DISTRICT':
					if ($this->currentGeo['COUNTRY_DISTRICT']['ID'] == 1)
					{
						$this->currentGeo['COUNTRY_DISTRICT']['ID'] = $item['ID'];
					}
					if ($item['ID'] == $this->currentGeo['COUNTRY_DISTRICT']['ID'])
					{
						$this->currentGeo['COUNTRY_DISTRICT']['NAME'] = $item['NAME_RU'];
					}
						$this->locations[] = $item;
					break;
				case 'REGION':
					if ($item['PARENT_ID'] == $this->currentGeo['COUNTRY_DISTRICT']['ID'])
					{
						if (!isset($this->currentGeo['REGION']['ID']))
						{
							$this->currentGeo['REGION']['ID'] = $item['ID'];
							$this->currentGeo['REGION']['PARENT_ID'] = $item['PARENT_ID'];
						}
						else
						{
							if ($this->currentGeo['REGION']['PARENT_ID'] != $item['PARENT_ID'])
							{
								$this->currentGeo['REGION']['ID'] = $item['ID'];
								$this->currentGeo['REGION']['PARENT_ID'] = $item['PARENT_ID'];
							}
						}
						if ($item['ID'] == $this->currentGeo['REGION']['ID'])
						{
							if ($this->request['LOCATION_REGION'] == 3)
								$this->currentGeo['REGION']['NAME'] = 'Москва' . $item['NAME_RU'];
							else
								$this->currentGeo['REGION']['NAME'] = $item['NAME_RU'];
						}
						$this->locations[] = $item;
					}
					break;
				case 'CITY':
					if ($item['PARENT_ID'] == $this->currentGeo['REGION']['ID'])
					{
						if (!isset($this->currentGeo['CITY']['ID']))
						{
							$this->currentGeo['CITY']['ID'] = $item['ID'];
							$this->currentGeo['CITY']['PARENT_ID'] = $item['PARENT_ID'];
							$this->debug['CHK'] = 1;
						}
						else
						{
							if ($this->currentGeo['CITY']['PARENT_ID'] != $item['PARENT_ID'])
							{
								$this->currentGeo['CITY']['ID'] = $item['ID'];
								$this->currentGeo['CITY']['PARENT_ID'] = $item['PARENT_ID'];
								$this->debug['CHK'] = 2;
							}
						}
						if ($item['ID'] == $this->currentGeo['CITY']['ID'])
						{
							$this->currentGeo['CITY']['NAME'] = $item['NAME_RU'];
							$this->debug['CHK'] = 3;
						}
						$this->locations[] = $item;
					}
					break;
				default:
					break;
			}			
		}
		for ($i = 0; $i < count($this->locations) - 1; $i++)
		{
			for ($j = $i + 1; $j < count($this->locations); $j++)	
			{
				if ($this->locations[$j]['TYPE_CODE'] != 'REGION') continue;
				if (strcmp($this->locations[$i]['NAME_RU'], $this->locations[$j]['NAME_RU']) > 0)
				//if ($this->currentGeo['REGION'][$i]['NAME'] < $this->currentGeo['REGION'][$j]['NAME'])
				{
					$tmp = $this->locations[$i];
					$this->locations[$i] = $this->locations[$j];
					$this->locations[$j] = $tmp;
				}
			}
		}
	}

	protected function getUser()
	{
		global $USER;
		$this->user = $USER->GetByID(intval($USER->GetID()))->Fetch();
		$db_sales = CSaleOrderUserProps::GetList(
	        array("DATE_UPDATE" => "DESC"),
	        array("USER_ID" => $USER->GetID())
	    );
	    while ($ar_sales = $db_sales->Fetch())
        {
            $this->profile[$ar_sales['ID']] = $ar_sales;
            $db_propVals = CSaleOrderUserPropsValue::GetList(array("ID" => "ASC"), Array("USER_PROPS_ID"=>$ar_sales['ID']));
            while($prop = $db_propVals->Fetch())
            {
                $this->profile[$ar_sales['ID']]['PROPS'][] = $prop;
            }
        }
	}

    protected function saveUserProfile()
    {
        global $USER;
        if ($this->request['user_profile_id'] != '')
        {
            foreach ($this->order->getPropertyCollection()->getArray()['properties'] as $prop) {
                if ($prop['CODE'] == 'FIO')
                {
                    $name = $prop['VALUE'][0];
                    break;
                }
            }
        }
        // $this->debug[] = array(
        //     $this->profile[$this->request['user_profile_id']]['NAME'],
        //     $name,
        // );
        if ($this->request['user_profile_id'] == '' || $this->profile[$this->request['user_profile_id']]['NAME'] != $name)
        {
            $arFields = array(
                'NAME' => $this->request['FIO'],
                'USER_ID' => $USER->getID(),
                'PERSON_TYPE_ID' => 1,
            );
            $PROFILE_ID = CSaleOrderUserProps::Add($arFields);
            if ($PROFILE_ID)
            {
                $PROPS = [];
                //формируем массив свойств
                foreach ($this->order->getPropertyCollection()->getArray()['properties'] as $prop) {
                    $value = $prop['VALUE'][0];
                    $PROPS[] = array(
                       "USER_PROPS_ID" => $PROFILE_ID,
                       "ORDER_PROPS_ID" => $prop['ID'],
                       "NAME" => $prop['NAME'],
                       "VALUE" => $value
                    );
                }              
                foreach ($PROPS as $prop)
                    CSaleOrderUserPropsValue::Add($prop);
            }
        }
        else
        {
            $PROPS = [];
                //формируем массив свойств
                foreach ($this->order->getPropertyCollection()->getArray()['properties'] as $prop) {
                    $value = $prop['VALUE'][0];
                    $id = '';
                    foreach ($this->profile[$this->request['user_profile_id']]['PROPS'] as $v) {
                        if ($v['PROP_CODE'] == $prop['CODE']) 
                        {
                            $id = $v['ID'];
                            break;
                        }
                    }
                    $PROPS[] = array(
                        'ID' => $id,
                       "USER_PROPS_ID" => $this->request['user_profile_id'],
                       "ORDER_PROPS_ID" => $prop['ID'],
                       "NAME" => $prop['NAME'],
                       "VALUE" => $value
                    );
                }
                $this->debug['save_props'] = $PROPS;
            foreach ($PROPS as $prop)
                CSaleOrderUserPropsValue::Update($this->request['user_profile_id'], $prop); 
        }
    }

	protected function setOrderProps()
	{
		global $USER;
		$arUser = $USER->GetByID(intval($USER->GetID()))->Fetch();
 
		if (is_array($arUser)) {
			$fio = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
			$fio = trim($fio);
			$arUser['FIO'] = $fio;
		}
		foreach ($this->order->getPropertyCollection() as $prop) {
			/** @var \Bitrix\Sale\PropertyValue $prop */
			$this->propMap[$prop->getField('CODE')] = $prop->getPropertyId();
			$value = '';
			switch (strtolower($prop->GetField('CODE')))
			{
				case 'location':
					$value = CSaleLocation::getLocationCODEbyID($this->currentGeo['CITY']['ID']);
					break;
			}
			if (empty($value)) {
				foreach ($this->request as $key => $val) {
					if (strtolower($key) == strtolower($prop->getField('CODE'))) {
						$value = $val;
					}
				}
			}
			if (empty($value) || $value == '') 
			{
				if (is_array($this->user))
				{
					switch (strtolower($prop->GetField('CODE')))
					{
						case 'fio':
							$value = $this->user['NAME'] . ' ' . $this->user['LAST_NAME'];
							break;
						case 'phone':
							$value = (!empty($this->user['PERSONAL_PHONE'])) ? $this->user['PERSONAL_PHONE'] : $this->user['WORK_PHONE'];
							break;
						case 'email':
							$value = $this->user['EMAIL'];
							break;
					}
				}
				else
				{
					$value = $prop->getProperty()['DEFAULT_VALUE'];
				}
			}

			if (!empty($value)) {
				$prop->setValue($value);
			}
		}
        
        $count = 1;
         if (!isset($this->request['check']) && $count == 1)
        {
            foreach ($this->profile as $key => $value) {
                if ($count > 1) continue;
                $this->currentProfile = $key;
                foreach ($value['PROPS'] as $val)
                {
                    foreach ($this->order->getPropertyCollection() as $prop)
                    {
                        if ($prop->getField('CODE') == $val['PROP_CODE'])
                        {
                            $prop->setValue($val['VALUE']);
                        }
                    }
                }
            $count++;
            }
            
        }
	}

    protected function doAction($action, $id)
    {
        $siteId = \Bitrix\Main\Context::getCurrent()->getSite();
        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(
                \CSaleBasket::GetBasketUserID(), 
                $siteId
            );
        $basketItems = $basket->getBasketItems();
        $quantity = 1;
        foreach ($basketItems as $basketItem)
        {
            if ($basketItem->getProductId() == $id)
            {    
                switch ($action)
                {
                    case 'plus':
                        $basketItem->setField('QUANTITY', $basketItem->getQuantity() + $quantity);
                        break;
                    case 'minus':
                        $basketItem->setField('QUANTITY', $basketItem->getQuantity() - $quantity);
                        break;
                     case 'set_quantity':
                        $prod = CCatalogProduct::GetByID($id);
                        $basketItem->setField('QUANTITY', $this->request['set_quantity'] > $prod['QUANTITY'] ? $prod['QUANTITY'] : $this->request['set_quantity']);
                        break;
                    case 'delete':
                        $basketItem->delete();
                        break;

                }
                //$basketItem->save();
                break;
            }
        }
        $basket->save();
    }

	function executeComponent()
	{
		global $USER;
        if ($this->request['basket_action'] == 'Y')
        {
            $this->doAction($this->request['basket_action_type'], $this->request['basket_action_item_id']);
        }
        $this->createVirtualOrder();
		if (isset($this->request['save']) && $this->request['save'] == 'Y' && $this->canSaveOrder)
		{
			if (isset($this->request['save']) && $this->request['save'] == 'Y' && !$USER->IsAuthorized()) {
				$USER->Register('new_user_'.time(), "", "", "123456", "123456", $this->request['EMAIL']);
				$USER->Authorize($USER->GetID());
				$this->createVirtualOrder();
			}
			foreach ($this->order->getBasket() as $item)
			{
				$basketPropertyCollection = $item->getPropertyCollection(); 
				$basketPropertyCollection->getPropertyValues();
				foreach ($basketPropertyCollection as $propertyItem)
				{
				    if ($propertyItem->getField('CODE') == 'IMAGE')
				    {
				        $propertyItem->delete();
				        break;
				    }
				}
				$basketPropertyCollection->save();
			}
			$this->order->save();
            $this->saveUserProfile();
			$this->saved_order_id = $this->order->GetId();
			$this->getSavedOrder();
		}
		if (isset($this->request['test']) && $this->request['test'] == 'Y')
		{
			$this->getResults();
		}
		if (isset($this->request['ajax']) && $this->request['ajax'] == 'Y')
		{
			$GLOBALS['APPLICATION']->RestartBuffer();
			$this->includeComponentTemplate();
			die();
		} 
		else
		{
			$this->includeComponentTemplate();
		}
	}
 
}