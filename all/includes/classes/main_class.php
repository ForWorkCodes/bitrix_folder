<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Config\Option;
CModule::IncludeModule('iblock');

class main_class
{
	protected $iblock_trun;
	protected $obSections;
	protected $obElements;
	protected $HBPay;
	protected $HBID;
	protected $HBIDLog;
	protected $HBIDOrder;
	protected $id_subs_empty;
	protected $id_subs_price;
	protected $subs_count_limit;
	protected $subs_count_day;
	protected $subs_text_new_user;

	public function __construct()
	{
		$this->iblock_trun = Option::get('plastilin.settings', "turn_order");
		if (!$this->iblock_trun) return;
		
		$this->obElements = new \CIBlockElement();
		$this->obSections = new \CIBlockSection();
		$this->HBID = 5;
		$this->HBIDLog = 6;
		$this->HBIDOrder = 7;
		$this->HBPay = 10;
		$this->id_subs_empty = Option::get('plastilin.settings', "subs_empty_id");
		$this->id_subs_price = Option::get('plastilin.settings', "price_turn");
		$this->subs_count_limit = Option::get('plastilin.settings', "subs_count_limit");
		$this->subs_count_day = Option::get('plastilin.settings', "subs_count_day");
		$this->subs_text_new_user = Option::get('plastilin.settings', "subs_text_new_user");
	}

	protected function GetMainSectionUser($user_id)
	{
		$obSection = $this->obSections->GetList(
			[],
			['IBLOCK_ID' => $this->iblock_trun, 'ACTIVE' => 'Y', 'DEPTH_LEVEL' => 1, 'UF_ID_USER' => $user_id],
			false,
			[],
			[]
		);
		while ($arSection = $obSection->GetNext())
		{
			$id_section = $arSection['ID'];
		}

		return $id_section;
	}

	public function GetMonthUser($user_id)
	{
		$id_section = $this->GetMainSectionUser($user_id);

		if (!$id_section) return;

		unset($obSection);
		unset($arSection);
		$obSection = $this->obSections->GetList(
			[],
			['SECTION_ID' => $id_section, 'ACTIVE' => 'Y', 'IBLOCK_ID' => $this->iblock_trun],
			false,
			['*', 'UF_*'],
			[]
		);

		while ($arSection = $obSection->GetNext())
		{
			$arMonth[$arSection['ID']] = $arSection;
		}

		return $arMonth;
	}

	protected function GetDataUser($user_id)
	{
		$arFilter = array("ID" => $user_id);
		$arParams["SELECT"] = array("*", "UF_DATE_SUBSCRIPTION", "UF_GENDER");

		$obRes = CUser::GetList($by,$desc,$arFilter, $arParams);
		while ($arRes = $obRes->Fetch())
		{
			$data = $arRes;
			return $data;
		}
	}

	protected function GetDateSubscribe($user_id)
	{
		$arFilter = array("ID" => $user_id);
		$arParams["SELECT"] = array("*", "UF_DATE_SUBSCRIPTION");

		$obRes = CUser::GetList($by,$desc,$arFilter, $arParams);
		while ($arRes = $obRes->Fetch())
		{
			$date = new \DateTime($arRes['UF_DATE_SUBSCRIPTION']);
			return $date;
		}
	}

	protected function GetHBConnect($hlbl)
	{
		CModule::IncludeModule('highloadblock');
		$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch(); 
		$entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock); 
		$entity_data_class = $entity->getDataClass();
		if ($entity_data_class)
		{
			return $entity_data_class;
		}
	}

	protected function GetIDSubs($user_id)
	{
		$arFilter = array("ID" => $user_id);
		$arParams["SELECT"] = array("*", "UF_SUBSCRIBE");

		$obRes = CUser::GetList($by,$desc,$arFilter, $arParams);
		while ($arRes = $obRes->Fetch())
		{
			$id_subs = $arRes['UF_SUBSCRIBE'];
			return $id_subs;
		}
	}

	public function UserIsSubs($user_id)
	{
		$id_subs = $this->GetIDSubs($user_id);
		if ($id_subs != '' && $id_subs != $this->id_subs_empty)
			return true;
		else
			return false;
	}

	protected function PushMessageToUser($text, $user_id)
	{
		$arFilter = array("ID" => $user_id);
		$arParams["SELECT"] = array("*", "UF_WARNING");

		$obRes = CUser::GetList($by,$desc,$arFilter, $arParams);
		while ($arRes = $obRes->Fetch())
		{
			$UF_WARNING = $arRes['UF_WARNING'];
		}
		$UF_WARNING[] = $text;
		$user = new \CUser;
		$user->Update($user_id, ['UF_WARNING' => $UF_WARNING]);
	}

	protected function PushToLog($log)
	{
		$entity_data_class = $this->GetHBConnect($this->HBIDLog);

		// Массив полей для добавления
		if (empty($log['UF_NOW_DATE']))
		{
			$date = new \DateTime();
			$log['UF_NOW_DATE'] = $date->format('d.m.Y H:i:s');
		}

		$data = $log;
		$result = $entity_data_class::add($data);

		if ($log['UF_FATAL'])
		{
			$data['ID_LOG'] = $result->getId();
			$this->FatalError($data);
		}
	}

	protected function FatalError($log)
	{
		$arFilter = array("ID" => 1);

		$site = \Bitrix\Main\Config\Option::get('main', 'site_name');
		$obRes = CUser::GetList($by,$desc,$arFilter, $arParams);
		while ($arRes = $obRes->Fetch())
		{
			$email = $arRes['EMAIL'];
		}

		$arFilter = array("ID" => $log['UF_ID_USER']);
		$obRes = CUser::GetList($by,$desc,$arFilter, $arParams);
		while ($arRes = $obRes->Fetch())
		{
			$email_user = $arRes['EMAIL'];
		}

		/* Пушинг админу */
		\Bitrix\Main\Mail\Event::send(array(
		    "EVENT_NAME" => "FATAL_SUBS",
		    "LID" => "s1",
		    "C_FIELDS" => array(
		    	"SITE_NAME" => $site,
		        "EMAIL_TO" => $email,
		        "ID_LOG" => $log['ID_LOG'],
		        "TEXT" => $log['UF_TEXT'],
		        "ID_USER" => $log['UF_ID_USER'],
		        "ERROR_ORDER" => $log['UF_ORDER_ERROR'],
		        "ERROR_PAY" => $log['UF_ERROR_PAY']
		    ),
		));
		/* -Пушинг админу- */

		/* Пушинг пользователю */
		\Bitrix\Main\Mail\Event::send(array(
		    "EVENT_NAME" => "FATAL_SUBS_TO_USER",
		    "LID" => "s1",
		    "C_FIELDS" => array(
		    	"SITE_NAME" => $site,
		        "EMAIL_TO" => $email_user,
		        "ID_LOG" => $log['ID_LOG'],
		        "TEXT" => $log['UF_TEXT'],
		        "ID_USER" => $log['UF_ID_USER'],
		        "ERROR_ORDER" => $log['UF_ORDER_ERROR'],
		        "ERROR_PAY" => $log['UF_ERROR_PAY']
		    ),
		));
		/* -Пушинг пользователю- */
	}

	public function Create_new_turn($arSection = [], $parent_id)
	{
		if (!empty($arSection))
		{
			$year = $arSection['UF_YEAR'];
			$month = $arSection['UF_MONTH'];
			$stringTime = "01.".$month.".".$year." 00:00:00";
		}
		$objDateTime = new \Bitrix\Main\Type\DateTime($stringTime);
		if (!empty($arSection)) $objDateTime->add('1 month');

		$newYear = $objDateTime->format('Y');
		$newMonth = $objDateTime->format('m');
		$newMonthText = GetMessage('MON_'.$newMonth);

		$fieldSe = [
			'IBLOCK_ID' => $this->iblock_trun,
			'ACTIVE' => 'Y',
			'NAME' => $newMonth . ' ' . $newYear,
			'IBLOCK_SECTION_ID' => $parent_id,
			'UF_YEAR' => $newYear,
			'UF_MONTH' => $newMonth
		];
		$id_section = $this->obSections->Add($fieldSe);

		if ($id_section)
			return $id_section;
	}
	
	public function Create_element_in_turn($id_parent, $to_section)
	{
		CModule::IncludeModule('plastilin.settings');
		$arItem = \plastilin\settings\MainPlastilin::GetElement($id_parent);
		$PROP['ID_ITEM'] = $arItem['ID'];
		$fieldEl = [
			'ACTIVE' => 'Y',
			'IBLOCK_ID' => $this->iblock_trun,
			'NAME' => $arItem['NAME'],
			'PROPERTY_VALUES' => $PROP,
			'IBLOCK_SECTION_ID' => $to_section
		];
		$id_new_el = $this->obElements->Add($fieldEl);
		return $id_new_el;
	}

}