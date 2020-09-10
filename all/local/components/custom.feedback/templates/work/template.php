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
<? if (!empty($arResult["ERROR_MESSAGE"])) {
	?><pre><? print_r($arResult["ERROR_MESSAGE"]) ?></pre><?
    foreach ($arResult["ERROR_MESSAGE"] as $v) {
        $str = "<script>";
        $str .= "if(document.querySelector('.Apage-form._js-error *[name=$v]'))";
        $str .= "document.querySelector('.Apage-form._js-error *[name=$v]').style.borderColor = 'red'; ";
        $str .= "</script>";
        echo $str;
    }
}
if (strlen($arResult["OK_MESSAGE"]) > 0) {
    ?>
	<script>
        $('.modal-fog').addClass('_active');
        $('.fast-dun').addClass('_active');
	</script>
    <?
}
?>
<?$rsUser = CUser::GetByID($USER->GetID());
$arUser = $rsUser->Fetch();?>

<form action="<?= POST_FORM_ACTION_URI ?>" method="POST" class="Apage-form _empty _js-error contact__form">
    <?= bitrix_sessid_post() ?>
    <? 
        $field = (!empty($arResult['AUTHOR_NAME'])) ? $arResult['AUTHOR_NAME'] : '' ;
        $req = array( 'required' => '', 'star'     => '');
        if (empty($arParams["REQUIRED_FIELDS"]) || in_array("NAME", $arParams["REQUIRED_FIELDS"]))
        {
            $req = array( 'required' => 'required', 'star' => '*');
        }
    ?>
	<div class="contact__container">
        <span class="contact__name-input">Имя<?=$req['star']?></span> <input type="text" name="user_name" value="<?= $field ?>" class="contact__input" <?=$req['required']?>>
    </div>
    <div class="contact__container">
        <?
            $field = (!empty($arResult['PHONE'])) ? $arResult['PHONE'] : '' ; 
            $req = array( 'required' => '', 'star'     => '');
            if (empty($arParams["REQUIRED_FIELDS"]) || in_array("PHONE", $arParams["REQUIRED_FIELDS"]))
            {
                $req = array( 'required' => 'required', 'star' => '*');
            }
        ?>
        <span class="contact__name-input">Телефон<?=$req['star']?></span> <input type="text" name="PHONE" value="<?= $field ?>" class="contact__input phonemask" <?=$req['required']?>>
        <?
            $field = (!empty($arResult['PHONE_2'])) ? $arResult['PHONE_2'] : '' ; 
        ?>
        <input type="text" name="PHONE_2" class="contact__input hid" value="<?= $field ?>">
    </div>
    <div class="contact__container">
         <?
            $field = (!empty($arResult['AUTHOR_EMAIL'])) ? $arResult['AUTHOR_EMAIL'] : '' ; 
            $req = array( 'required' => '', 'star'     => '');
            if (empty($arParams["REQUIRED_FIELDS"]) || in_array("EMAIL", $arParams["REQUIRED_FIELDS"]))
            {
                $req = array( 'required' => 'required', 'star' => '*');
            }
        ?>
        <span class="contact__name-input">E-mail<?=$req['star']?></span> <input type="text" name="user_email" value="<?= $field ?>" class="contact__input" <?=$req['required']?>>
    </div>
    <div class="contact__container-width">
        <?
            $field = (!empty($arResult['MESSAGE'])) ? $arResult['MESSAGE'] : '' ; 
            $req = array( 'required' => '', 'star'     => '');
            if (empty($arParams["REQUIRED_FIELDS"]) || in_array("MESSAGE", $arParams["REQUIRED_FIELDS"]))
            {
                $req = array( 'required' => 'required', 'star' => '*');
            }
        ?>
        <span class="contact__name-input">Вопрос<?=$req['star']?></span> <input type="text" name="MESSAGE" value="<?= $field ?>" class="contact__input _width" <?=$req['required']?>>
    </div>
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
	<input type="submit" name="submit" id="Apage-form_sb-1" class="Apage-form__submit contact__button contact__button-name contact__button" value="Отправить">
	<input type="hidden" name="PARAMS_HASH" value="<?= $arResult["PARAMS_HASH"] ?>">
</form>
<script type="text/javascript">
    $(document).ready(function(){
        $('.phonemask').mask("+7(999) 999-99-99");
    })
</script>