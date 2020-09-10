<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var customOrderComponent $component */
use \Bitrix\Sale\BasketItemBase;
?>


<? if(intval($component->saved_order_id) > 0) : ?>
	<script type="text/javascript">
		location = 'spasibo/?ORDER_ID=<?=$component->saved_order_id?>';
        // console.log('DA');
	</script>
<? else: ?>
	<? if (count($component->order->getBasket()) > 0) : ?>
		<? 
            // prr($component->order->getField('USER_ID'));
			foreach ($component->services as $service)
			{
				if ($service['ID'] == 1) continue;
				if ($service['ID'] == $component->default_service) // Проверка, если выбранная доставка
				{
					$selected_service = $service;
				}
			}
		?>

		<?
			foreach ($component->order->getPaymentCollection() as $payment):
				$selected_paySystem = $payment->getPaySystem();
			endforeach;
            $discounts = $component->order->getDiscount();
            $showPrices = $discounts->getShowPrices();
            if (!empty($showPrices['BASKET']))
            {
                foreach ($showPrices['BASKET'] as $basketCode => $data)
                {
                    $basketItem = $component->order->getBasket()->getItemByBasketCode($basketCode);
                    if ($basketItem instanceof \Bitrix\Sale\BasketItemBase)
                    {
                        $basketItem->setFieldNoDemand('BASE_PRICE', $data['SHOW_BASE_PRICE']);
                        $basketItem->setFieldNoDemand('DISCOUNT_PRICE', $data['SHOW_DISCOUNT']);
                        $basketItem->setFieldNoDemand('DISCOUNT_VALUE', $data['SHOW_DISCOUNT_PERCENT']);
                    }
                }
            }
            //prr($component->debug);
		?>
        <form action="<?= $APPLICATION->GetCurPage(); ?>" method="post" id="form_order" data-container="form_order_container">
            
            <div class="table-background">
                    <div class="response-tbl">
                        <table class="basket-table">
                            <tr>
                                <th colspan="2">Товары</th>
                                <th>Скидка</th>
                                <th>Цена</th>
                                <th>Сумма</th>
                                <th colspan="2">Количество</th>
                            </tr>
                            <? 
                                foreach ($component->order->getBasket() as $arItem):
                                $props = $arItem->getPropertyCollection()->getPropertyValues();
                                
                            ?>
                                <tr class="item-container">
                                    
                                    <?
                                    //костыль для получения картинки торгового предложения (если нет картинки) из родительского товара
                                    $srcImg = $props['IMAGE']['VALUE'];
                                    if ($srcImg == "") {
                                        $mxResult = CCatalogSku::GetProductInfo($arItem->getField('PRODUCT_ID'));
                                        if (is_array($mxResult))
                                        {
                                            $res = CIBlockElement::GetByID($mxResult["ID"]);
                                            if ($arElem = $res->GetNext()) {
                                                $pictID = "";
                                                if ($arElem["PREVIEW_PICTURE"] != "") {
                                                    $pictID = $arElem["PREVIEW_PICTURE"];
                                                } elseif ($arElem["DETAIL_PICTURE"] != "") {
                                                    $pictID = $arElem["DETAIL_PICTURE"];
                                                }
                                                if ($pictID != "") {
                                                    $arFile = CFile::GetFileArray($pictID);
                                                    if ($arFile) {
                                                        $srcImg = $arFile["SRC"];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    //получение ссылки на товар
                                    $urlProd = "";
                                    $res = CCatalogSku::GetProductInfo($arItem->getField('PRODUCT_ID'));
                                    $resProd = CIBlockElement::GetByID($res["ID"]);
                                    if ($arProd = $resProd->GetNext()) {
                                        $urlProd = $arProd["DETAIL_PAGE_URL"];
                                    }
                                    if ($urlProd == "") {
                                        $resProd = CIBlockElement::GetByID($arItem->getField('PRODUCT_ID'));
                                        if ($arProd = $resProd->GetNext()) {
                                            $urlProd = $arProd["DETAIL_PAGE_URL"];
                                        }
                                    }
                                    //конец костыля
                                    ?>
                                    
                                    <td class="image-view">
                                        <div class="table-image">
                                            <img src="<?= $srcImg ?>" alt="" width="100" height="auto">
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?=$urlProd?>"><p><?= $arItem->getField('NAME') ?></p></a>
                                    </td>
                                    <td><?= $arItem->getField('DISCOUNT_VALUE') ?>%</td>
                                    <td>
                                        <span class="bold-text size-text"><?= round($arItem->getField('PRICE'),2) ?><span class="rub-text">₽</span></span>
                                        <? if ($arItem->getField('DISCOUNT_PRICE') > 0) : ?>
                                            <p><del><span class="rub-text size-text"><?= round($arItem->getField('BASE_PRICE'), 2) ?> ₽</span></del></p>
                                        <? endif ?>
                                    </td>
                                    <td>
                                        <span class="bold-text size-text"><?= $arItem->getFinalPrice(); ?><span class="rub-text">₽</span></span>
                                    </td>
                                    <td>
                                        <div class="input-group custom-plus-min">
                                            <span class="input-group-btn">
                                                <button class="btn btn-danger basket-action" data-action="minus" type="button"><span>-</span></button>
                                            </span>
                                            <input type="text" class="form-control" data-field="quantity" data-action="set_quantity" value="<?= $arItem->getQuantity(); ?>">
                                            <span class="input-group-btn">
                                                <button class="btn btn-success basket-action" data-action="plus" type="button"><span>+</span></button>
                                            </span>
                                            <input type="hidden" data-id="<?= $arItem->getProductId(); ?>">
                                        </div>
                                    </td>
                                    <td class="last-col">
                                        <div class="basket-action" data-action="delete">
                                            <span>
                                                <object type="image/svg+xml" data="<?=SITE_TEMPLATE_PATH ?>/images/icons/close.svg">SVG</object>
                                            </span>
                                            <span class="table-delete">Удалить</span>
                                        </div>
                                    </td>                                    
                                </tr>
                            <? endforeach ?>
                        </table>
                    </div>
                    <div class="bet-flex-cen bot-table">
                        <div class="cupon-place">
                            <p>Введите код купона для скидки:</p>
                            <div class="form-sub-cupon">
                                <input type="text" name="coupon" value="" placeholder="">
                                <input class="typeSubmit" type="submit" value="Применить">
                            </div>
                            <? if (count($component->couponList) > 0) : ?>
                                <p>Список купонов:</p>
                                <? foreach ($component->couponList as $key => $value) : ?>
                                    <p> <?= $key ?> - <span class="coupon-status <?= $value['MODE'] == 1 ? 'red' : '' ?> <?= $value['STATUS'] == 4 ? 'green' : '' ?>"> <?=$value['STATUS_TEXT']?></span>
                                        <span data-code="<?= $key ?>" class="delete_coupon">
                                            Удалить
                                        </span>
                                    </p>
                                <? endforeach ?>
                                <input type="hidden" value="" name="DEL_COUPON">
                            <? endif ?>
                        </div>
                        <div class="result-price">
                            <span>Итого: </span>
                            <span class="price-place">
                                <span class="price"><?=$component->order->getBasket()->getPrice()?></span>
                                <span class="rub">₽</span>
                            </span>
                        </div>
                    </div>
                </div>
            
            <div class="order-info">
                <p class="basket-title">Информация для оплаты и доставки заказа</p>
                <? if (!empty($component->profile)) : ?>
                    <p class="input-title">Профиль покупателя</p>
                    <select name="user_profile_id" class="customer-select-2 select2">
                        <? foreach ($component->profile as $k => $prof) : ?>
                            <option value="<?=$k?>" <?= $k == $component->currentProfile ? 'selected' : ''; ?>><?=!empty($prof['NAME']) ? $prof['NAME'] : 'Без имени' ?></option>
                        <? endforeach ?>
                    </select>
                <? else : ?>
                    <input type="hidden" name="user_profile_id" value="" ?>
                <? endif ?>
            </div>
            <div class="customer-info">
                <div class="box-title">
                    <p>Информация о покупателе</p>
                    <p class="roll">Свернуть <i class="fas fa-angle-down"></i></p>
                </div>
                <div class="customer-form">
                    <div class="grid-tre">
                        <? foreach ($component->order->getPropertyCollection()->getArray()['properties'] as $key => $prop) : ?>
                            <?
                                if ($prop['TYPE'] != 'LOCATION') 
                                {
                                    if (!empty($prop['RELATION']))
                                    {
                                        $relations = array(
                                            'D',
                                            'P'
                                        );
                                        foreach ($prop['RELATION'] as $relation)
                                        {
                                            $relations[$relation['ENTITY_TYPE']][] = $relation['ENTITY_ID'];
                                        }
                                    }
                                    $in = false;
                                    if (!empty($relations['D']))
                                    {
                                        if (in_array($component->default_service, $relations['D']))
                                        {
                                            $in = true;
                                        }
                                        else
                                        {
                                            $in = false;
                                        }
                                    }
                                    else
                                    {
                                        $in = true;
                                    }
                                    $in2 = false;
                                    if (!empty($relations['P']))
                                    {
                                        if (in_array($component->default_paySystem, $relations['P']))
                                        {
                                            $in2 = true;
                                        }
                                        else
                                        {
                                            $in2 = false;
                                        }
                                    }
                                    else
                                    {
                                        $in2 = true;
                                    }
                                    if (!$in || !$in2) continue;
                                    if ($prop['CODE'] != 'PVZ') :
                                        ?>
                                            <label class="form-input">
                                                <p class="name">
                                                    <?= $prop['NAME'] ?>
                                                    <? if ($prop['REQUIRED'] == 'Y') : ?>
                                                        <span class="red">*</span> 
                                                    <? endif ?> 
                                                </p>
                                                <?
                                                    $val = '';
                                                    if (!empty($prop['VALUE'][0]))
                                                    {
                                                        $val = $prop['VALUE'][0];
                                                    }
                                                    elseif ($prop['TYPE'] == 'NUMBER')
                                                    {
                                                        $val = $prop['MIN'];   
                                                    }

                                                ?>
                                                <input class="mobile-style-input" type="text" name="<?=$prop['CODE'] ?>" value="<?=$val?>" placeholder="<?=$prop['DEFAULT_VALUE']?>">
                                                <? if ($component->checkers[$prop['CODE']]['success'] == 'N') :?>
                                                    <div class="red"><?=$component->checkers[$prop['CODE']]['reason']?></div>
                                                <? endif ?>
                                            </label>
                                        <?
                                    else:
                                        ?>
                                            <!--<label class="form-input">
                                                <p class="name">
                                                    <?= $prop['NAME'] ?>
                                                    <? if ($prop['REQUIRED'] == 'Y') : ?>
                                                        <span class="red">*</span> 
                                                    <? endif ?> 
                                                </p>
                                                <select name="<?=$prop['CODE']?>" class="customer-select-4 select2 basket-select">
                                                    <? foreach ($component->storages as $key => $val) : ?>
                                                        <option <?= $val['SELECTED'] == 'Y' ? 'selected' : '' ?> value="<?= $val['NAME'] ?>">
                                                            <?= $val['NAME'] ?>
                                                        </option>
                                                    <? endforeach ?>
                                                </select>
                                            </label>-->
                                        <?
                                    endif;
                                }
                                else
                                {
                                    ?>
                                        <label class="form-input">
                                            <p class="name">
                                                <?= $prop['NAME'] ?>
                                                <? if ($prop['REQUIRED'] == 'Y') : ?>
                                                    <span class="red">*</span> 
                                                <? endif ?> 
                                            </p>
                                            <select name="LOCATION_COUNTRY_DISTRICT" class="customer-select-3 select2 basket-select location-select">
                                                <? foreach ($component->locations as $key => $val) : ?>
                                                    <? if ($val['TYPE_CODE'] == 'COUNTRY_DISTRICT') : ?>
                                                        <option 
                                                            <?= $val['ID'] == $component->currentGeo['COUNTRY_DISTRICT']['ID'] ? 'selected' : ''; ?> 
                                                            value="<?= $val['ID'] ?>"
                                                        >
                                                            <?= $val['NAME_RU'] ?>
                                                        </option>
                                                    <? endif ?>
                                                <? endforeach ?>
                                            </select>
                                            <select name="LOCATION_REGION" class="customer-select-3 select2 location-select">
                                                <? foreach ($component->locations as $key => $val) : ?>
                                                    <? if ($val['TYPE_CODE'] == 'REGION') : ?>
                                                        <option 
                                                            <?= $val['ID'] == $component->currentGeo['REGION']['ID'] ? 'selected' : ''; ?> 
                                                            value="<?= $val['ID'] ?>"
                                                        >
                                                            <?= $val['NAME_RU'] ?>
                                                        </option>
                                                    <? endif ?>
                                                <? endforeach ?>
                                            </select>
                                            <select name="LOCATION_CITY" class="customer-select-3 select2 location-select">
                                                <? foreach ($component->locations as $key => $val) : ?>
                                                    <? if ($val['TYPE_CODE'] == 'CITY') : ?>
                                                        <option 
                                                            <?= $val['ID'] == $component->currentGeo['CITY']['ID'] ? 'selected' : ''; ?> 
                                                            value="<?= $val['ID'] ?>"
                                                        >
                                                            <?= $val['NAME_RU'] ?>
                                                        </option>
                                                    <? endif ?>
                                                <? endforeach ?>
                                            </select>
                                        </label>
                                    <?
                                }
                            ?>
                        <? endforeach ?>
                    </div>         
                </div>
                
                    <?/*if ($component->default_service == "11") :*/?>
                    <div class="customSelect-wrapper<?if ($component->default_service != "11") :?> hid<?endif?>">
                        <div class="customSelect-title">
                            Самовывоз из магазина
                        </div>
                        <div class="customSelect">
                            <?
                            $bFind = false;
                            $arCurStorage = array();
                            foreach ($component->storages as $stkey => $arStorage) {
                                if ($arStorage['SELECTED'] == 'Y') {
                                    $arCurStorage = $arStorage;
                                    $bFind = true;
                                    break;
                                }
                            }
                            if (!$bFind) {
                                foreach ($component->storages as $stkey => $arStorage) {
                                    if ($arStorage['ALL_BASKET'] == 'Y') {
                                        $arCurStorage = $arStorage;
                                        $bFind = true;
                                        break;
                                    }
                                }
                            }
                            if (!$bFind) {
                                $arCurStorage = $component->storages[0];
                            }
                            ?>
                            <div class="customSelect__item customSelect__item-main">
                                <div class="selectShop">
                                    <?/*if ($arCurStorage['SELECTED'] == 'Y') :*/?>
                                    <div class="customSelect__chk">
                                        <object type="image/svg+xml" data="<?=SITE_TEMPLATE_PATH ?>/images/icons/checked.svg">SVG</object>
                                    </div>
                                    <?/*endif*/?>
                                    <div class="selectShop__first">
                                        <div class="selectShop__title">
                                            <!--<div class="decorateRadio">
                                                <input class="mobile-radio" type="radio" name="radio" id="rad11" checked="true">
                                                <label class="label-city" for="rad11"></label>
                                            </div>-->
                                            <?=$arCurStorage['NAME']?>, <?=$arCurStorage['ADDRESS']?>
                                        </div>
                                        <!--<a class="selectShop__mapLook" href="">Посмотреть на карте</a>-->
                                        <div class="selectShop__info">Тел.: <span class="selectShop__number"><?=$arCurStorage['PHONE']?></span></div>
                                        <div class="selectShop__info operatingMode"><?=$arCurStorage['SCHEDULE']?></div>
                                    </div>
                                    <div class="selectShop__sec">
                                        <div class="takeTime">Забрать можно</div>
                                        <div class="takeTime__date">
                                            <?if ($arCurStorage['ALL_BASKET'] == 'Y') :?>
                                            Сегодня
                                            <?else :?>
                                            Через 2 дня
                                            <?endif?>
                                        </div>
                                    </div>
                                    <div class="selectShop__third">
                                        <div class="selectShop__btn selectShop__btn-collapse">
                                            Развернуть
                                        </div>
                                    </div>
                                    <input type="hidden" name="PVZ" value="<?=$arCurStorage['NAME']?>">
                                </div>
                            </div>
                        	<ul class="customSelect__list">
                        	<?foreach ($component->storages as $stkey => $arStorage) :?>
                            	<li class="customSelect__item">
                                    <div class="selectShop">
                                        <div class="selectShop__first">
                                            <div class="selectShop__title">
                                                <span class="pvz-name"><?=$arStorage['NAME']?></span>, <?=$arStorage['ADDRESS']?>
                                            </div>
                                            <!--<a class="selectShop__mapLook" href="">Посмотреть на карте</a>-->
                                            <div class="selectShop__info">Тел.: <span class="selectShop__number"><?=$arStorage['PHONE']?></span></div>
                                            <div class="selectShop__info operatingMode"><?=$arStorage['SCHEDULE']?></div>
                                        </div>
                                        <div class="selectShop__sec">
                                            <div class="takeTime">Забрать можно</div>
                                            <div class="takeTime__date">
                                                <?if ($arStorage['ALL_BASKET'] == 'Y') :?>
	                                            Сегодня
	                                            <?else :?>
	                                            Через 2 дня
	                                            <?endif?>
	                                            </div>
                                        </div>
                                        <div class="selectShop__third">
                                            <div class="selectShop__btn replaceBtn<?if ($arStorage['ID'] == $arCurStorage['ID']) :?> active<?endif?>">
                                                <div class="hidenBtn">
                                                    <? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/images/checked.html', array(), array())?>
                                                </div>
                                                <?if ($arStorage['ID'] == $arCurStorage['ID']) :?>
                                                    <div class="replaceBtn__title">Выбрано</div>
                                                <?else :?>
												    <div class="replaceBtn__title">Выбрать</div>
                                        	   <?endif?>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?endforeach?>
                        	</ul>
                            
                        </div>
                    </div>
                    <?/*endif*/?>
                 
            </div>
            <div class="customer-info deliv">
                <div class="box-title">
                    <p>Служба доставки</p>
                </div>
                <div class="grid-tre p15 img-input">
                    <? foreach ($component->currendDeliveries as $service): ?>
                        <? if ($service['id'] == 1) continue; ?>
                        <?
                            if ($service['id'] == $component->default_service) // Проверка, если выбранная доставка
                            {
                                $checked = 'checked="checked"';
                                $da = 'da';
                            }
                            else
                            {
                                $checked = '';
                                $da = '';
                            }
                        ?>
                        <input id="deliv_<?=$service['id']?>" class="invision" <?= $checked ?> type="radio" name="delivery_type" value="<?=$service['id']?>">
                        <label class="one-img" for="deliv_<?=$service['id']?>">
                            <div class="da-open <?=$da?>">
                                <object type="image/svg+xml" data="<?=SITE_TEMPLATE_PATH ?>/images/icons/checked.svg">SVG</object>
                            </div>
                            <p class="name"><?= $service['name'] ?></p>
                            <img src="<?= $service['logo_path'] ?>" alt="" class="p30">
                            <p class="desc"> <?= $service['period_text'] ?> </p>
                            <p class="desc"><span class="fz16">Стоимость</span> <span class="_b"><?= $service['price'] ?></span> <span class="_m">₽</span></p>
                        </label>
                    <? endforeach ?>
                </div>
            </div>
            <div class="customer-info pay">
                <div class="box-title">
                    <p>Платежная система</p>
                </div>
                <div class="grid-tre p15 img-input">
                    <? foreach ($component->paySystems as $key => $paySystem) : ?>
                        <?
                            if ($paySystem['ID'] == $component->default_paySystem)
                            {
                                $checked = 'checked="checked"';
                                $da = 'da';
                            }
                            else
                            {
                                $checked = '';
                                $da = '';
                            }
                        ?>
                        <input id="pay_<?=$paySystem['ID']?>" class="invision" type="radio" <?= $checked ?> name="payment_type" value="<?= $paySystem['ID'] ?>">
                        <label class="one-img <?= $da ?>" for="pay_<?=$paySystem['ID']?>">
                            <div class="da-open">
                                <object type="image/svg+xml" data="<?=SITE_TEMPLATE_PATH ?>/images/icons/checked.svg">SVG</object>
                            </div>
                            <p class="name"><?=$paySystem['NAME']?></p>
                            <img src="<?= CFile::GetPath($paySystem['LOGOTIP']) ?>" alt="" class="pt30">
                        </label>
                    <? endforeach ?>
                </div>
            </div>
            <div class="end-review bet-flex-cen">
                <div class="md-width-100">
                    <label class="wid-100pers">
                        <p class="name">Комментарии к заказу:</p>
                        <textarea class="wid-100pers" name="COMMENT" rows="3"><?=$component->order->getField('USER_DESCRIPTION')?></textarea>
                    </label>
                    <div>
                        <label class="custom-check <?= (!$conmponent->canSaveOrder && !$component->agree) ? 'red-label' : ''?>">
                            <input type="checkbox" name="AGREE" class="hide-check" <?=$component->agree === true ? 'checked' : '' ?>>
                            <span class="new-check"></span>
                            <span class="text-check">Вы соглашаетесь с условиями обработки персональных данных <a href="/policy/">(ознакомиться)</a></span>
                        </label>
                    </div>
                </div>
                <div class="right-content">
                    <?
                        $discount = $component->order->getDiscountPrice();
                        if ($discount == 0)
                        {
                            $discount = $component->order->getBasket()->getBasePrice() - $component->order->getBasket()->getPrice();
                        }

                    ?>
                    <p class="one-res">Товаров на: <span class="price"><?=$component->order->getBasket()->getBasePrice()?></span><span class="rub">₽</span></p>
                    <p class="one-res">Скидка: <span class="price"><?=$discount?> </span><span class="rub">₽</span></p>
                    <p class="one-res">Доставка: <span class="price"><?=$component->order->getDeliveryPrice()?></span><span class="rub">₽</span></p>
                    <p class="one-res">Итого: <span class="end-price"><?=$component->order->getPrice()?> </span><span class="rub">₽</span></p>
                </div>
            </div>
            <hr>
            <div class="center">
                <input type="hidden" name="basket_action" value="N">
                <input type="hidden" name="basket_action_type" value="">
                <input type="hidden" name="basket_action_item_id" value="">
                <input type="hidden" name="set_quantity" value="">
                <input type="hidden" name="set_profile_id" value="N">
                <input type="hidden" name="ajax" value="Y">
                <input type="hidden" name="check" value="N">
                <input type="hidden" name="save" value="N">
                <input type="submit" name="submit" class="custom-btn basket-button send-order" value="Оформить заказ">
            </div>
        </form>

	<? else: ?>
		<div class="basket-place">
			<div class="inner-basket flex-center empty_">
				<!--<object type="image/svg+xml" data="<?= SITE_TEMPLATE_PATH ?>/images/ico/basket.svg" class="right-line">stars</object>-->
				<p class="name">В вашей корзине ещё нет товаров</p>
				<div class="btn-place">
					<a href="<?=$arParams['EMPTY_BASKET_HINT_PATH']?>" class="btn-cat -o">Продолжить покупки</a>
				</div>
			</div>
		</div>
	<? endif ?>
<? endif ?>
<script type="text/javascript">
    $(document).ready(function(){
        $('input[name=PHONE]').mask("+7(999) 999-99-99");
    })
</script>

