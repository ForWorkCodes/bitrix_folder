<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$templateData = array(
	'TEMPLATE_THEME' => $this->GetFolder().'/themes/'.$arParams['TEMPLATE_THEME'].'/colors.css',
	'TEMPLATE_CLASS' => 'bx-'.$arParams['TEMPLATE_THEME']
);

if (isset($templateData['TEMPLATE_THEME']))
{
	$this->addExternalCss($templateData['TEMPLATE_THEME']);
}
?>
<div class="asideBlock asideBlock-filters bx-filter <?=$templateData["TEMPLATE_CLASS"]?>">
	<div class="aside__close">
		<?$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/img/html/close.html', array(), array())?>
	</div>
	<div class="mainTitle">
		Фильтры
	</div>

	<form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get" class="smartfilter">
		<?foreach($arResult["HIDDEN"] as $arItem):?>
			<input type="hidden" name="<?echo $arItem["CONTROL_NAME"]?>" id="<?echo $arItem["CONTROL_ID"]?>" value="<?echo $arItem["HTML_VALUE"]?>" />
		<?endforeach;?>
		<div>
			<?foreach($arResult["ITEMS"] as $key=>$arItem)//prices
			{
				$key = $arItem["ENCODED_ID"];
				if(isset($arItem["PRICE"])):
					if ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0)
						continue;

					$step_num = 1;
					$step = ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"]) / $step_num;
					$prices = array();
					if (Bitrix\Main\Loader::includeModule("currency"))
					{
						for ($i = 0; $i < $step_num; $i++)
						{
							$prices[$i] = CCurrencyLang::CurrencyFormat($arItem["VALUES"]["MIN"]["VALUE"] + $step*$i, $arItem["VALUES"]["MIN"]["CURRENCY"], false);
						}
						$prices[$step_num] = CCurrencyLang::CurrencyFormat($arItem["VALUES"]["MAX"]["VALUE"], $arItem["VALUES"]["MAX"]["CURRENCY"], false);
					}
					else
					{
						$precision = $arItem["DECIMALS"]? $arItem["DECIMALS"]: 0;
						for ($i = 0; $i < $step_num; $i++)
						{
							$prices[$i] = number_format($arItem["VALUES"]["MIN"]["VALUE"] + $step*$i, $precision, ".", "");
						}
						$prices[$step_num] = number_format($arItem["VALUES"]["MAX"]["VALUE"], $precision, ".", "");
					}
					?>
					<ul class="catalogCategoryList bx-filter-parameters-box">
						<li class="catalogCategoryList__item active">
							<span class="bx-filter-container-modef"></span>
							<div class="bx-filter-parameters-box-title" onclick="smartFilter.hideFilterProps(this)"></div>
							<span class="catalogCategoryList__link">
								Цена
								<i class="fas fa-chevron-down"></i>
							</span>	
							<div class="catalogCategoryList__collapse bx-filter-block" data-role="bx_filter_block" style="display: block">
								<div class="filterBox__flex bx-filter-parameters-box-container">
									<div class="filterBox__input bx-filter-parameters-box-container-block bx-left">
										<i class="bx-ft-sub"><?=GetMessage("CT_BCSF_FILTER_FROM")?></i>
										<div class="bx-filter-input-container">
											<input
												class="customInput min-price"
												type="text"
												name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"
												id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"
												value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>"
												size="5"
												onkeyup="smartFilter.keyup(this)"
											/>
										</div>
									</div>
									<div class="filterBox__input bx-filter-parameters-box-container-block bx-right">
										<i class="bx-ft-sub"><?=GetMessage("CT_BCSF_FILTER_TO")?></i>
										<div class="bx-filter-input-container">
											<input
												class="customInput max-price"
												type="text"
												name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"
												id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"
												value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>"
												size="5"
												onkeyup="smartFilter.keyup(this)"
											/>
										</div>
									</div>
									<div class="filterBox__tack bx-ui-slider-track-container">
										<div class="bx-ui-slider-track" id="drag_track_<?=$key?>">
											<?for($i = 0; $i <= $step_num; $i++):?>
											<div class="bx-ui-slider-part p<?=$i+1?>"><span><?=$prices[$i]?></span></div>
											<?endfor;?>

											<div class="bx-ui-slider-pricebar-vd" style="left: 0;right: 0;" id="colorUnavailableActive_<?=$key?>"></div>
											<div class="bx-ui-slider-pricebar-vn" style="left: 0;right: 0;" id="colorAvailableInactive_<?=$key?>"></div>
											<div class="bx-ui-slider-pricebar-v"  style="left: 0;right: 0;" id="colorAvailableActive_<?=$key?>"></div>
											<div class="bx-ui-slider-range" id="drag_tracker_<?=$key?>"  style="left: 0%; right: 0%;">
												<a class="bx-ui-slider-handle left"  style="left:0;" href="javascript:void(0)" id="left_slider_<?=$key?>"></a>
												<a class="bx-ui-slider-handle right" style="right:0;" href="javascript:void(0)" id="right_slider_<?=$key?>"></a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</li>
					</ul>
					<?
					$arJsParams = array(
						"leftSlider" => 'left_slider_'.$key,
						"rightSlider" => 'right_slider_'.$key,
						"tracker" => "drag_tracker_".$key,
						"trackerWrap" => "drag_track_".$key,
						"minInputId" => $arItem["VALUES"]["MIN"]["CONTROL_ID"],
						"maxInputId" => $arItem["VALUES"]["MAX"]["CONTROL_ID"],
						"minPrice" => $arItem["VALUES"]["MIN"]["VALUE"],
						"maxPrice" => $arItem["VALUES"]["MAX"]["VALUE"],
						"curMinPrice" => $arItem["VALUES"]["MIN"]["HTML_VALUE"],
						"curMaxPrice" => $arItem["VALUES"]["MAX"]["HTML_VALUE"],
						"fltMinPrice" => intval($arItem["VALUES"]["MIN"]["FILTERED_VALUE"]) ? $arItem["VALUES"]["MIN"]["FILTERED_VALUE"] : $arItem["VALUES"]["MIN"]["VALUE"] ,
						"fltMaxPrice" => intval($arItem["VALUES"]["MAX"]["FILTERED_VALUE"]) ? $arItem["VALUES"]["MAX"]["FILTERED_VALUE"] : $arItem["VALUES"]["MAX"]["VALUE"],
						"precision" => $precision,
						"colorUnavailableActive" => 'colorUnavailableActive_'.$key,
						"colorAvailableActive" => 'colorAvailableActive_'.$key,
						"colorAvailableInactive" => 'colorAvailableInactive_'.$key,
					);
					?>
					<script type="text/javascript">
						BX.ready(function(){
							window['trackBar<?=$key?>'] = new BX.Iblock.SmartFilter(<?=CUtil::PhpToJSObject($arJsParams)?>);
						});
					</script>
				<?endif;
			}

			//not prices
			foreach($arResult["ITEMS"] as $key=>$arItem)
			{
				if(
					empty($arItem["VALUES"])
					|| isset($arItem["PRICE"])
				)
					continue;

				if (
					$arItem["DISPLAY_TYPE"] == "A"
					&& (
						$arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0
					)
				)
					continue;
				?>
				<ul class="catalogCategoryList bx-filter-parameters-box">
					<li class="catalogCategoryList__item <?if ($arItem["DISPLAY_EXPANDED"]== "Y"):?>active<?endif?>">
						<span class="bx-filter-container-modef"></span>
						<div class="bx-filter-parameters-box-title"></div>
						<span class="catalogCategoryList__link">
							<?=$arItem['NAME'] ?>
							<i class="fas fa-chevron-down"></i>
						</span>
						<div class="catalogCategoryList__collapse bx-filter-block" data-role="bx_filter_block" <?if ($arItem["DISPLAY_EXPANDED"]== "Y"):?>style="display: block"<?endif?>>
							<div class="collapseFlex bx-filter-parameters-box-container">
								<?
								$arCur = current($arItem["VALUES"]);
								switch ($arItem["DISPLAY_TYPE"])
								{
									case "A"://NUMBERS_WITH_SLIDER
										?>
										<div class="filterBox__flex w100 nomar">
											<div class="filterBox__input bx-filter-parameters-box-container-block bx-left">
												<i class="bx-ft-sub"><?=GetMessage("CT_BCSF_FILTER_FROM")?></i>
												<div class="bx-filter-input-container">
													<input
														class="customInput min-price"
														type="text"
														name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"
														id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"
														value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>"
														size="5"
														onkeyup="smartFilter.keyup(this)"
													/>
												</div>
											</div>
											<div class="filterBox__input bx-filter-parameters-box-container-block bx-right">
												<i class="bx-ft-sub"><?=GetMessage("CT_BCSF_FILTER_TO")?></i>
												<div class="bx-filter-input-container">
													<input
														class="customInput max-price"
														type="text"
														name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"
														id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"
														value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>"
														size="5"
														onkeyup="smartFilter.keyup(this)"
													/>
												</div>
											</div>
											<div class="filterBox__tack bx-ui-slider-track-container">
												<div class="bx-ui-slider-track" id="drag_track_<?=$key?>">
													<?
													$precision = $arItem["DECIMALS"]? $arItem["DECIMALS"]: 0;
													$step = ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"]) / 2;
													$value1 = number_format($arItem["VALUES"]["MIN"]["VALUE"], $precision, ".", "");
													$value5 = number_format($arItem["VALUES"]["MAX"]["VALUE"], $precision, ".", "");
													?>
													<div class="bx-ui-slider-part p1"><span><?=$value1?></span></div>
													<div class="bx-ui-slider-part p2"><span><?=$value5?></span></div>

													<div class="bx-ui-slider-pricebar-vd" style="left: 0;right: 0;" id="colorUnavailableActive_<?=$key?>"></div>
													<div class="bx-ui-slider-pricebar-vn" style="left: 0;right: 0;" id="colorAvailableInactive_<?=$key?>"></div>
													<div class="bx-ui-slider-pricebar-v"  style="left: 0;right: 0;" id="colorAvailableActive_<?=$key?>"></div>
													<div class="bx-ui-slider-range" 	id="drag_tracker_<?=$key?>"  style="left: 0;right: 0;">
														<a class="bx-ui-slider-handle left"  style="left:0;" href="javascript:void(0)" id="left_slider_<?=$key?>"></a>
														<a class="bx-ui-slider-handle right" style="right:0;" href="javascript:void(0)" id="right_slider_<?=$key?>"></a>
													</div>
												</div>
											</div>
										</div>

										<?
										$arJsParams = array(
											"leftSlider" => 'left_slider_'.$key,
											"rightSlider" => 'right_slider_'.$key,
											"tracker" => "drag_tracker_".$key,
											"trackerWrap" => "drag_track_".$key,
											"minInputId" => $arItem["VALUES"]["MIN"]["CONTROL_ID"],
											"maxInputId" => $arItem["VALUES"]["MAX"]["CONTROL_ID"],
											"minPrice" => $arItem["VALUES"]["MIN"]["VALUE"],
											"maxPrice" => $arItem["VALUES"]["MAX"]["VALUE"],
											"curMinPrice" => $arItem["VALUES"]["MIN"]["HTML_VALUE"],
											"curMaxPrice" => $arItem["VALUES"]["MAX"]["HTML_VALUE"],
											"fltMinPrice" => intval($arItem["VALUES"]["MIN"]["FILTERED_VALUE"]) ? $arItem["VALUES"]["MIN"]["FILTERED_VALUE"] : $arItem["VALUES"]["MIN"]["VALUE"] ,
											"fltMaxPrice" => intval($arItem["VALUES"]["MAX"]["FILTERED_VALUE"]) ? $arItem["VALUES"]["MAX"]["FILTERED_VALUE"] : $arItem["VALUES"]["MAX"]["VALUE"],
											"precision" => $arItem["DECIMALS"]? $arItem["DECIMALS"]: 0,
											"colorUnavailableActive" => 'colorUnavailableActive_'.$key,
											"colorAvailableActive" => 'colorAvailableActive_'.$key,
											"colorAvailableInactive" => 'colorAvailableInactive_'.$key,
										);
										?>
										<script type="text/javascript">
											BX.ready(function(){
												window['trackBar<?=$key?>'] = new BX.Iblock.SmartFilter(<?=CUtil::PhpToJSObject($arJsParams)?>);
											});
										</script>
										<?
										break;
									case "B"://NUMBERS
										?>
										<div class="filterBox__flex w100">
											<div class="filterBox__input bx-filter-parameters-box-container-block bx-left">
												<i class="bx-ft-sub"><?=GetMessage("CT_BCSF_FILTER_FROM")?></i>
												<div class="bx-filter-input-container">
													<input
														class="customInput min-price"
														type="text"
														name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"
														id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"
														value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>"
														size="5"
														placeholder="<?=$arItem['VALUES']['MIN']['VALUE'] ?>"
														onkeyup="smartFilter.keyup(this)"
														/>
												</div>
											</div>
											<div class="filterBox__input bx-filter-parameters-box-container-block bx-right">
												<i class="bx-ft-sub"><?=GetMessage("CT_BCSF_FILTER_TO")?></i>
												<div class="bx-filter-input-container">
													<input
														class="customInput max-price"
														type="text"
														name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"
														id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"
														value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>"
														size="5"
														placeholder="<?=$arItem['VALUES']['MAX']['VALUE'] ?>"
														onkeyup="smartFilter.keyup(this)"
														/>
												</div>
											</div>
										</div>
										<?
										break;
									case "G"://CHECKBOXES_WITH_PICTURES
										?>
										<?foreach ($arItem["VALUES"] as $val => $ar):?>
											<label for="<?=$ar["CONTROL_ID"]?>" data-role="label_<?=$ar["CONTROL_ID"]?>" class="colorFLowerLine bx-filter-param-label<?=$class?>" onclick="smartFilter.keyup(BX('<?=CUtil::JSEscape($ar["CONTROL_ID"])?>'));">
												<input
													type="checkbox"
													name="<?=$ar["CONTROL_NAME"]?>"
													id="<?=$ar["CONTROL_ID"]?>"
													value="<?=$ar["HTML_VALUE"]?>"
													<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
													class="custom-chekbox"
												/>
												<?
												$class = "";
												if ($ar["CHECKED"])
													$class.= " active";
												if ($ar["DISABLED"])
													$class.= " disabled";
												?>
												<label class="custom-label custom-label--color<?=$class?>" for="<?=$ar["CONTROL_ID"]?>" data-role="label_<?=$ar["CONTROL_ID"]?>" onclick="smartFilter.keyup(BX('<?=CUtil::JSEscape($ar["CONTROL_ID"])?>'));">
													<?if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])):?>
														<img src="<?=$ar["FILE"]["SRC"]?>">
													<?endif?>
												</label>
											</label>
										<?endforeach?>
										<?
										break;
									case "H"://CHECKBOXES_WITH_PICTURES_AND_LABELS
										?>
										<?foreach ($arItem["VALUES"] as $val => $ar):?>
											<label for="<?=$ar["CONTROL_ID"]?>" data-role="label_<?=$ar["CONTROL_ID"]?>" class="colorFLowerLine bx-filter-param-label<?=$class?>" onclick="smartFilter.keyup(BX('<?=CUtil::JSEscape($ar["CONTROL_ID"])?>'));">
												<input
													type="checkbox"
													name="<?=$ar["CONTROL_NAME"]?>"
													id="<?=$ar["CONTROL_ID"]?>"
													value="<?=$ar["HTML_VALUE"]?>"
													<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
													class="custom-chekbox"
												/>
												<?
												$class = "";
												if ($ar["CHECKED"])
													$class.= " active";
												if ($ar["DISABLED"])
													$class.= " disabled";
												?>
												<label class="custom-label custom-label--color<?=$class?>" for="<?=$ar["CONTROL_ID"]?>" data-role="label_<?=$ar["CONTROL_ID"]?>" onclick="smartFilter.keyup(BX('<?=CUtil::JSEscape($ar["CONTROL_ID"])?>'));">
													<?if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])):?>
														<img src="<?=$ar["FILE"]["SRC"]?>">
													<?endif?>
												</label>
												<div class="colorFLowerLine__name" title="<?=$ar["VALUE"]?>">
													<?=$ar["VALUE"]?>
												</div>
											</label>
										<?endforeach?>
										<?
										break;
									case "P"://DROPDOWN
										$checkedItemExist = false;
										?>
										<div class="col-xs-12">
											<div class="bx-filter-select-container">
												<div class="bx-filter-select-block" onclick="smartFilter.showDropDownPopup(this, '<?=CUtil::JSEscape($key)?>')">
													<div class="bx-filter-select-text" data-role="currentOption">
														<?
														foreach ($arItem["VALUES"] as $val => $ar)
														{
															if ($ar["CHECKED"])
															{
																echo $ar["VALUE"];
																$checkedItemExist = true;
															}
														}
														if (!$checkedItemExist)
														{
															echo GetMessage("CT_BCSF_FILTER_ALL");
														}
														?>
													</div>
													<div class="bx-filter-select-arrow"></div>
													<input
														style="display: none"
														type="radio"
														name="<?=$arCur["CONTROL_NAME_ALT"]?>"
														id="<? echo "all_".$arCur["CONTROL_ID"] ?>"
														value=""
													/>
													<?foreach ($arItem["VALUES"] as $val => $ar):?>
														<input
															style="display: none"
															type="radio"
															name="<?=$ar["CONTROL_NAME_ALT"]?>"
															id="<?=$ar["CONTROL_ID"]?>"
															value="<? echo $ar["HTML_VALUE_ALT"] ?>"
															<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
														/>
													<?endforeach?>
													<div class="bx-filter-select-popup" data-role="dropdownContent" style="display: none;">
														<ul>
															<li>
																<label for="<?="all_".$arCur["CONTROL_ID"]?>" class="bx-filter-param-label" data-role="label_<?="all_".$arCur["CONTROL_ID"]?>" onclick="smartFilter.selectDropDownItem(this, '<?=CUtil::JSEscape("all_".$arCur["CONTROL_ID"])?>')">
																	<? echo GetMessage("CT_BCSF_FILTER_ALL"); ?>
																</label>
															</li>
														<?
														foreach ($arItem["VALUES"] as $val => $ar):
															$class = "";
															if ($ar["CHECKED"])
																$class.= " selected";
															if ($ar["DISABLED"])
																$class.= " disabled";
														?>
															<li>
																<label for="<?=$ar["CONTROL_ID"]?>" class="bx-filter-param-label<?=$class?>" data-role="label_<?=$ar["CONTROL_ID"]?>" onclick="smartFilter.selectDropDownItem(this, '<?=CUtil::JSEscape($ar["CONTROL_ID"])?>')"><?=$ar["VALUE"]?></label>
															</li>
														<?endforeach?>
														</ul>
													</div>
												</div>
											</div>
										</div>
										<?
										break;
									case "R"://DROPDOWN_WITH_PICTURES_AND_LABELS
										?>
										<div class="col-xs-12">
											<div class="bx-filter-select-container">
												<div class="bx-filter-select-block" onclick="smartFilter.showDropDownPopup(this, '<?=CUtil::JSEscape($key)?>')">
													<div class="bx-filter-select-text fix" data-role="currentOption">
														<?
														$checkedItemExist = false;
														foreach ($arItem["VALUES"] as $val => $ar):
															if ($ar["CHECKED"])
															{
															?>
																<?if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])):?>
																	<span class="bx-filter-btn-color-icon" style="background-image:url('<?=$ar["FILE"]["SRC"]?>');"></span>
																<?endif?>
																<span class="bx-filter-param-text">
																	<?=$ar["VALUE"]?>
																</span>
															<?
																$checkedItemExist = true;
															}
														endforeach;
														if (!$checkedItemExist)
														{
															?><span class="bx-filter-btn-color-icon all"></span> <?
															echo GetMessage("CT_BCSF_FILTER_ALL");
														}
														?>
													</div>
													<div class="bx-filter-select-arrow"></div>
													<input
														style="display: none"
														type="radio"
														name="<?=$arCur["CONTROL_NAME_ALT"]?>"
														id="<? echo "all_".$arCur["CONTROL_ID"] ?>"
														value=""
													/>
													<?foreach ($arItem["VALUES"] as $val => $ar):?>
														<input
															style="display: none"
															type="radio"
															name="<?=$ar["CONTROL_NAME_ALT"]?>"
															id="<?=$ar["CONTROL_ID"]?>"
															value="<?=$ar["HTML_VALUE_ALT"]?>"
															<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
														/>
													<?endforeach?>
													<div class="bx-filter-select-popup" data-role="dropdownContent" style="display: none">
														<ul>
															<li style="border-bottom: 1px solid #e5e5e5;padding-bottom: 5px;margin-bottom: 5px;">
																<label for="<?="all_".$arCur["CONTROL_ID"]?>" class="bx-filter-param-label" data-role="label_<?="all_".$arCur["CONTROL_ID"]?>" onclick="smartFilter.selectDropDownItem(this, '<?=CUtil::JSEscape("all_".$arCur["CONTROL_ID"])?>')">
																	<span class="bx-filter-btn-color-icon all"></span>
																	<? echo GetMessage("CT_BCSF_FILTER_ALL"); ?>
																</label>
															</li>
														<?
														foreach ($arItem["VALUES"] as $val => $ar):
															$class = "";
															if ($ar["CHECKED"])
																$class.= " selected";
															if ($ar["DISABLED"])
																$class.= " disabled";
														?>
															<li>
																<label for="<?=$ar["CONTROL_ID"]?>" data-role="label_<?=$ar["CONTROL_ID"]?>" class="bx-filter-param-label<?=$class?>" onclick="smartFilter.selectDropDownItem(this, '<?=CUtil::JSEscape($ar["CONTROL_ID"])?>')">
																	<?if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])):?>
																		<span class="bx-filter-btn-color-icon" style="background-image:url('<?=$ar["FILE"]["SRC"]?>');"></span>
																	<?endif?>
																	<span class="bx-filter-param-text">
																		<?=$ar["VALUE"]?>
																	</span>
																</label>
															</li>
														<?endforeach?>
														</ul>
													</div>
												</div>
											</div>
										</div>
										<?
										break;
									case "K"://RADIO_BUTTONS
										?>
										<label class="colorFLowerLine bx-filter-param-label" for="<?="all_".$arCur["CONTROL_ID"] ?>">
											<input
												type="radio"
												value=""
												name="<?=$arCur["CONTROL_NAME_ALT"] ?>"
												id="<?="all_".$arCur["CONTROL_ID"] ?>"
												onclick="smartFilter.click(this)"
												class="custom-radio"
											/>
											<label class="custom-label" for="<?="all_".$arCur["CONTROL_ID"] ?>" title="<?=GetMessage("CT_BCSF_FILTER_ALL"); ?>"></label>
											<div class="colorFLowerLine__name" title="<?=GetMessage("CT_BCSF_FILTER_ALL"); ?>">
												<?=GetMessage("CT_BCSF_FILTER_ALL"); ?>
											</div>
										</label>
										
										<?foreach($arItem["VALUES"] as $val => $ar):?>
											
												<label data-role="label_<?=$ar["CONTROL_ID"]?>" class="colorFLowerLine bx-filter-param-label" for="<?=$ar["CONTROL_ID"] ?>">
													<input
														type="radio"
														value="<?=$ar["HTML_VALUE_ALT"] ?>"
														name="<?=$ar["CONTROL_NAME_ALT"] ?>"
														id="<?=$ar["CONTROL_ID"] ?>"
														<?=$ar["CHECKED"]? 'checked="checked"': '' ?>
														onclick="smartFilter.click(this)"
														class="custom-radio"
													/>
													<label class="custom-label" for="<?=$ar["CONTROL_ID"] ?>" title="<?=$ar["VALUE"]?>"></label>
													<div class="colorFLowerLine__name" title="<?=$ar["VALUE"]?>">
														<?=$ar["VALUE"]?>
													</div>
													
												</label>
											
										<?endforeach;?>
										
										<?
										break;
									case "U"://CALENDAR
										?>
										<div class="col-xs-12">
											<div class="bx-filter-parameters-box-container-block"><div class="bx-filter-input-container bx-filter-calendar-container">
												<?$APPLICATION->IncludeComponent(
													'bitrix:main.calendar',
													'',
													array(
														'FORM_NAME' => $arResult["FILTER_NAME"]."_form",
														'SHOW_INPUT' => 'Y',
														'INPUT_ADDITIONAL_ATTR' => 'class="calendar" placeholder="'.FormatDate("SHORT", $arItem["VALUES"]["MIN"]["VALUE"]).'" onkeyup="smartFilter.keyup(this)" onchange="smartFilter.keyup(this)"',
														'INPUT_NAME' => $arItem["VALUES"]["MIN"]["CONTROL_NAME"],
														'INPUT_VALUE' => $arItem["VALUES"]["MIN"]["HTML_VALUE"],
														'SHOW_TIME' => 'N',
														'HIDE_TIMEBAR' => 'Y',
													),
													null,
													array('HIDE_ICONS' => 'Y')
												);?>
											</div></div>
											<div class="bx-filter-parameters-box-container-block"><div class="bx-filter-input-container bx-filter-calendar-container">
												<?$APPLICATION->IncludeComponent(
													'bitrix:main.calendar',
													'',
													array(
														'FORM_NAME' => $arResult["FILTER_NAME"]."_form",
														'SHOW_INPUT' => 'Y',
														'INPUT_ADDITIONAL_ATTR' => 'class="calendar" placeholder="'.FormatDate("SHORT", $arItem["VALUES"]["MAX"]["VALUE"]).'" onkeyup="smartFilter.keyup(this)" onchange="smartFilter.keyup(this)"',
														'INPUT_NAME' => $arItem["VALUES"]["MAX"]["CONTROL_NAME"],
														'INPUT_VALUE' => $arItem["VALUES"]["MAX"]["HTML_VALUE"],
														'SHOW_TIME' => 'N',
														'HIDE_TIMEBAR' => 'Y',
													),
													null,
													array('HIDE_ICONS' => 'Y')
												);?>
											</div></div>
										</div>
										<?
										break;
									default://CHECKBOXES
										?>
										<?foreach($arItem["VALUES"] as $val => $ar):?>
											<label data-role="label_<?=$ar["CONTROL_ID"]?>" class="colorFLowerLine bx-filter-param-label <? echo $ar["DISABLED"] ? 'disabled': '' ?>" for="<?=$ar["CONTROL_ID"] ?>">
												
												<input
													type="checkbox"
													value="<?=$ar["HTML_VALUE"] ?>"
													name="<?=$ar["CONTROL_NAME"] ?>"
													id="<?=$ar["CONTROL_ID"] ?>"
													<?=$ar["CHECKED"]? 'checked="checked"': '' ?>
													onclick="smartFilter.click(this)"
													class="custom-chekbox"
												/>
												<label class="custom-label bx-filter-param-text" for="<?=$ar["CONTROL_ID"] ?>" title="<?=$ar["VALUE"];?>"></label>
												<div class="colorFLowerLine__name" title="<?=$ar["VALUE"]?>"><?=$ar["VALUE"]?></div>
											</label>
										<?endforeach;?>
								<?
								}
								?>
							</div>
							<div style="clear: both"></div>
						</div>
					</li>
				</ul>
			<?
			}
			?>
		</div><!--//div-->
		<div>
			<ul class="catalogCategoryList bx-filter-parameters-box">
				
					<div class="bx-filter-parameters-box-container">
						<input
							class="btn-submit-filter"
							type="submit"
							id="set_filter"
							name="set_filter"
							value="<?=GetMessage("CT_BCSF_SET_FILTER")?>"
						/>
						<input
							class="btn-submit-filter"
							type="submit"
							id="del_filter"
							name="del_filter"
							value="<?=GetMessage("CT_BCSF_DEL_FILTER")?>"
						/>
						<div class="bx-filter-popup-result <?if ($arParams["FILTER_VIEW_MODE"] == "VERTICAL") echo $arParams["POPUP_POSITION"]?>" id="modef" <?if(!isset($arResult["ELEMENT_COUNT"])) echo 'style="display:none"';?> style="display: inline-block;">
							<?echo GetMessage("CT_BCSF_FILTER_COUNT", array("#ELEMENT_COUNT#" => '<span id="modef_num">'.intval($arResult["ELEMENT_COUNT"]).'</span>'));?>
							<span class="arrow"></span>
							<br/>
							<a href="<?echo $arResult["FILTER_URL"]?>" target=""><?echo GetMessage("CT_BCSF_FILTER_SHOW")?></a>
						</div>
					</div>
				
			</ul>
		</div>
		<div class="clb"></div>
	</form>
</div>
<script type="text/javascript">
	var smartFilter = new JCSmartFilter('<?echo CUtil::JSEscape($arResult["FORM_ACTION"])?>', '<?=CUtil::JSEscape($arParams["FILTER_VIEW_MODE"])?>', <?=CUtil::PhpToJSObject($arResult["JS_FILTER_PARAMS"])?>);
</script>