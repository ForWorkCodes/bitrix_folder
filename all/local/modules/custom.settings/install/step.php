<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid())
    return;

if ($errorException = $APPLICATION->getException())
{
    CAdminMessage::showMessage(
        'ошибка при установке модуля : '.$errorException->GetString()
    );
}
else
{
    CAdminMessage::showNote(
        'модуль успешно установлен'
    );
}
?>

<form action="<?= $APPLICATION->getCurPage(); ?>"> <!-- Кнопка возврата к списку модулей -->
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID; ?>" />
    <input type="submit" value="К списку модулей">
</form>