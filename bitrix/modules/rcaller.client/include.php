<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rcaller.client/lib/RCallerImport.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rcaller.client/adapter/RCallerAdapterImport.php");

use rcaller\adapter\RCallerAdapterImport;
use rcaller\lib\RCallerImport;

RCallerImport::importRCallerLib();
RCallerAdapterImport::importAdapter();
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rcaller.client/classes/general/RCallerPlaceOrderHandler.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rcaller.client/RCallerConstants.php");
