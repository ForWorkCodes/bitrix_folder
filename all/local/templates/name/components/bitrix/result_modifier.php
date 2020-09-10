<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
foreach ($arResult['ITEMS'] as &$arItem) {
	if ($arItem['PROPERTIES']['PHOTOS']['VALUE']) {
		foreach ($arItem['PROPERTIES']['PHOTOS']['VALUE'] as $arPhoto) {
			$arItem['PROPERTIES']['PHOTOS']['RESIZE']['BIG'][] = CFile::ResizeImageGet(CFile::GetFileArray($arPhoto), ['height' => 1000, 'width' => 500], BX_RESIZE_IMAGE_PROPORTIONAL)['src'];
			$arItem['PROPERTIES']['PHOTOS']['RESIZE']['SMALL'][] = CFile::ResizeImageGet(CFile::GetFileArray($arPhoto), ['height' => 170, 'width' => 94], BX_RESIZE_IMAGE_EXACT)['src'];
		}
	}
}