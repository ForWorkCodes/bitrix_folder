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

<?if ($arResult['ERROR_MESSAGE']):?>
    <script>
    $(document).ready(function() {
        <? foreach ($arResult['ERROR_MESSAGE'] as $error): ?>
            $('input[name="<?=$error ?>"]').css('border', '1px solid red');
        <? endforeach ?>
    });
</script>
<?endif;?>

<? if ($arResult["OK_MESSAGE"]): ?>
    <script>
        $('input').val('');
        $('textarea').html('');
        $('.dun-message').addClass('active');
        $('.background').addClass('active');
    </script>
<? endif ?>

<form method="post" action="<?=POST_FORM_ACTION_URI ?>">
    <?= bitrix_sessid_post() ?>
    <input class="customInput" id="fr1" type="text" name="user_name" value="<?=$_POST['user_name'] ?>">
    <input class="customInput phone2" type="text" name="name" value="<?=$_POST['name'] ?>">
    <input class="customInput phonemask" id="fr2" type="text" name="user_phone" value="<?=$_POST['user_phone'] ?>">
    <input class="customInput" id="fr3" type="text" name="user_email" value="<?=$_POST['user_email'] ?>">
    <textarea class="customArea" id="fr4" name="message"><?=$POST['message'] ?></textarea>
    <input class="custom-chekbox" type="checkbox" id="ase1" name="ok" value="<?=$_POST['ok'] ?>" checked>
    <input type="hidden" name="PARAMS_HASH" value="<?=$arResult["PARAMS_HASH"]?>">
    <input type="submit" name="submit" value="Отправить">
</form>
<script>
    $('.phonemask').mask('+7 (999) 999 99 99');
</script>