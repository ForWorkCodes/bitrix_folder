<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/**
 * @global array $arParams
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global string $cartId
 */?>
<a href="<?= $arParams['PATH_TO_BASKET'] ?>">
    <div class="basketIco">
        <? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/img/svg/cart.html', array(), array())?>
        <div class="basket-counter"><?=$arResult['NUM_PRODUCTS'] ?></div>
    </div>
</a>
<div class="basketInfo">
    <div class="basketInfo__title">
        <a href="<?= $arParams['PATH_TO_BASKET'] ?>">Моя корзина</a>
    </div>
    <a class="basketInfo__totalSum" href="<?= $arParams['PATH_TO_BASKET'] ?>"><?=str_replace(' руб.', '', $arResult['TOTAL_PRICE']) ?> ₽</a>
</div>