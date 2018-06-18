<?

namespace rcaller\adapter;
use rcaller\lib\adapterInterfaces\EventService;
use rcaller\RCallerConstants;
use Bitrix\Main\EventManager;

class BitrixEventService implements EventService
{
    const ORDER_PLACED_MODULE_CODE = "sale";
    const ORDER_PLACED_EVENT_NAME = "OnSaleComponentOrderOneStepComplete";
    const ORDER_PLACED_EVENT_HANDLER_CLASS = "\classes\general\RCallerPlaceOrderHandler";
    const ORDER_PLACED_EVENT_HANDLER_METHOD = "onOrderPlaced";

    public function subscribePlaceOrderEvent($rcallerClient, $logger)
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible(self::ORDER_PLACED_MODULE_CODE, self::ORDER_PLACED_EVENT_NAME, RCallerConstants::MODULE_CODE, self::ORDER_PLACED_EVENT_HANDLER_CLASS, self::ORDER_PLACED_EVENT_HANDLER_METHOD);
    }

    public function unsubscribePlaceOrderEvent()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(self::ORDER_PLACED_MODULE_CODE, self::ORDER_PLACED_EVENT_NAME, RCallerConstants::MODULE_CODE, self::ORDER_PLACED_EVENT_HANDLER_CLASS, self::ORDER_PLACED_EVENT_HANDLER_METHOD);
    }
}
