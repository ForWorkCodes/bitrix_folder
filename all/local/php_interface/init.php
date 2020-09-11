<?
session_start();
CModule::AddAutoloadClasses(
    '', // не указываем имя модуля
    array(
       // ключ - имя класса, значение - путь относительно корня сайта к файлу с классом
            'CMyClassName1' => '/path/cmyclassname1file.php',
            'CMyClassName2' => '/path/cmyclassname2file.php',
    )
);

function prr($arr)
{
	echo '<pre>',print_r($arr, 1),'</pre>';
}