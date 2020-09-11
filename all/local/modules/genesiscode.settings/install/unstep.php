<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid())
    return;

if ($errorException = $APPLICATION->getException())
{
    CAdminMessage::showMessage(
        'ошибка при удалении модуля : '.$errorException->GetString()
    );
}
else
{
    CAdminMessage:showNote(
        'модуль успешно удален'
    );
}
?>

<form action="<?= $APPLICATION->getCurPage(); ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID; ?>" />
    <input type="submit" value="К списку модулей">
</form>