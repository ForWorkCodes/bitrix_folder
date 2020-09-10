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
 	public $profile = '';

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
						'success' => 'N',
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
			if ($value['REQUIRED'] == 'Y')
			{
				if (isset($this->request['save']))
					$this->checkers[$value['CODE']] = $this->testField($value['VALUE'][0], $value['CODE']);
				else
					$this->checkers[$value['CODE']] = array(
						'success'	=> 'Y',
						'reason'	=> 'Начало пути'
					);
				switch($value['CODE'])
				{
					case 'INDEX':
						if (!isset($this->request['forceOpen']) || $this->request['forceOpen'] == '')
						{
							$this->blocks['region_block'] = $this->testField($value['VALUE'][0], $value['CODE'])['success'];
							if (!isset($this->request['forceOpen']))
							{
								$this->forceOpen = 'forceOpen';	
							}
						}
						else
						{
							$this->blocks['region_block'] = 'N';
							$this->forceOpen = $this->request['forceOpen'];
						}
						break;
					case 'FIO':
					case 'PHONE':
					case 'EMAIL':
					case 'ADDRESS':
						if (!isset($this->blocks['personal_block']) || $this->blocks['personal_block'] == 'Y')
						{
							$this->blocks['personal_block'] = $this->testField($value['VALUE'][0], $value['CODE'])['success'];
						}
						break;
				}
			}
			else
			{
				$this->checkers[$value['CODE']] = 'Y';	
			}
		}
		foreach ($this->checkers as $key => $value) {

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
			$shipmentItemCollection = $shipment->getShipmentItemCollection();
			$shipment->setField('CURRENCY', $this->order->getCurrency());
 
			foreach ($this->order->getBasket()->getOrderableItems() as $item) {
				/**
				 * @var $item \Bitrix\Sale\BasketItem
				 * @var $shipmentItem \Bitrix\Sale\ShipmentItem
				 * @var $item \Bitrix\Sale\BasketItem
				 */
				$shipmentItem = $shipmentItemCollection->createItem($item);
				$shipmentItem->setQuantity($item->getQuantity());
			}
			$this->getPaySystems();
			if (isset($this->request['payment_type'])) {
				$this->default_paySystem = $this->request['payment_type'];
			}
			$paymentCollection = $this->order->getPaymentCollection();
			$payment = $paymentCollection->createItem(
				Bitrix\Sale\PaySystem\Manager::getObjectById(intval($this->default_paySystem))
			);
			$payment->setField("SUM", $this->order->getPrice());
			$payment->setField("CURRENCY", $this->order->getCurrency());
			
			$this->getUser();
			$this->setUserLocation();
			$this->getLocation();
			$this->setOrderProps();
			$this->settingsBlocks();

		} catch (\Exception $e) {
			$this->errors[] = $e->getMessage();
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
			$this->debug[] = array(
				'ip' => $ipAddress,
				'loc' => $locCode,
			);
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
			$tmp = '';
			while($item = $res->fetch())
			{
				if ($item['I_TYPE_CODE'] != 'COUNTRY')
				{
					$this->currentGeo[$item['I_TYPE_CODE']]['ID'] = $item['I_ID'];
					$this->currentGeo[$item['I_TYPE_CODE']]['NAME'] = $item['I_NAME_RU'];
					$this->currentGeo[$item['I_TYPE_CODE']]['PARENT_ID'] = $tmp;
					$tmp = $item['I_ID'];
				}
			    $this->debug[] = $item;
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
			$this->currentGeo['REGION']['ID'] = $this->request['LOCATION_REGION'];
			$this->currentGeo['REGION']['PARENT_ID'] = $this->currentGeo['COUNTRY_DISTRICT']['ID'];
		}
		if (isset($this->request['LOCATION_CITY']))
		{
			$this->currentGeo['CITY']['ID'] = $this->request['LOCATION_CITY'];
			$this->currentGeo['CITY']['PARENT_ID'] = $this->currentGeo['REGION']['ID'];
		}
		$res = \Bitrix\Sale\Location\LocationTable::getList(array(
		    'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID),
		    'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
		));
		while($item = $res->fetch())
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
						}
						else
						{
							if ($this->currentGeo['CITY']['PARENT_ID'] != $item['PARENT_ID'])
							{
								$this->currentGeo['CITY']['ID'] = $item['ID'];
								$this->currentGeo['CITY']['PARENT_ID'] = $item['PARENT_ID'];
							}
						}
						if ($item['ID'] == $this->currentGeo['CITY']['ID'])
						{
							$this->currentGeo['CITY']['NAME'] = $item['NAME_RU'];
						}
						$this->locations[] = $item;
					}
					break;
				default:
					break;
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
		   $this->profile[] = $ar_sales;
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
 		//$this->debug['user'] = $arUser;
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
	}

	// protected function getUser()
	// {
	// 	global $USER;
	// 	$arUser = CSaleOrderUserProps::getList(array(), array('ID' => intval($USER->GetID())), false, false, array())->GetNext();
	// 	$this->user = $arUser;
	// }

	public function getResults()
	{
		print_r($this->request);
	}

	function executeComponent()
	{
		$this->createVirtualOrder();
		if (isset($this->request['save']) && $this->request['save'] == 'Y')
		{
			$this->order->save();
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
			// $this->getResults();
			die();
		} 
		else
		{
			$this->includeComponentTemplate();
		}
	}
 
}