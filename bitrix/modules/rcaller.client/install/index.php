<?
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rcaller.client/include.php");
use rcaller\adapter\BitrixAdaptedIOC;
use rcaller\RCallerConstants;

Class rcaller_client extends CModule
{
    var $MODULE_ID = "rcaller.client";
    var $MODULE_GROUP_RIGHTS = "Y";

    var $errors = false;

    function __construct()
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

        if (!IsModuleInstalled(RCallerConstants::MODULE_CODE)) {
            RegisterModule(RCallerConstants::MODULE_CODE);
        }

        return true;
    }

    function UnInstallDB($arParams = Array())
    {
        global $DB, $DBType, $APPLICATION;

        if (IsModuleInstalled(RCallerConstants::MODULE_CODE)) {
            UnRegisterModule(RCallerConstants::MODULE_CODE);
        }

        BitrixAdaptedIOC::getIOC()->getPluginManager()->removeOptions();

        return true;
    }

    function InstallEvents()
    {
        BitrixAdaptedIOC::getIOC()->getPluginManager()->subscribePlaceOrderEvent();

        return true;
    }

    function UnInstallEvents()
    {
        BitrixAdaptedIOC::getIOC()->getPluginManager()->unsubscribePlaceOrderEvent();

        return true;
    }

    function DoInstall()
    {
        global $APPLICATION, $step;

        if (!IsModuleInstalled(RCallerConstants::MODULE_CODE)) {
            $this->InstallDB();
            $this->InstallEvents();

            $GLOBALS["errors"] = $this->errors;
        }

        return true;
    }

    function DoUninstall()
    {
        if (IsModuleInstalled(RCallerConstants::MODULE_CODE)) {
            $this->UnInstallDB();
            $this->UnInstallEvents();
            $GLOBALS["errors"] = $this->errors;
        }

        return true;
    }
}

