<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
    "NAME"        => "Форма с 4 полями",
    "DESCRIPTION" => "Форма отправки с телефоном, email",
    "PATH"        => array(
        "ID" => "CUSTOM",
        'NAME' => 'Кастомные компоненты',
        "CHILD" => array(
            "ID"=>'forms',
            "NAME" => "Формы отправки"
        )
    ),
);