<?

Class rcaller_client extends CModule
{
    var $MODULE_ID = "rcaller.client";
    var $MODULE_GROUP_RIGHTS = "Y";

    var $errors = false;

    const MODULE_CODE = "rcaller.client";

    const ORDER_PLACED_MODULE_CODE = "sale";
    const ORDER_PLACED_EVENT_NAME = "OnSaleComponentOrderOneStepComplete";
    const ORDER_PLACED_EVENT_HANDLER_CLASS = "\RCaller\general\RCallerClient";
    const ORDER_PLACED_EVENT_HANDLER_METHOD = "sendOrderToRCaller";

    function rcaller_client()
    {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->PARTNER_NAME = "RCaller";
        $this->PARTNER_URI = "https://rcaller.com";

        $this->MODULE_NAME = "RCaller";
        $this->MODULE_DESCRIPTION = "RCaller extension";
    }

    function InstallDB($install_wizard = true)
    {
        global $DB, $DBType, $APPLICATION;

        if (!IsModuleInstalled(self::MODULE_CODE)) {
            RegisterModule(self::MODULE_CODE);
        }

        return true;
    }

    function UnInstallDB($arParams = Array())
    {
        global $DB, $DBType, $APPLICATION;

        if (IsModuleInstalled(self::MODULE_CODE)) {
            UnRegisterModule(self::MODULE_CODE);
        }

        COption::RemoveOption(self::MODULE_CODE);

        return true;
    }

    function InstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible(self::ORDER_PLACED_MODULE_CODE, self::ORDER_PLACED_EVENT_NAME, self::MODULE_CODE, self::ORDER_PLACED_EVENT_HANDLER_CLASS, self::ORDER_PLACED_EVENT_HANDLER_METHOD);

        return true;
    }

    function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler(self::ORDER_PLACED_MODULE_CODE, self::ORDER_PLACED_EVENT_NAME, self::MODULE_CODE, self::ORDER_PLACED_EVENT_HANDLER_CLASS, self::ORDER_PLACED_EVENT_HANDLER_METHOD);

        return true;
    }

    function DoInstall()
    {
        global $APPLICATION, $step;

        if (!IsModuleInstalled(self::MODULE_CODE)) {
            $this->InstallDB();
            $this->InstallEvents();

            $GLOBALS["errors"] = $this->errors;
        }

        return true;
    }

    function DoUninstall()
    {
        if (IsModuleInstalled(self::MODULE_CODE)) {
            $this->UnInstallDB();
            $this->UnInstallEvents();
            $GLOBALS["errors"] = $this->errors;
        }

        return true;
    }
}

?>