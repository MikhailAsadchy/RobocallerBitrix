<?

namespace classes\general;

use Exception;
use Bitrix\Sale;

class rcallerclient
{
    const MODULE_NAME = "rcaller.client";

    const USERNAME_OPTION_NAME = "username";
    const API_KEY_OPTION_NAME = "api_key";

    function __construct()
    {
    }

    function sendOrderToRCaller($orderId, $fieldValues, $arParams)
    {
        try {
            $data = $this->populateRequestBody($orderId, $fieldValues);

            $username = \COption::GetOptionString(self::MODULE_NAME, self::USERNAME_OPTION_NAME);
            $password = \COption::GetOptionString(self::MODULE_NAME, self::API_KEY_OPTION_NAME);

            static::sendOrderToRCallerInternal($data, $username, $password);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * @param $data
     * @param $username
     * @param $password
     * @return int
     */
    public function sendOrderToRCallerInternal($data, $username, $password)
    {
        $rcallerConfig = parse_ini_file("rcaller-config.ini");
        $curl = curl_init($rcallerConfig["rcaller.url"]);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $rcallerConfig["rcaller.connectionTimeOut"]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $httpCode;
    }

    /**
     * @param $basket
     */
    public function getEntriesAsString($basket)
    {
        $entriesAsStrings = [];
        foreach ($basket as $item) {
            $values = $item->getFieldValues();

            $name = $values["NAME"];
            $quantity = intval($values["QUANTITY"]);
            $unit = $values["MEASURE_NAME"];

            $entryString = $name . " " . $quantity . " " . $unit . ".";
            array_push($entriesAsStrings, $entryString);
        }

        return join(" | ", $entriesAsStrings);
    }

    /**
     * @param $orderId
     * @param $fieldValues
     * @return array
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     */
    public function populateRequestBody($orderId, $fieldValues)
    {
        $order = Sale\Order::load($orderId);
        $orderProps = $order->getPropertyCollection();

        $basket = $order->getBasket();
        $entriesAsString = static::getEntriesAsString($basket);

        $userName = $orderProps->getPayerName()->getValue();
        $phone = $orderProps->getPhone()->getValue();
        $address = $orderProps->getAddress()->getValue();

        $data = array(
            'price' => $fieldValues["PRICE"],
            'entries' => $entriesAsString,
            'customerAddress' => $address,
            'customerPhone' => "+" . $phone,
            'customerName' => $userName,
            'priceCurrency' => $fieldValues["CURRENCY"],
            'channel' => "BITRIX");
        return $data;
    }
}

?>