<?$APPLICATION->IncludeComponent(
	"bitrix:sale.basket.basket.line", 
	".default", 
	array(
		"PATH_TO_BASKET" => SITE_DIR."personal/cart/",
		"PATH_TO_PERSONAL" => SITE_DIR."personal/",
		"SHOW_PERSONAL_LINK" => "N",
		"SHOW_NUM_PRODUCTS" => "Y",
		"SHOW_TOTAL_PRICE" => "N",
		"SHOW_PRODUCTS" => "N",
		"POSITION_FIXED" => "N",
		"SHOW_AUTHOR" => "N",
		"PATH_TO_REGISTER" => SITE_DIR."login/",
		"PATH_TO_PROFILE" => SITE_DIR."personal/",
		"COMPONENT_TEMPLATE" => ".default",
		"PATH_TO_ORDER" => SITE_DIR."personal/order/make/",
		"SHOW_EMPTY_VALUES" => "Y",
		"HIDE_ON_BASKET_PAGES" => "N",
		"PATH_TO_AUTHORIZE" => "",
		"SHOW_REGISTRATION" => "N"
	),
	false
);?>