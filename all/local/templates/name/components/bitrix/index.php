<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");?>
<main>
<?
if ($_GET['exel'] == 'Y') {
	if (!CModule::IncludeModule("nkhost.phpexcel")) die();
    global $PHPEXCELPATH;      
	 // Подключаем класс для работы с excel
	require_once($PHPEXCELPATH.'/PHPExcel.php');
	// Подключаем класс для вывода данных в формате excel
	require_once($PHPEXCELPATH.'/PHPExcel/Writer/Excel5.php');
	 
	// Создаем объект класса PHPExcel
	$xls = new PHPExcel();
	// Устанавливаем индекс активного листа
	$xls->setActiveSheetIndex(0);
	// Получаем активный лист
	$sheet = $xls->getActiveSheet();
	// Подписываем лист
	$sheet->setTitle('Таблица первая');
	 
	// Вставляем текст в ячейку A1
	$sheet->setCellValue("A1", 'Тестовая таблица');
	$sheet->getStyle('A1')->getFill()->setFillType(
	    PHPExcel_Style_Fill::FILL_SOLID);
	$sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('EEEEEE');
	 
	// Объединяем ячейки
	$sheet->mergeCells('A1:H1');
	 
	// Выравнивание текста
	$sheet->getStyle('A1')->getAlignment()->setHorizontal(
	    PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	 
	for ($i = 2; $i < 10; $i++) {
	    for ($j = 2; $j < 10; $j++) {
	        // Выводим таблицу умножения
	        $sheet->setCellValueByColumnAndRow(
	                                          $i - 2,
	                                          $j,
	                                          $i . "x" .$j . "=" . ($i*$j));
	        // Применяем выравнивание
	        $sheet->getStyleByColumnAndRow($i - 2, $j)->getAlignment()->
	                setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	    }
	}
	$objWriter = \PHPExcel_IOFactory::createWriter($xls, 'Excel5');
	$objWriter->save("table.xls");
	header('Location: table.xls');
}
?>
<?
if ($_GET['copy'] == 'Y') {
	$iSection = new CIBlockSection;
	$parentSections = $iSection->GetList(
		[],
		['IBLOCK_ID' => 1, 'ACTIVE' => 'Y'],
		false,
		['*', 'UF_*']
	);
	$i = 0;
	while ($parentSection = $parentSections->GetNext()) {
		$i++;
		$field = [
			'IBLOCK_ID' => 9,
			'NAME' => $parentSection['NAME'],
			'CODE' => $parentSection['CODE'],
			'ACTIVE' => 'Y',
			'UF_ID' => $parentSection['UF_ID']
		];
		if ($iSection->Add($field)) {}
	}
}
?>	
</main>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>