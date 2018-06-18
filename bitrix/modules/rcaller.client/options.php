<?

use rcaller\adapter\BitrixAdaptedIOC;
use rcaller\RCallerConstants;

$module_id = RCallerConstants::MODULE_CODE;
$RIGHT = $APPLICATION->GetGroupRight($module_id);

$ioc = BitrixAdaptedIOC::getIOC();
$rCallerFormHelper = $ioc->getRCallerFormHelper();
$credentialsManager = $ioc->getCredentialsManager();

$checkCredentialsStatus = $rCallerFormHelper->processFormSubmission();
$username = $credentialsManager->getUserName();
$password = $credentialsManager->getPassword();

/**
 * @param $APPLICATION
 * @param $module_id
 */
function getPostUrl($APPLICATION, $module_id)
{
    $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($module_id) ?>&amp;lang=<?= LANGUAGE_ID;
}

if ($RIGHT >= "R") :

    $aTabs = array(
        array("DIV" => "Credentials", "TAB" => "Credentials", "ICON" => $module_id . "_settings", "TITLE" => "Manage your robocaller credentials"),
    );
    $tabControl = new CAdminTabControl("tabControl", $aTabs);

    CModule::IncludeModule($module_id);

    ?>
    <form method="post"
          action="<? getPostUrl($APPLICATION, $module_id); ?>">
        <?
        $tabControl->Begin();
        $tabControl->BeginNextTab();
        ?>

        <tr>
            <td width="40%" nowrap>
                <? echo $rCallerFormHelper->renderUserNameLabel(); ?>
            <td width="60%">
                <? echo $rCallerFormHelper->renderUserNameField($username); ?>
            </td>
        </tr>
        <tr>
            <td width="40%" nowrap>
                <? echo $rCallerFormHelper->renderPasswordLabel(); ?>
            <td width="60%">
                <? echo $rCallerFormHelper->renderPasswordField($password); ?>
            </td>
        </tr>

        <? echo $rCallerFormHelper->renderCheckCredentialsStatus($checkCredentialsStatus); ?>

        <? $tabControl->Buttons(); ?>
        <? echo $rCallerFormHelper->renderCheckCredentialsButton(); ?>
        <? echo $rCallerFormHelper->renderSaveButton(); ?>

        <?= bitrix_sessid_post(); ?>
        <? $tabControl->End(); ?>
    </form>
<? endif; ?>
