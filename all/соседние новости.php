<?
$arFilter = array("IBLOCK_ID" => $arResult['IBLOCK_ID']);

// Выбиреам записи
$rs = CIBlockElement::GetList(array("SORT"=>"ASC"),$arFilter,false,false,array('ID','NAME','DETAIL_PAGE_URL'));
$i=0;
while ($ar = $rs -> GetNext()) {
   $arNavi[$i] = $ar;
        // Если ID полученной записи равен ID новости которая отображается, то запоминаем ее номер
   if ($ar['ID'] == $arResult['ID']) $iCurPos = $i;
   $i++;
}
// Заполняем массив информацией о следующей и предыдущей записи
// Ключ предыдущего элемента будет [$iCurPos - 1]
// Ключ следующего элемента будет [$iCurPos + 1]
// Если элементы массива с этими ключами существуют то сохраняем их, иначе осталяем пустыми
// $arLink - массив со ссылками на след и предыд новости
$arResult['LINKS'] = array();
$arResult['LINKS']['PREVIOUS'] = isset($arNavi[$iCurPos - 1]) ? $arNavi[$iCurPos - 1] : '';
$arResult['LINKS']['NEXT'] = isset($arNavi[$iCurPos+1]) ? $arNavi[$iCurPos+1] : '';
?>

<div class="padding-bottom">
	<nav class="nav-line obj">
		<? if ($arResult['LINKS']['PREVIOUS']): ?>
			<a class="prew-or-next" href="<?=$arResult['LINKS']['PREVIOUS']['DETAIL_PAGE_URL'] ?>">
				<div class="arrow-svg"></div>
				Предыдущая
			</a>
		<? else: ?>
			<a class="prew-or-next">
				Начало
			</a>
		<? endif ?>
		<a class="s-btn" href="<?=$arResult['LIST_PAGE_URL'] ?>">Весь список</a>
		<? if ($arResult['LINKS']['NEXT']): ?>
			<a href="<?=$arResult['LINKS']['NEXT']['DETAIL_PAGE_URL'] ?>" class="prew-or-next">
				Следующая
				<div class="arrow-svg-r"></div>
			</a>
		<? else: ?>
			<a class="prew-or-next">
				Конец
			</a>
		<? endif ?>
	</nav>
</div>