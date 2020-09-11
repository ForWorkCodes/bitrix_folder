<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$site = ($_REQUEST["site"] <> '' ? $_REQUEST["site"] : ($_REQUEST["src_site"] <> '' ? $_REQUEST["src_site"] : false));
$arFilter = Array("TYPE_ID" => "FEEDBACK_FORM", "ACTIVE" => "Y");
if ($site !== false)
    $arFilter["LID"] = $site;

$arEvent = Array();
$dbType = CEventMessage::GetList($by = "ID", $order = "DESC", $arFilter);
while ($arType = $dbType->GetNext())
    $arEvent[ $arType["ID"] ] = "[" . $arType["ID"] . "] " . $arType["SUBJECT"];

$arComponentParameters = array(
    "GROUPS"     => array(
        "CUSTOM" => array(
            "NAME" => "Текстовая информация",
            "SORT" => 100
        )
    ),
    "PARAMETERS" => array(
        "USE_CAPTCHA"     => Array(
            "NAME"    => GetMessage("MFP_CAPTCHA"),
            "TYPE"    => "CHECKBOX",
            "DEFAULT" => "Y",
            "PARENT"  => "BASE",
        ),
        "HEADER_FORM"     => Array(
            "NAME"    => "Заголовок формы",
            "TYPE"    => "STRING",
            "DEFAULT" => "Сделать заказ",
            "PARENT"  => "CUSTOM"
        ),
        "SUBMIT_TEXT"     => array(
            "NAME"    => "Текст для кнопки отправки",
            "TYPE"    => "STRING",
            "DEFAULT" => "Отправить",
            "PARENT"  => "CUSTOM"
        ),
        "OK_TEXT"         => Array(
            "NAME"    => GetMessage("MFP_OK_MESSAGE"),
            "TYPE"    => "STRING",
            "DEFAULT" => GetMessage("MFP_OK_TEXT"),
            "PARENT"  => "BASE",
        ),
        "EMAIL_TO"        => Array(
            "NAME"    => GetMessage("MFP_EMAIL_TO"),
            "TYPE"    => "STRING",
            "DEFAULT" => htmlspecialcharsbx(COption::GetOptionString("main", "email_from")),
            "PARENT"  => "BASE",
        ),
        "REQUIRED_FIELDS" => Array(
            "NAME"     => GetMessage("MFP_REQUIRED_FIELDS"),
            "TYPE"     => "LIST",
            "MULTIPLE" => "Y",
            "VALUES"   => Array("NONE" => GetMessage("MFP_ALL_REQ"), "NAME" => GetMessage("MFP_NAME"), "EMAIL" => "E-mail", "MESSAGE" => GetMessage("MFP_MESSAGE"), "PHONE" => GetMessage("MFP_PHONE")),
            "DEFAULT"  => "",
            "COLS"     => 25,
            "PARENT"   => "BASE",
        ),

        "EVENT_MESSAGE_ID" => Array(
            "NAME"     => GetMessage("MFP_EMAIL_TEMPLATES"),
            "TYPE"     => "LIST",
            "VALUES"   => $arEvent,
            "DEFAULT"  => "",
            "MULTIPLE" => "Y",
            "COLS"     => 25,
            "PARENT"   => "BASE",
        ),
        "AJAX_MODE"        => array()
    )
);