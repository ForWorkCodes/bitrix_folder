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

?>
<? if(intval($component->saved_order_id) > 0) : ?>
	<script type="text/javascript">
		location = 'spasibo/?ORDER_ID=<?=$component->saved_order_id?>';
	</script>
<? else: ?>
	<? if (count($component->order->getBasket()) > 0) : ?>
		<? 
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
				/**
				 * @var \Bitrix\Sale\Payment $payment
				 */
				$selected_paySystem = $payment->getPaySystem();
			endforeach; 
		?>
		<form action="<?= $APPLICATION->GetCurPage(); ?>" method="post" id="form_order" data-container="form_order_container">
			<div class="double-block flex-between basket-place">
				<div class="result-place">
					<div class="main-result">
						<div class="title-place default-pad">
							<p class="name"><span class="med">Товары в корзине</span></p>
						</div>
						<div class="default-pad flex-between flex-vc title table">
							<div class="w30">
								<p class="name">Товары</p>
							</div>
							<div class="w70 flex-center">
								<div class="center w20">
									<p class="name">Скидка</p>
								</div>
								<div class="center w20">
									<p class="name">Цена</p>
								</div>
								<div class="center w30">
									<p class="name">Сумма</p>
								</div>
								<div class="center w30">
									<p class="name">Количество</p>
									<p class="no-mar">шт.</p>
								</div>
							</div>
						</div>
						<? $items_price = 0; ?>
						<? foreach ($component->order->getBasket() as $arItem): ?>
						<?/**
						 * @var $arItem \Local\Sale\BasketItem
						 */
						$props = $arItem->getPropertyCollection()->getPropertyValues();
						?>
							<div class="default-pad flex-between table" id="row_<?= $arItem->getId();?>">
								<div class="w30">
									<p class="big-h"><?=$props['item_article']['VALUE']?> <span class="dop-i"><?=$props['item_brand']['VALUE']?></span></p>
									<p class="small-h">Тип: <?=$props['item_type']['VALUE']?></p>
									<p class="small-h">Производитель: <a href=""><?=$props['item_brand']['VALUE']?></a></p>
									<p class="small-h">Номер детали: <a href=""><?=$props['item_article']['VALUE']?> </a></p>
								</div>
								<div class="w70 flex-start">
									<div class="center w20">
										<p>0%</p>
									</div>
									<div class="center w20">
										<span class="g-price"><?= $arItem->getPrice(); ?> </span><span class="rubl">₽</span>
										<?
											$items_price += $arItem->getFinalPrice();
										?>
									</div> 
									<div class="center w30">
										<span class="g-price" id="current-price-<?=$arItem->getId();?>"><?= $arItem->getFinalPrice(); ?> </span><span class="rubl">₽</span>
									</div>
									<div class="center w30">
										<div class="up-number">
											<input type="text" class="number-input" value="<?= $arItem->getQuantity(); ?>">
											<input type="hidden" data-name="now_count" value="<?= $arItem->getQuantity(); ?>">
											<input type="hidden" data-name="all_count" value="<?= $props['all_count']['VALUE'] ?>">
											<input type="hidden" data-name="step" value="<?= $props['count']['VALUE'] ?>">
											<input type="hidden" data-name="this_id" value="<?=$arItem->getId();?>">
											<div class="btn-number">
												<a class="plus">+</a>
												<a class="minus">-</a>
											</div>
										</div>
									</div>
									<a href="javascript:void(0);" data-id="<?=$arItem->getId();?>" class="remove-btn"><object type="image/svg+xml" data="<?= SITE_TEMPLATE_PATH ?>/images/ico/remove.svg" class="mes-obj clo"></object></a>
								</div>
							</div>
						<? endforeach ?>
					</div>
				</div>
				<div class="filter-place">
					<div class="small-btn">
						<a>Результат</a>
					</div>
					<div class="filter">
						<div class="list-page">
							<?
								$total_price = $component->order->getPrice();
							?>
							<div class="rigth-side-row">
								<div>Товаров на</div>
								<div class="rigth-text">
									<span class="g-price basket-price"><?= $items_price ?></span>
									<span>₽</span>
								</div>
							</div>
							<div class="rigth-side-row delivery-row">
								<div>Доставка</div>
								<div class="rigth-text">
									<div class="g-price">
										<?= $component->order->getDeliveryPrice(); ?>
										<span>₽</span>									
									</div>
								</div>
							</div>
							<div class="rigth-side-row result-price">
								<div>ИТОГО:</div>
								<div class="rigth-text">
									<span class="total_price"><?= $total_price ?></span> ₽
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="coupon">
				<p class="name">Введите код купона для скидки:</p>
				<div class="flex-wrap">
					<input type="text" class="name-coupon">
					<input type="button" class="sub-coupon" value="Активировать">
				</div>
			</div>
			<!-- <div class="g-border center">
				<p>Вы заказывали в нашем интернет-магазине, поэтому мы заполнили все данные автоматически.</p>
				<p>Если все заполнено верно, нажмите кнопку "Оформить заказ".</p>
			</div> -->
			<div class="detail-cart-list flex-between double-block">
				<div class="result-place">
					<?
						if ($component->blocks['region_block'] == 'N')
						{
							$before = 'style="display:none;"';
							$after = 'style="display:block;"';
							$active = 'active';
						}
						else
						{
							$before = '';
							$after = '';
							$active = '';	
						}
					?>
					<? $next = $component->blocks['region_block'] == 'Y' ? true : false; ?>
					<div class="grey-block <?= $active ?>" data-name="region_block">					
						<div class="title-block default-pad <?= $active ?>">
							<p class="name">1. Регион доставки</p>
							<? if ($component->blocks['region_block'] == 'Y') : ?>
								<a href="" class="change" data-target="region_block">Изменить</a>
							<? endif ?>
						</div>
						<div class="default-pad flex-between show-before" <?= $before ?> >
							<div class="flex">
								<div class="flex-vc">
									<?
										$location = $component->currentGeo['COUNTRY_DISTRICT']['NAME'] . ', ' . $component->currentGeo['REGION']['NAME'] . ', ' . $component->currentGeo['CITY']['NAME'];
									?>
									<p class="black">Местоположение: <?= $location ?></p>
								</div>
							</div>
						</div>
						<div class="default-pad show-after <?= $active ?>" <?= $after ?>>
							<div class="flex-between">
								<div class="wid-50 place-order-step">
									<b>* Местоположение</b>
									<div class="mar-select"></div>
									<select class="select2-city" name="LOCATION_COUNTRY_DISTRICT">
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
									<div class="mar-select"></div>
									<select class="select2-city" name="LOCATION_REGION">
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
									<div class="mar-select"></div>
									<select class="select2-city" name="LOCATION_CITY">
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
									<div class="mar-select"></div>
								</div>
								<div>
									<? foreach ($component->order->getPropertyCollection()->getArray()['properties'] as $key => $prop) : ?>
										<? if (strtolower($prop['CODE']) == 'index') : ?>
											<b><?= $prop['REQUIRED'] == 'Y' ? '*' : ''; ?> <?= $prop['NAME'] ?></b>
											<input type="text" name="<?= strtolower($prop['CODE']) ?>" value="<?= is_array($prop['VALUE']) ? $prop['VALUE'][0] : $prop['VALUE'] ?>" class="custom-input-basket <?= $component->checkers[$prop['CODE']]['success'] == 'N' ? 'error' : '' ?>">
										<? endif ?>
									<? endforeach ?>
								</div>
							</div>
							<p class="light" style="display: none;">Выберите свой город в списке. Если вы не нашли свой город, выберите "другое местоположение", а город впишите в поле "Город"</p>
							<div class="sumbit-place">
								<input type="button" class="submit-change" data-type="forward" value="далее">
							</div>
						</div>
					</div>
					<?
						if ($component->blocks['delivery_block'] == 'N' && $next)
						{
							$before = 'style="display:none;"';
							$after = 'style="display:block;"';
							$active = 'active';
						}
						else
						{
							$before = '';
							$after = '';
							$active = '';	
						}
					?>
					<div class="grey-block <?= $active ?>" data-name="delivery_block">					
						<div class="title-block default-pad <?= $active ?>">
							<p class="name">2. способ доставки</p>
							<? if ($component->blocks['delivery_block'] == 'Y' && $next) : ?>
								<a href="" class="change" data-target="delivery_block">Изменить</a>
							<? endif ?>
						<? $next = ($component->blocks['delivery_block'] == 'Y' && $next) ? true : false; ?>
						</div>
						<div class="default-pad flex-between show-before" <?= $before ?>>
							<div class="flex">
								<div class="flex-vc">
									<div class="img">
										<img src="<?= SITE_TEMPLATE_PATH?>/images/posta.png" alt="">
									</div>
									<p class="black"><?= $selected_service['NAME'] ?></p>
								</div>
							</div>
							<div class="flex-vc">
								<div>
									<p class="green"><?= $selected_service['CONFIG']['MAIN']['PRICE'] ?> ₽</p>
								</div>
							</div>
						</div>
						<div class="default-pad show-after <?= $active ?>"  <?= $after ?>>
							<div class="flex-between pad-t">
								<div class="left-part-order">

									<? foreach ($component->services as $service): ?>
										<? if ($service['ID'] == 1) continue; ?>
										<?
											if ($service['ID'] == $component->default_service) // Проверка, если выбранная доставка
											{
												$checked = 'checked="checked"';
											}
											else
											{
												$checked = '';
											}
										?>
										<div class="flex-wrap flex-vc pad-bot-15">
											<div class="send-img">
												<img src="<?= CFile::GetPath($service['LOGOTIP'])?>" alt="">
											</div>
											<div class="mar-left-check">
												<label class="custom-check">
													<input type="radio" name="delivery_type" <?= $checked; ?> value="<?= $service['ID'] ?>" class="filter-check">
													<span class="check-ok"></span>
													<input type="hidden" data-name="delivery_price" value="<?= $service['CONFIG']['MAIN']['PRICE'] ?> ₽" >
													<input type="hidden" data-name="delivery_about" value="<?= $service['DESCRIPTION'] ?>" >
													<span class="delivery-name"><?= $service['NAME'] ?></span>
												</label>
											</div>
										</div>
									<? endforeach ?>
								</div>
								<div class="data-change right-part-order">
									<p class="name pad-bot-15" id="selected-delivery-name"><?= $selected_service['NAME'] ?></p>
									<div class="send-img pad-bot-5">
										<img id="selected-delivery-image" src="<?= CFile::GetPath($selected_service['LOGOTIP']) ?>" alt="">
									</div>
									<div class="pad-bot-30" id="selected-delivery-about">
										<?= $selected_service['DESCRIPTION'] ?>
									</div>
									<div class="pad-bot-15">
										Стоимость:
									</div>
									<div class="g-price" id="selected-delivery-price">
										<?= $selected_service['CONFIG']['MAIN']['PRICE'] ?> ₽
									</div>
								</div>
							</div>
							<div class="coupon pad-t-b">
								<p class="name pad-bot-5">Введите код купона для скидки:</p>
								<div class="flex-wrap">
									<input type="text" class="name-coupon">
									<input type="button" class="sub-coupon" value="Активировать">
								</div>
							</div>
							<div class="sumbit-place flex-between">
								<input type="button" class="submit-change" data-type="back" value="Назад">
								<input type="button" class="submit-change" data-type="forward" value="далее">
							</div>
						</div>
					</div>
					<?
						if ($component->blocks['payment_block'] == 'N' && $next)
						{
							$before = 'style="display:none;"';
							$after = 'style="display:block;"';
							$active = 'active';
						}
						else
						{
							$before = '';
							$after = '';
							$active = '';	
						}
					?>
					<div class="grey-block <?= $active ?>" data-name="payment_block">
						<div class="title-block default-pad">
							<p class="name">3. Оплата</p>
							<? if ($component->blocks['payment_block'] == 'Y'  && $next) : ?>
								<a href="" class="change" data-target="payment_block">Изменить</a>
							<? endif ?>
							<? $next = ($component->blocks['payment_block'] == 'Y' && $next) ? true : false; ?>
						</div>
						<div class="default-pad flex-between show-before" <?= $before ?>>
							<div class="flex">
								<div class="flex-vc">
									<div class="img">
										<img src="<?= CFile::GetPath($selected_paySystem->getField('LOGOTIP')) ?>" alt="">
									</div>
									<p class="black"><?= $selected_paySystem->getField('NAME') ?></p>
								</div>
							</div>
						</div>
						<div class="default-pad show-after" <?= $after ?>>
							<div class="flex-between pad-t">
								<div class="left-part-order">
									<? foreach ($component->paySystems as $key => $paySystem) : ?>
										<?
											if ($paySystem['ID'] == $component->default_paySystem)
											{
												$checked = 'checked="checked"';
											}
											else
											{
												$checked = '';
											}
										?>
										<div class="flex-wrap flex-vc pad-bot-15">
											<div class="send-img">
												<img src="<?= CFile::GetPath($paySystem['PSA_LOGOTIP']) ?>" alt="">
											</div>
											<div class="mar-left-check">
												<label class="custom-check">
													<input type="radio" name="payment_type" <?= $checked ?> value="<?= $paySystem['ID'] ?>" class="filter-check">
													<span class="check-ok"></span>
													<input type="hidden" data-name="payment_about" value="<?= $paySystem['DESCRIPTION'] ?>" >
													<span class="payment_name"><?= $paySystem['PSA_NAME'] ?></span>
												</label>
											</div>
										</div>
									<? endforeach ?>
								</div>
								<div class="data-change right-part-order">
									<p class="name pad-bot-15" id="selected-payment-name"><?= $selected_paySystem->getField('NAME') ?></p>
									<div class="send-img pad-bot-5">
										<img id="selected-payment-image" src="<?= CFile::GetPath($selected_paySystem->getField('LOGOTIP')) ?>" alt="">
									</div>
									<div class="pad-bot-30" id="selected-payment-about">
										<?= $selected_paySystem->getField('DESCRIPTION') ?>
									</div>
								</div>
							</div>

							<div class="sumbit-place flex-between">
								<input type="button" class="submit-change" data-type="back" value="Назад">
								<input type="button" class="submit-change" data-type="forward" value="далее">
							</div>
						</div>
					</div>
					<?
						if ($component->blocks['personal_block'] == 'N' && $next)
						{
							$before = 'style="display:none;"';
							$after = 'style="display:block;"';
							$active = 'active';
						}
						else
						{
							$before = '';
							$after = '';
							$active = '';	
						}
					?>
					<div class="grey-block <?= $active ?>" data-name="personal_block">					
						<div class="title-block default-pad">
							<p class="name">4. Покупатель</p>
							<? if ($component->blocks['personal_block'] == 'Y'  && $next) : ?>
								<a href="" class="change" data-target="personal_block">Изменить</a>
							<? endif ?>
							<? $next = ($component->blocks['personal_block'] == 'Y' && $next) ? true : false; ?>
						</div>					
						<div class="default-pad flex-between show-before" <?= $before ?>>
							<div class="flex">
								<div class="flex-vc">
									<p class="black">Свойства заказа</p>
								</div>
							</div>
						</div>
						<div class="default-pad show-after" <?= $after ?>>
							<div class="personal-place">
								<? $index = 0; ?>
								<? $no_codes = array('index', 'user_comment', 'location'); ?>
								<? foreach ($component->order->getPropertyCollection()->getArray()['properties'] as $key => $prop) : ?>
									<? if (!in_array(strtolower($prop['CODE']), $no_codes)) : ?>
										<? $index++; ?>
										<? if ($index == 1) : ?>
											<div class="input-area">
										<? endif ?>
											<div class="w-50 <?= $index == 2 ? 'padleft' : ''; ?>">
												<div class="input-label">
													<?= $prop['REQUIRED'] == 'Y' ? '*' : ''; ?> <?= $prop['NAME'] ?>
												</div>
												<input class="input-personal <?= $component->checkers[$prop['CODE']]['success'] == 'N' ? 'error' : '' ?>" type="text" name="<?= strtolower($prop['CODE']) ?>" value="<?= is_array($prop['VALUE']) ? $prop['VALUE'][0] : $prop['VALUE'] ?>" placeholder="">
											</div>
											<? if (strtolower($prop['CODE']) == 'phone') : ?>
												<script type="text/javascript">
													$('input[name=<?=strtolower($prop['CODE'])?>').mask("+7(999)999-9999");
												</script>
											<? endif ?>
										<? if ($index == 2) : ?>
											</div>
											<? $index = 0; ?>
										<? endif ?>
									<? endif ?>
								<? endforeach ?>
								<? if ($index != 0) : ?>
									</div>
								<? endif ?>
								<? foreach ($component->order->getPropertyCollection()->getArray()['properties'] as $key => $prop) : ?>
									<? if (strtolower($prop['CODE']) == 'user_comment') : ?>
										<div class="input-label <?= $component->checkers[$prop['CODE']] == 'N' ? 'error' : '' ?>">
											<?= $prop['NAME'] ?>
										</div>
										<textarea class="comment-area" name="<?= strtolower($prop['CODE']) ?>" rows="7"><?= is_array($prop['VALUE']) ? $prop['VALUE'][0] : $prop['VALUE'] ?></textarea>
									<? endif ?>
								<? endforeach ?>
							</div>
							<div class="sumbit-place flex-between">
								<input type="button" class="submit-change" data-type="back" value="Назад">
								<input type="button" class="submit-change" data-type="forward" value="далее">
							</div>
						</div>
					</div>
					<?
						if ($component->blocks['basket_block'] == 'N' && $next)
						{
							$before = 'style="display:none;"';
							$after = 'style="display:block;"';
							$active = 'active';
						}
						else
						{
							$before = '';
							$after = '';
							$active = '';	
						}
					?>
		
					<div class="grey-block <?= $active ?>" data-name="basket_block">					
						<div class="title-block default-pad <?= $active ?>">
							<p class="name">5. Товары в заказе</p>
							<? if ($component->blocks['basket_block'] == 'Y' && $next) : ?>
								<a href="" class="change" data-target="basket_block">Изменить</a>
							<? endif ?>
							<? $next = ($component->blocks['basket_block'] == 'Y' && $next) ? true : false; ?>
						</div>
						
						<? $items_price = 0; ?>
						<? foreach ($component->order->getBasket() as $arItem): ?>
						<?/**
						 * @var $arItem \Local\Sale\BasketItem
						 */
						$props = $arItem->getPropertyCollection()->getPropertyValues();
						?>
							<div class="default-pad new-grid right" id="row2_<?= $arItem->getId();?>">
								<div class="flex">
									<div>
										<p class="big-h left-text"><?=$props['item_article']['VALUE']?> <span class="dop-i"><?=$props['item_brand']['VALUE']?></span></p>
										<p class="small-h">Тип: <?=$props['item_type']['VALUE']?></p>
									</div>
								</div>
								<div>
									<div class="inline-block new-wid">
										<p><?= $arItem->getQuantity(); ?> шт.</p>
									</div>
								</div>
								<div class="btn-place">
									<span class="number"><?= $arItem->getFinalPrice(); ?> </span>
									<span class="rubl">&#8381;</span>
								</div>
							</div>
						<? endforeach ?>
						<div class="default-pad show-after" <?= $after ?>>
							<div class="coupon pad-t">
								<p class="name pad-bot-5">Введите код купона для скидки:</p>
								<div class="flex-wrap">
									<input type="text" class="name-coupon">
									<input type="button" class="sub-coupon" value="Активировать">
								</div>
							</div>
							<div class="sumbit-place flex-end">
								<input type="button" class="submit-change" data-type="forward" value="далее">
							</div>
						</div>
					</div>
					<div class="btn-place center">
						<input type="hidden" name="save" value="N">
						<input type="hidden" name="test" value="Y">
						<input type="hidden" name="ajax" value="Y">
						<input type="hidden" name="forceOpen" value="<?= $component->forceOpen; ?>">
						<? foreach ($component->blocks as $key => $value) : ?>
							<input type="hidden" name="<?= $key ?>" value="<?= $value ?>">
						<? endforeach ?>
						<? if ($next) : ?>
							<input type="button" name="submit" class="btn-cat -o pad-for-btn saveOrder" value="Оформить заказ">
						<? endif ?>
					</div>
				</div>
				<div class="filter-place">
					<div class="small-btn">
						<a>Возникли вопросы</a>
					</div>
					<div class="filter">
						<div class="baner">
							<div class="question-title">
								Возникли вопросы
							</div> 
							<div class="question">
								при оформлении заказа?
							</div>
							<div class="we-answer">
								Наши менеджеры помогут Вам! 
								Просто ставьте свои данные и мы перезвоним вам в ближайшее время
							</div>
							<input class="order-call" type="button" name="oreder-bell" value="заказать звонок">
							<img src="<?= SITE_TEMPLATE_PATH?>/images/ico/conversation.svg" alt="">
						</div>
					</div>
				</div>
			</div>
		</form>
	<? else: ?>
		<div class="basket-place">
			<div class="inner-basket flex-center empty_">
				<object type="image/svg+xml" data="<?= SITE_TEMPLATE_PATH ?>/images/ico/basket.svg" class="right-line">stars</object>
				<p class="name">В вашей корзине ещё нет товаров</p>
				<div class="btn-place">
					<a href="/" class="btn-cat -o">Продолжить покупки</a>
				</div>
			</div>
		</div>
	<? endif ?>
<? endif ?>