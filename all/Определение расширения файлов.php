<?
/* Получаю файлы */
if (!empty($arResult['PROPERTIES']['FILES']['VALUE'])) {
	foreach ($arResult['PROPERTIES']['FILES']['VALUE'] as $arFile) {
		$arFil = CFile::GetFileArray($arFile);
		$arSize = CFile::MakeFileArray($arFile);
		$loadFile['SRC'] = $arFil['SRC'];
		$loadFile['NAME'] = $arFil['ORIGINAL_NAME'];
		$loadFile['OLD_SIZE'] = $arSize['size'];
		$size = $arSize['size'] / 1000;
		if ($size < 1) {
			$sizeNew = floor($size * 1000);
			$loadFile['SIZE_NAME'] = 'байт';
		}
		if ( ($size >= 1) && ($size < 1000) ) {
			$sizeNew = floor($size);
			$loadFile['SIZE_NAME'] = 'КБ';
		}
		if ( ($size >= 1000) && ($size < 1000000) ) {
			$sizeNew = floor($size / 1000);
			$loadFile['SIZE_NAME'] = 'МБ';
		}
		$loadFile['SIZE'] = $sizeNew;
		$loadFile['IMG'] = 'und.svg';

		$info = new SplFileInfo($arFil['FILE_NAME']);
		$mine = $info->getExtension();

		switch ($mine) {
			case 'jpg':
				$loadFile['IMG'] = 'jpg.svg';
				break;
			case 'jpeg':
				$loadFile['IMG'] = 'jpg.svg';
				break;
			case 'pdf':
				$loadFile['IMG'] = 'pdf.svg';
				break;
			case 'docx':
				$loadFile['IMG'] = 'doc.svg';
				break;
			case 'doc':
				$loadFile['IMG'] = 'doc.svg';
				break;
			case 'png':
				$loadFile['IMG'] = 'png.svg';
				break;
			case 'xlsx':
				$loadFile['IMG'] = 'xml.svg';
				break;
			case 'xls':
				$loadFile['IMG'] = 'xml.svg';
				break;
			default:
				$loadFile['IMG'] = 'no_type.svg';
				break;
		}
		$arF[] = $loadFile;
	}
	$arResult['CUSTOM']['FILES'] = $arF;
}
/* -Получаю файлы- */
?>