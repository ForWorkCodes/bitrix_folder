<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * Bitrix vars
 *
 * @var array                    $arParams
 * @var array                    $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain                 $APPLICATION
 * @global CUser                 $USER
 */
?>
<div class="pure-form  introduction__pure-form">
    <? if (!empty($arResult["ERROR_MESSAGE"])) {
        foreach ($arResult["ERROR_MESSAGE"] as $v) {
            $str = "<script>";
            $str .= "if(document.querySelector('.pure-form *[name=$v]'))";
            $str .= "document.querySelector('.pure-form *[name=$v]').style.borderColor = 'red'; ";
            $str .= "</script>";
            echo $str;
        }
    }
    if (strlen($arResult["OK_MESSAGE"]) > 0) {
        ?>
        <script>
			document.getElementById('modal-text').innerText = '<?= $arResult["OK_MESSAGE"]?>';
			document.querySelector('.modal-form').classList.add('_active');
        </script>
        <?
    }
    ?>
    <div class="pure-form__header"><?= $arResult['HEADER_FORM'] ?></div>
    <form action="<?= POST_FORM_ACTION_URI ?>" method="POST" class="pure-form__action">
        <?= bitrix_sessid_post() ?>
        <input class="pure-form__field" name="user_name"
               placeholder="Имя <? if (empty($arParams["REQUIRED_FIELDS"]) || in_array("NAME",
                       $arParams["REQUIRED_FIELDS"])): ?>*<? endif; ?>">
        <input type="tel" class="order-form__field hid" value="" maxlength="15" name="PHONE_2" />
        <input type="tel" class="pure-form__field" maxlength="15" name="PHONE"
               placeholder="Телефон <? if (empty($arParams["REQUIRED_FIELDS"]) || in_array("PHONE",
                       $arParams["REQUIRED_FIELDS"])): ?>*<? endif; ?>">
        <input type="email" class="pure-form__field" name="user_email"
               placeholder="E-mail <? if (empty($arParams["REQUIRED_FIELDS"]) || in_array("EMAIL",
                       $arParams["REQUIRED_FIELDS"])): ?>*<? endif; ?>">
        <textarea class="pure-form__area" name="MESSAGE"
                  placeholder="Маршрут, краткое описание груза,
требуемая услуга.
Например: Тюмень - Москва, перевезти мебель, примерный вес 1000 кг, объем 12 м3 <? if (empty($arParams["REQUIRED_FIELDS"]) || in_array("MESSAGE",
                          $arParams["REQUIRED_FIELDS"])): ?>*<? endif; ?>"></textarea>

        <? if ($arParams["USE_CAPTCHA"] == "Y"): ?>
            <div class="mf-captcha">
                <div class="mf-text"><?= GetMessage("MFT_CAPTCHA") ?></div>
                <input type="hidden" name="captcha_sid" value="<?= $arResult["capCode"] ?>">
                <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["capCode"] ?>" width="180" height="40"
                     alt="CAPTCHA">
                <div class="mf-text"><?= GetMessage("MFT_CAPTCHA_CODE") ?><span class="mf-req">*</span></div>
                <input name="captcha_word" size="30" maxlength="50" value="">
            </div>
        <? endif; ?>
		<input type="hidden" name="page" value="<?= $_SERVER['HTTP_HOST'] . $APPLICATION->GetCurPage(); ?>">
		<input type="hidden" name="service" value="<?= $_POST['service'] == '' ? 'Форма на главной' : $_POST['service']; ?>">
		<input type="hidden" name="page_form" value="Заказ перевозки из формы на главной">
        <input type="hidden" name="PARAMS_HASH" value="<?= $arResult["PARAMS_HASH"] ?>">
        <input type="submit" name="submit" id="pure-submit" class="pure-form__submit">
    </form>
    <label for="pure-submit" id="main-form" class="pure-form__footer"><?= $arResult['SUBMIT_TEXT']; ?></label>
</div>
