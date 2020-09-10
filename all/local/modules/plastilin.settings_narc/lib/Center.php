<?
namespace plastilin\settings;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;

class Center
{
    protected $obSections;
    protected $obElements;
    protected $module_id;
    public $user_id;
    public $currency;
    public $price_id;
    protected $HBIDLog;
    protected $catalog_id;
    public $LIST_ERROR;

    function __construct()
    {
    	global $USER;
        $this->obElements = new \CIBlockElement();
        $this->obSections = new \CIBlockSection();
        $this->module_id = pathinfo(dirname(__DIR__))["basename"];
        $this->user_id = $USER->GetID();
        $this->HBIDLog = Option::get($this->module_id, "id_log");
        $this->catalog_id = Option::get($this->module_id, "Iblock_catalog");
        $this->currency = Option::get($this->module_id, "currency");
        $this->price_id = Option::get($this->module_id, "price_id");
    }

    public function GetSections($section_id = '', $iblock_id = '', $show_count = '')
    {
        if (empty($section_id) && empty($iblock_id)) return;
        Loader::includeModule('iblock');
        $arFilter = [];
        $arNav = [];
        $arFilter['ACTIVE'] = 'Y';

        if ($idParent)
        {
            $arFilter['SECTION_ID'] = $section_id;
        }
        if ($iblock)
        {
            $arFilter['IBLOCK_ID'] = $iblock_id;
        }
        if ($show_count)
        {
            $arNav['nTopCount'] = $show_count;
        }

        $obSections = $this->obElements->GetList(
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

    protected function GetHBConnect($hlbl)
    {
        \CModule::IncludeModule('highloadblock');
        if ($obHlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl))
        {
            $hlblock = $obHlblock->fetch();
            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock); 
            $entity_data_class = $entity->getDataClass();
            if ($entity_data_class)
            {
                return $entity_data_class;
            }
        }
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
    }

    public function GetCatalogId()
    {
        return $this->catalog_id;
    }

}
?>