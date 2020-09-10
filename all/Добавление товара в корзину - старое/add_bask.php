<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$productID = intval(htmlspecialchars($_POST["id"]));
$quantity = 1;
if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
{
    if (IntVal($productID)>0)
    {
        Add2BasketByProductID(
        $productID,
        $quantity,
        array()
      );
    	echo "1";
    }else {
    	echo "0";
    }
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>