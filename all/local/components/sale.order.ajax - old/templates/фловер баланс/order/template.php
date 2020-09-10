<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * Bitrix vars
 *
 * @var array                    $arParams
 * @var array                    $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain                 $APPLICATION
 * @global CUser                 $USER
 */
    $fileds = $component->publickField;
?>
<? if(intval($component->saved_order_id) > 0) : ?>
    <script type="text/javascript">
        window.location.replace("/spasibo/");
    </script>
<? endif ?>
<? if ($component->order->getPrice() != 0): ?>
    <div class="hidefon"></div>
    <div class="mobile-order">
        <div class="flex-detail">
            <div class="flex-d-2"><img src="<?=SITE_TEMPLATE_PATH ?>/img/telega.png"> <span>Скрыть детали заказа</span></div>
            <i class="fas fa-chevron-down scale"></i>
        </div>
    </div>
    <div class="list-price">
        <? foreach ($component->services as $arDelPrice): ?>
            <div id="del-<?=$arDelPrice['ID'] ?>"><?=$arDelPrice['CONFIG']['MAIN']['PRICE'] ?></div>
        <? endforeach ?>
        <div id="total-price-hid"><?=$component->order->getBasket()->getBasePrice() ?></div>
    </div>
    <form action="<?=$APPLICATION->GetCurPage();?>" method="POST" novalidate>
        <input type="hidden" name="ajax" value="N">
        <input type="hidden" name="save" value="Y">
        <div class="order-form">
            <div class="grid-form">
                <div class="rigt-part" id="first-lvl">
                    <div class="order-b">
                        <div id="contact-inf" class="current">Контактная информация</div>
                        <div class="slash">/</div>
                        <div id="delivery-kind">Способ доставки</div>
                        <div class="slash">/</div>
                        <div id="order-kind">  Способ оплаты</div>
                    </div>
                    <div class="contact-info">
                        <div class="contact-information">
                            Контактная информация
                        </div>
                        <? foreach ($component->order->getPropertyCollection() as $prop): ?>
                            <? if ($prop->getGroupId() == 2): ?>
                                <div>
                                    <input 
                                        class="order-input <?= ($prop->getField('CODE') == 'PHONE') ? 'phonemask' : '' ?>" 
                                        type="text" 
                                        placeholder="<?=$prop->getField('NAME') ?> <?= ($prop->isRequired()) ? '*' : '' ?>" 
                                        name="<?=$prop->getField('CODE') ?>" 
                                        value="<?=$fileds[$prop->getField('CODE')] ?>" 
                                        <?= ($prop->isRequired()) ? 'required' : '' ?>>
                                </div>
                            <? endif ?>
                        <? endforeach; ?>
                        <div class="contact-information">
                            Адрес доставки
                        </div>
                        <? foreach ($component->order->getPropertyCollection() as $prop): ?>
                            <? if ($prop->getGroupId() == 3): ?>
                                <? if ($prop->getField('CODE') == 'CITY'): ?>
                                    <div class="relative-inp">
                                        <input class="order-input city read-only" type="text" name="<?=$prop->getField('CODE') ?>" value="<?= (empty($fileds[$prop->getField('CODE')])) ? 'Москва' : $fileds[$prop->getField('CODE')] ?>" placeholder="Город <?= ($prop->isRequired()) ? '*' : '' ?>" readonly <?= ($prop->isRequired()) ? 'required' : '' ?>>
                                        <div class="child-inp">Изменить</div>
                                    </div>
                                <? else: ?>
                                    <div>
                                        <input class="order-input" type="text" placeholder="<?=$prop->getField('NAME') ?> <?= ($prop->isRequired()) ? '*' : '' ?>" name="<?=$prop->getField('CODE') ?>" value="<?=$fileds[$prop->getField('CODE')]?>" <?= ($prop->isRequired()) ? 'required' : '' ?>>
                                    </div>
                                <? endif ?>
                            <? endif ?>
                        <? endforeach; ?>
                        <? foreach ($component->order->getPropertyCollection() as $prop): ?>
                            <? if ($prop->getGroupId() == 4): ?>
                                <div class="comment-area">
                                    <input class="order-input" type="text" placeholder="<?=$prop->getField('NAME') ?> <?= ($prop->isRequired()) ? '*' : '' ?>" name="<?=$prop->getField('CODE') ?>" value="<?=$fileds[$prop->getField('CODE')]?>" <?= ($prop->isRequired()) ? 'required' : '' ?>>
                                </div>
                            <? endif ?>
                        <? endforeach; ?>
                        <div class="button-area">
                            <div class="order-buttn-flex">
                                <input type="hidden" name="PARAMS_HASH" value="<?= $arResult["PARAMS_HASH"] ?>">
                                <input type="hidden" name="product_name" value="<?=$array['NAME'] ?>">
                                <input type="hidden" name="product_price" value="<?=$array['PRICE'] ?>">
                                <input class="order-button next-part" id="b-1" type="button" value="К способу доставки" name="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rigt-part" id="sec-lvl">
                    <div class="order-b">
                        <div id="contact-inf" class="current prev change-btn first-lvl">Контактная информация</div>
                        <div class="slash">/</div>
                        <div id="delivery-kind" class="current">Способ доставки</div> 
                        <div class="slash">/</div>
                        <div id="order-kind"> Способ оплаты</div>
                    </div>
                    <div class="prev-information">
                        <div class="contact-line">
                            <div class="contact-filled">
                                <div>Контакты</div>
                                <div class="customer-em" data-comp="ok-email"></div>
                                <div class="customer-num" data-comp="ok-phone"></div>
                            </div>
                            <div class="change-btn first-lvl">
                                Изменить
                            </div>
                        </div>
                        <div class="contact-line">
                            <div class="contact-filled">
                                <div>Адрес доставки</div>
                                <div class="customer-em" data-comp="ok-address">
                                    
                                </div>
                            </div>
                            <div class="change-btn first-lvl">
                                Изменить
                            </div>
                        </div>
                    </div>
                    <div class="delivery-info">
                        <div class="contact-information del-title">
                            Способ доставки
                        </div>
                        <? foreach ($component->services as $arDel): ?>
                            <? if ( ($arDel['ACTIVE'] == 'Y') && ($arDel['ID'] != 1) ): ?>
                                <div class="delivery-time">
                                    <div class="radio-line">
                                        <div class="input-deli-2">
                                            <input class="radio-cust" type="radio" id="in-time-<?=$arDel['ID']?>" name="delivery_type" value="<?=$arDel['ID'] ?>" <?= ($component->default_service == $arDel['ID']) ? 'checked' : '' ?>>
                                            <? if ($arDel['ID'] == 4): ?>
                                                <label class="order-label" for="in-time-<?=$arDel['ID']?>"><?=$arDel['NAME'] ?> (ул. Добролюбова д. 18)</label>
                                            <? else: ?>
                                                <label class="order-label" for="in-time-<?=$arDel['ID']?>"><?=$arDel['NAME'] ?></label>
                                            <? endif ?>
                                        </div>
                                        <? if ($arDel['ID'] == 2): ?>
                                            <div class="input-time">
                                                <div class="time-time">Введите время</div>
                                                <input class="time-place" type="time" name="DOSTAVKA-KO-VREMENI" value="<?=$fileds['DOSTAVKA-KO-VREMENI'] ?>">
                                            </div>
                                        <? endif ?>
                                        <? if ($arDel['ID'] == 3): ?>
                                            <div class="input-time">
                                                c
                                                <input class="time-place" type="time" name="DOSTAVKA-OT" value="<?=$fileds['DOSTAVKA-OT'] ?>">
                                                до
                                                <input class="time-place" type="time" name="DOSTAVKA-DO" value="<?=$fileds['DOSTAVKA-DO'] ?>">
                                            </div>
                                        <? endif ?>
                                    </div>
                                    <div class="price-line">
                                        Стоимость доставки <span class="digit"><?=$arDel['CONFIG']['MAIN']['PRICE'] ?> <?=$arDel['CONFIG']['MAIN']['CURRENCY'] ?></span>
                                    </div>
                                </div>
                            <? endif ?>
                        <? endforeach ?>
                        <div class="p-s">
                            <span class="warning">!</span>Если адрес доставки находится за пределами МКАД, точная стоимость доставки будет рассчитана нашим менеджером
                        </div>
                        <div class="button-area-step2">
                            <div class="order-buttn-flex-step2">
                                <div class="prev-card" id="contact-i">< Назад к контактной информации</div>
                                <input class="order-button next-part" id="b-2" type="button" value="К способу оплаты" name="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rigt-part" id="third-lvl">
                    <div class="order-b">
                        <div id="contact-inf" class="current prev change-btn first-lvl">Контактная информация</div>
                        <div class="slash">/</div>
                        <div id="delivery-kind" class="current prev change-btn delivery-kind">Способ доставки</div> 
                        <div class="slash">/</div>
                        <div id="order-kind" class="current">  Способ оплаты</div>
                    </div>
                    <div class="prev-information">
                        <div class="contact-line">
                            <div class="contact-filled">
                                <div>Контакты</div>
                                <div class="customer-em" data-comp="ok-email"></div>
                                <div class="customer-num" data-comp="ok-phone"></div>
                            </div>
                            <div class="change-btn first-lvl">
                                Изменить
                            </div>
                        </div>
                        <div class="contact-line">
                            <div class="contact-filled">
                                <div>Адрес доставки</div>
                                <div class="customer-em" data-comp="ok-address">
                                    
                                </div>
                            </div>
                            <div class="change-btn first-lvl">
                                Изменить
                            </div>
                        </div>
                        <div class="contact-line">
                            <div class="contact-filled">
                                <div>Способ доставки</div>
                                <div class="customer-em" data-comp="ok-del">
                                    Доставка курьером к 17:00
                                </div>
                            </div>
                            <div class="change-btn sec-lvl">
                                Изменить
                            </div>
                        </div>
                    </div>
                    <div class="delivery-info">
                        <div class="contact-information del-title">
                            Способ оплаты
                        </div>
                        <? foreach ($component->paySystems as $arPay): ?>
                            <? if ( ($arPay['ACTIVE'] == 'Y') && ($arPay['ID'] != 1) ): ?>
                                <div class="delivery-time">
                                    <div class="radio-line">
                                        <div class="input-deli-2">
                                            <input class="radio-cust" type="radio" id="cash-<?=$arPay['ID'] ?>" name="payment_type" value="<?=$arPay['ID'] ?>" <?= ($component->default_paySystem == $arPay['ID']) ? 'checked' : '' ?>>
                                            <label class="order-label" for="cash-<?=$arPay['ID'] ?>"><?=$arPay['NAME'] ?></label>
                                        </div>
                                    </div>
                                </div>
                            <? endif ?>
                        <? endforeach ?>
                        <div class="p-s">
                            <span class="warning">!</span>Если адрес доставки находится за пределами МКАД, точная стоимость доставки будет рассчитана нашим менеджером
                        </div>
                        <div class="button-area-step2">
                            <div class="order-buttn-flex-step2">
                                <div class="prev-card" id="delivery-i">< Назад к способу доставки</div>

                                <input class="order-button" id="" type="submit" value="Оформить заказ" name="submit">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="left-part" style="display: block">
                    <? foreach ($component->order->getBasket() as $item): ?>
                        <div class="product-in-basket">
                            <div class="ordrer-flowers">
                                <a href="<?=$component->GetProduct($item->getField("PRODUCT_ID"))['DETAIL_PAGE_URL'] ?>" class="link-in-order" target="_blank"></a>
                                <div class="relat-img">
                                    <div class="relat-img-before"><?=$item->getQuantity() ?></div>
                                    <? if (!empty($component->GetProduct($item->getField("PRODUCT_ID"))['PREVIEW_PICTURE'])): ?>
                                        <img class="order-img" src="<?=$component->GetProduct($item->getField("PRODUCT_ID"))['PREVIEW_PICTURE'] ?>">
                                    <? endif ?>
                                </div>
                                <div>
                                    <div class="djul"><?=$item->getField("NAME"); ?></div>
                                    <!-- <div class="flower-label">Весенний букет</div> -->
                                </div>
                            </div>
                            <div>
                                <?=$item->getFinalPrice(); ?> Р
                            </div>
                        </div>
                    <? endforeach ?>
                    <div class="border-n-pad">
                        <div class="product-flex">
                            <div class="title-order">
                                ТОВАРОВ НА СУММУ
                            </div>
                            <div class="price-order">

                                <?=\SaleFormatCurrency($component->order->getBasket()->getBasePrice(), $component->order->getCurrency()) ?>
                            </div>
                        </div>
                        <div class="product-flex">
                            <div class="title-order">
                                за доставку
                            </div>
                            <div class="price-order del-pr">
                                <?= \SaleFormatCurrency($component->order->getDeliveryPrice(), $component->order->getCurrency()) ?>
                            </div>
                        </div>
                        <div class="product-flex">
                            <div class="title-order">
                                Итого
                            </div>
                            <div class="price-order total">
                                <?= \SaleFormatCurrency($component->order->getPrice(), $component->order->getCurrency()) ?>
                            </div>
                        </div>
                    </div>
                    <div class="label-product">
                        Без учёта доставки. Рассчёт стоимости доставки будет произведён на следующем шаге
                    </div>
                    <div class="errors">
                        <? if (!empty($component->ViewDebig)): ?>
                            <ul>
                                <? foreach ($component->ViewDebig as $arError): ?>
                                    <li><?=$arError?></li>
                                <? endforeach ?>
                            </ul>
                        <? endif ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
<? else: ?>
    <div class="empty-order">
        <a href="/catalog/">К списку букетов</a>
    </div>
<? endif ?>