<?

namespace classes\general;

use Bitrix\Sale\Order;
use Exception;
use rcaller\adapter\BitrixAdaptedIOC;
use rcaller\lib\adapterInterfaces\Logger;
use rcaller\lib\client\RCallerClient;

class RCallerPlaceOrderHandler
{
    const MODULE_NAME = "rcaller.client";

    const USERNAME_OPTION_NAME = "username";
    const API_KEY_OPTION_NAME = "api_key";

    const ENTRIES_DELIMITER = " | ";

    public function onOrderPlaced($orderId, $fieldValues, $arParams)
    {
        $order = self::findOrder($orderId);
        if ($order != null) {
            $orderProps = $order->getPropertyCollection();

            $price = $fieldValues["PRICE"];
            $entries = $order->getBasket();
            $customerAddress = $orderProps->getAddress()->getValue();
            $customerPhone = $orderProps->getPhone()->getValue();
            $customerName = $orderProps->getPayerName()->getValue();
            $priceCurrency = $fieldValues["CURRENCY"];

            BitrixAdaptedIOC::getIOC()->getRCallerClient()->sendOrderToRCaller($price, $entries, $customerAddress, $customerPhone, $customerName, $priceCurrency);
        }
    }

    /**
     * @param $orderId
     * @return Order|null
     */
    private function findOrder($orderId)
    {
        $order = null;
        try {
            $order = Order::load($orderId);
        } catch (Exception $e) {
            BitrixAdaptedIOC::getIOC()->getLogger()->log("error", "cannot find order by id: " . $orderId);
        }
        return $order;
    }
}

