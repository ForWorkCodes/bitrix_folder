<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
    "NAME"        => "Оформление заказа",
    "DESCRIPTION" => "Собственное оформление заказа с корзиной",
    "PATH"        => array(
        "ID"    => "PLASTILIN",
        "NAME"=>'Кастомные компоненты',
        "CHILD" => array(
            "ID"   => 'ORDER_AJAX',
            "NAME" => "Оформление"
            )
        ),
    "CACHE_PATH"  => "Y"
    );