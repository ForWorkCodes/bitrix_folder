<?
use YandexCheckout\Client;

require_once 'main_class.php';

class push_pay extends main_class
{
	/*
	Пройти про всем созданным запросам на оплату подписки и сделать запрос на оплату
	*/

	public function StartMoveToPay()
	{
		CModule::IncludeModule('highloadblock');
		$entity_data_class = $this->GetHBConnect($this->HBIDOrder);

		$rsData = $entity_data_class::getList([
			'select' => ['*'],
			'order' => [],
			'filter' => ['!UF_ORDER_ID' => '', 'UF_ACTIVE' => '1', 'UF_PAID' => '']
		]);

		while ($arData = $rsData->Fetch())
		{
			sleep('1');
			$this->PushPay($arData['UF_ORDER_ID'], $arData['UF_ID_USER']);
		}
	}

	private function PushPay($order_id, $user_id)
	{
		CModule::IncludeModule('sale');
		if (!$order = \Bitrix\Sale\Order::load($order_id)) return;
		$pay_sys_id = end($order->getPaymentSystemId());
		$arPaymentsCollection = $order->loadPaymentCollection();
		$currentPaymentOrder = $arPaymentsCollection->current();
		$pay_id = $currentPaymentOrder->getField("ID");

		$price = $order->getPrice();

		if ((float)$price <= 0)
		{
			$finalPrice = 1;
		}
		else
		{
			$finalPrice = $price;
		}

		$dataOrder = [
			'CURRECY' => $order->getCurrency(),
			'PRICE' => $finalPrice
		];

		$this->YandexPush($pay_id, $order_id, $dataOrder, $pay_sys_id, $user_id);
	}

	private function YandexPush($pay_id, $order_id, $dataOrder, $pay_sys_id, $user_id)
	{
		// Получение данных платежной системы нужно переделать
		global $DB;
		$STH = $DB->query('SELECT * FROM b_sale_bizval WHERE CONSUMER_KEY = "PAYSYSTEM_'. $pay_sys_id . '"');
		while ($part = $STH->fetch())
		{
			if ($part['CODE_KEY'] == 'YANDEX_CHECKOUT_SECRET_KEY')
			{
				$secret_key = $part['PROVIDER_VALUE'];
			}
			if ($part['CODE_KEY'] == 'YANDEX_CHECKOUT_SHOP_ID')
			{
				$shop_id = $part['PROVIDER_VALUE'];
			}
		}

		if (!$secret_key || !$shop_id)
		{
			$text = 'empty SHOP_ID or SECRET_CODE, payment attempt error';
			$log = [
				'UF_ID_USER' => $user_id,
				'UF_ERROR_PAY' => '1',
				'UF_ORDER_ID' => $order_id,
				'UF_NOW_DATE' => date(),
				'UF_TEXT' => $text,
				'UF_FATAL' => '1'
			];
			$this->PushToLog($log);
			return;
		}

		$yandex_id = $this->GetUserYandexID($user_id);

		if (!$yandex_id)
		{
			$text = 'Error connect to yandex. Users empty personal key from yandex';
			$log = [
				'UF_ID_USER' => $user_id,
				'UF_ERROR_PAY' => '1',
				'UF_ORDER_ID' => $order_id,
				'UF_NOW_DATE' => date(),
				'UF_TEXT' => $text,
				'UF_FATAL' => '1'
			];
			$this->PushToLog($log);
			return;
		}

		$client = new Client();
    	$client->setAuth($shop_id, $secret_key);

    	try
    	{
	    	$result = $client->createPayment(
		        array(
		            'description' => 'Заказ №'.$order_id,
		            'amount' => array(
		                'value' => $dataOrder['PRICE'],
		                'currency' => $dataOrder['CURRECY'],
		            ),
					'capture' => true,
		            "payment_method_id" => $yandex_id,
		            'metadata' => array(
		            	"ORDER_WITH_SUBS" => "1",
		            	"BX_ORDER_NUMBER" => $order_id,
		            	"BX_PAYMENT_NUMBER" => $pay_id,
						"BX_PAYSYSTEM_CODE" => $pay_sys_id,
						"BX_HANDLER" => "YANDEX_CHECKOUT",
						"cms_name" => "api_1c-bitrix"
		            ),
		        ),
		        uniqid('', true)
		    );
    	}
    	catch(Exception $e)
    	{
    		$text = $e->getMessage();
    		// $text = 'Error connect to yandex. Users personal key from yandex is invalid';
			$log = [
				'UF_ID_USER' => $user_id,
				'UF_ERROR_PAY' => '1',
				'UF_ORDER_ID' => $order_id,
				'UF_NOW_DATE' => date(),
				'UF_TEXT' => $text,
				'UF_FATAL' => '1'
			];
			$this->PushToLog($log);
			return;
    	}
	}

	private function GetUserYandexID($user_id)
	{

		$entity_data_class = $this->GetHBConnect($this->HBPay);

		$rsData = $entity_data_class::getList([
			'select' => ['*'],
			'order' => [],
			'filter' => ['UF_ID_USER' => $user_id]
		]);

		while ($arData = $rsData->Fetch())
		{
			$UF_ID_PAY = $arData['UF_ID_PAY'];
		}

		if ($UF_ID_PAY)
			return $UF_ID_PAY;
		else
			return false;

	}
}