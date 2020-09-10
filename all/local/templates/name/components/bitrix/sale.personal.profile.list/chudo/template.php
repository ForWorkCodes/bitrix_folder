<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$USER->isAuthorized()) {
	header('Location: /');
}
use Bitrix\Main\Localization\Loc;

if(strlen($arResult["ERROR_MESSAGE"])>0)
{
	ShowError($arResult["ERROR_MESSAGE"]);
}
if(strlen($arResult["NAV_STRING"]) > 0)
{
	?>
	<p><?=$arResult["NAV_STRING"]?></p>
	<?
}

if (count($arResult["PROFILES"]))
{
	?>
	<div class="profileTab">
		<div class="profileTab__row profileTab__row-gray">
			<?
			$dataColumns = array(
				"ID", "DATE_UPDATE", "NAME", "PERSON_TYPE_ID"
			);
			foreach ($dataColumns as $column)
			{
				?>
				<? if ($column == 'ID'): ?>
					<div class="profileTab__cell">
				<? else: ?>
					<div class="profileTab__cell profileTab__cel-double">
				<? endif ?>

					<div class="profileTab__name"><?=Loc::getMessage("P_".$column)?></div>
					<div class="arrowSort">
						<a class="sortCol sortCol-reverse sale-personal-profile-list-arrow-up" href="<?=$arResult['URL']?>by=<?=$column?>&order=asc#nav_start">
							<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/img/svg/arrow.html', array(), array())?>
						</a>
						<a class="sortCol sale-personal-profile-list-arrow-down" href="<?=$arResult['URL']?>by=<?=$column?>&order=desc#nav_start">
							<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/img/svg/arrow.html', array(), array())?>
						</a>
					</div>
				</div>
				<?
			}
			?>
		</div>
		<?foreach($arResult["PROFILES"] as $val)
		{
			?>
			<div class="profileTab__row">
				<div class="profileTab__cell">
					<div class="count-numer">
						<?= $val["ID"] ?>
					</div>
				</div>
				<div class="profileTab__cell profileTab__cel-double">
					<div class="profileTab__simpleInfo">
						<?= $val["DATE_UPDATE"] ?>
					</div>
				</div>
				<div class="profileTab__cell profileTab__cel-double">
					<div class="profileTab__simpleInfo">
						<?= $val["NAME"] ?>
					</div>
				</div>
				<div class="profileTab__cell profileTab__cel-double">
					<div class="profileTab__simpleInfo">
						<?= $val["PERSON_TYPE"]["NAME"] ?>
					</div>
				</div>
				<div class="profileTab__cell">
					<a class="custom-link" href="<?= $val["URL_TO_DETAIL"] ?>">
						Изменить
					</a>
				</div>
				<div class="profileTab__cell removeX">
					<a class="delLine" href="javascript:if(confirm('<?= Loc::getMessage("STPPL_DELETE_CONFIRM") ?>')) window.location='<?= $val["URL_TO_DETELE"] ?>'">
						<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/img/svg/close.html', array(), array())?>
					</a>
				</div>
			</div>
			<?
		}?>
		<?
		if(strlen($arResult["NAV_STRING"]) > 0)
		{
			?>
			<p><?=$arResult["NAV_STRING"]?></p>
			<?
		}
		?>
	</div>
	<?
}
else
{
	?>
	<h3><?=Loc::getMessage("STPPL_EMPTY_PROFILE_LIST") ?></h3>
	<?
}
?>