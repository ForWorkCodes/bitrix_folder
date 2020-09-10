<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * @var array $arResult
 * @var array $arParam
 * @var CBitrixComponentTemplate $this
 */

$this->setFrameMode(true);

if(!$arResult["NavShowAlways"])
{
	if ($arResult["NavRecordCount"] == 0 || ($arResult["NavPageCount"] == 1 && $arResult["NavShowAll"] == false))
		return;
}
?>
<nav class="nav-line">
	<? if ($arResult["NavPageNomer"] > 1):	?>
		<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]-1)?>" class="prew-or-next">
			<div class="arrow-svg"></div>
			назад
		</a>
	<? else: ?>
		<a class="prew-or-next">
			<div class="arrow-svg"></div>
			назад
		</a>
	<? endif ?>
	<?
	$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"]."&amp;" : "");
	$strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?".$arResult["NavQueryString"] : "");

	if ($arResult["bDescPageNumbering"] != true):
		?>
		<ul class="flex-numeral"><?
			if ($arResult["NavPageNomer"] > 1):
				if ($arResult["nStartPage"] > 1):
					if($arResult["bSavePage"]):
						?><li class="numeral"><a class="page-link" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=1">1</a></li><?
					else:
						?><li class="numeral"><a class="page-link" href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>">1</a></li><?
					endif;

					if ($arResult["nStartPage"] > 2):
						?><li class="numeral"><a class="page-link" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=round($arResult["nStartPage"] / 2)?>">...</a></li><?
					endif;
				endif;
			endif;

			do
			{
				if ($arResult["nStartPage"] == $arResult["NavPageNomer"]):
					?><li class="numeral active"><a class="page-link"><?=$arResult["nStartPage"]?></a></li><?
				elseif($arResult["nStartPage"] == 1 && $arResult["bSavePage"] == false):
					?><li class="numeral"><a class="page-link" href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>"><?=$arResult["nStartPage"]?></a></li><?
				else:
					?><li class="numeral"><a class="page-link" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["nStartPage"]?>"><?=$arResult["nStartPage"]?></a></li><?
				endif;
				$arResult["nStartPage"]++;
			}

			while($arResult["nStartPage"] <= $arResult["nEndPage"]);

			if($arResult["NavPageNomer"] < $arResult["NavPageCount"]):
				if ($arResult["nEndPage"] < $arResult["NavPageCount"]):
					if ($arResult["nEndPage"] < ($arResult["NavPageCount"] - 1)):
						?><li class="numeral"><a class="page-link" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=round($arResult["nEndPage"] + ($arResult["NavPageCount"] - $arResult["nEndPage"]) / 2)?>">...</a></li><?
					endif;
					?><li class="numeral"><a class="page-link" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["NavPageCount"]?>"><?=$arResult["NavPageCount"]?></a></li><?
				endif;
			endif;

			?>
		</ul>
		<?
	endif;
	?>
	<? if ($arResult['NavPageNomer'] < $arResult['NavPageCount']): ?>
		<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]+1)?>" class="prew-or-next">
			вперед
			<div class="arrow-svg-r"></div>
		</a>
	<? else: ?>
		<a class="prew-or-next">
			вперед
			<div class="arrow-svg-r"></div>
		</a>
	<? endif ?>
</nav>