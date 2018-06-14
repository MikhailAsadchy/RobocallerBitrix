<?
$module_id = "rcaller.client";
$RIGHT = $APPLICATION->GetGroupRight($module_id);

$userNameFieldName = "username";
$apiKeyFieldName = "api_key";

$userNameFieldValue = null;
$apiKeyFieldValue = null;

$checkCredentialsResult = "";

/**
 * @param $arAllOptions
 * @param $module_id
 */
function saveOptions($arAllOptions, $module_id)
{
    foreach ($arAllOptions as $arOption) {
        $name = $arOption[0];
        $val = $_REQUEST[$name];
        if ($arOption[2][0] == "checkbox" && $val != "Y")
            $val = "N";
        COption::SetOptionString($module_id, $name, $val, $arOption[1]);
    }
}

/**
 * @param $arAllOptions
 * @param $fieldName
 * @return string
 */
function getFormValue($arAllOptions, $fieldName)
{
    foreach ($arAllOptions as $arOption) {
        $name = $arOption[0];
        $val = $_REQUEST[$name];
        if ($arOption[2][0] == "checkbox" && $val != "Y")
            $val = "N";

        if ($name === $fieldName) {
            return $val;
        }
    }
}

/**
 * @param $APPLICATION
 * @param $module_id
 * @param $tabControl
 */
function selfRedirect($APPLICATION, $module_id, $tabControl)
{
    LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . urlencode($module_id) . "&lang=" . urlencode(LANGUAGE_ID) . "&" . $tabControl->ActiveTabParam());
}

/**
 * @param $APPLICATION
 * @param $module_id
 */
function getPostUrl($APPLICATION, $module_id)
{
    $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($module_id) ?>&amp;lang=<?= LANGUAGE_ID;
}

/**
 * @return array
 */
function getCheckCredentialsSampleOrder()
{
    $data = array(
        'price' => "0.1",
        'entries' => "check credentials entry",
        'customerAddress' => "check credentials address",
        'customerPhone' => "+375292765123",
        'customerName' => "Credentials checker",
        'priceCurrency' => "RUB",
        'channel' => "BITRIX credentials checker");
    return $data;
}

/**
 * @param $data
 * @param $userNameFieldValue
 * @param $apiKeyFieldValue
 * @return int
 */
function sendOrderToRCaller($data, $userNameFieldValue, $apiKeyFieldValue)
{
    $rcallerclient = new \classes\general\rcallerclient();
    $httpCode = $rcallerclient->sendOrderToRCallerInternal($data, $userNameFieldValue, $apiKeyFieldValue);
    return $httpCode;
}

/**
 * @param $httpCode
 * @return string
 */
function processResponse($httpCode)
{
    if ($httpCode === 200) {
        $checkCredentialsResult = "success";
    } else if ($httpCode === 404) {
        $checkCredentialsResult = "bad credentials";
    } else {
        $checkCredentialsResult = "unknown error";
    }
    return $checkCredentialsResult;
}

if ($RIGHT >= "R") :

    $arAllOptions = Array(
        array($userNameFieldName, "Customer name", array("text", 25)),
        array($apiKeyFieldName, "Api key", array("password", 25))
    );

    $aTabs = array(
        array("DIV" => "Credentials", "TAB" => "Credentials", "ICON" => $module_id . "_settings", "TITLE" => "Manage your robocaller credentials"),
    );
    $tabControl = new CAdminTabControl("tabControl", $aTabs);

    CModule::IncludeModule($module_id);

    if ($REQUEST_METHOD == "POST" && strlen($Save . $CheckCredentials) > 0 && $RIGHT == "W" && check_bitrix_sessid()) {
        if ($Save != null) {
            saveOptions($arAllOptions, $module_id);
            selfRedirect($APPLICATION, $module_id, $tabControl);
        } else if ($CheckCredentials != null) {
            $userNameFieldValue = getFormValue($arAllOptions, $userNameFieldName);
            $apiKeyFieldValue = getFormValue($arAllOptions, $apiKeyFieldName);

            $data = getCheckCredentialsSampleOrder();
            $httpCode = sendOrderToRCaller($data, $userNameFieldValue, $apiKeyFieldValue);
            $checkCredentialsResult = processResponse($httpCode);
        }
    }

    ?>
    <form method="post"
          action="<? getPostUrl($APPLICATION, $module_id); ?>">
        <?
        $tabControl->Begin();
        $tabControl->BeginNextTab();

        foreach ($arAllOptions as $arOption):
            $val = null;
            $fieldName = $arOption[0];
            if ($fieldName === $userNameFieldName && $userNameFieldValue != null) {
                $val = $userNameFieldValue;
            } else if ($fieldName === $apiKeyFieldName && $apiKeyFieldValue != null) {
                $val = $apiKeyFieldValue;
            } else {
                $val = COption::GetOptionString($module_id, $fieldName);
            }
            $type = $arOption[2];
            ?>
            <tr>
                <td width="40%" nowrap <? if ($type[0] == "textarea") echo 'class="adm-detail-valign-top"' ?>>
                    <label for="<? echo htmlspecialcharsbx($arOption[0]) ?>"><? echo $arOption[1] ?>:</label>
                <td width="60%">
                    <? if ($type[0] == "checkbox"): ?>
                        <input type="checkbox" name="<? echo htmlspecialcharsbx($arOption[0]) ?>"
                               id="<? echo htmlspecialcharsbx($arOption[0]) ?>" value="Y"<? if ($val == "Y") echo " checked"; ?>>
                    <? elseif ($type[0] == "text"): ?>
                        <input type="text" size="<? echo $type[1] ?>" maxlength="255" value="<? echo htmlspecialcharsbx($val) ?>"
                               name="<? echo htmlspecialcharsbx($arOption[0]) ?>"
                               id="<? echo htmlspecialcharsbx($arOption[0]) ?>">
                    <? elseif ($type[0] == "password"): ?>
                        <input type="password" size="<? echo $type[1] ?>" maxlength="255"
                               value="<? echo htmlspecialcharsbx($val) ?>"
                               name="<? echo htmlspecialcharsbx($arOption[0]) ?>"
                               id="<? echo htmlspecialcharsbx($arOption[0]) ?>">
                    <? elseif ($type[0] == "textarea"): ?>
                        <textarea rows="<? echo $type[1] ?>" cols="<? echo $type[2] ?>"
                                  name="<? echo htmlspecialcharsbx($arOption[0]) ?>"
                                  id="<? echo htmlspecialcharsbx($arOption[0]) ?>"><? echo htmlspecialcharsbx($val) ?></textarea>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>

        <span>Check credentials status: <? echo $checkCredentialsResult ?></span>

        <? $tabControl->Buttons(); ?>
        <input type="submit" name="Save" value="Save" title="Save" class="adm-btn-save">
        <input type="submit" name="CheckCredentials" value="Check credentials" title="Check credentials">

        <?= bitrix_sessid_post(); ?>
        <? $tabControl->End(); ?>
    </form>
<? endif; ?>
