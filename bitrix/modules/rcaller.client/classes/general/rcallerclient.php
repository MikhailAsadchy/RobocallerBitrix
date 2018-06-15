<?

namespace classes\general;

use classes\validation\ValidationResult;
use Exception;
use Bitrix\Sale;

class rcallerclient
{
    const MODULE_NAME = "rcaller.client";

    const USERNAME_OPTION_NAME = "username";
    const API_KEY_OPTION_NAME = "api_key";

    const ENTRIES_DELIMITER = " | ";

    function __construct()
    {
    }

    private static function validateCustomerPhone($data, $validationResult)
    {
        $customerPhone = $data["customerPhone"];
        $phonePattern = "/^((\+7)([0-9]){7,14})$/";
        $matches = preg_match($phonePattern, $customerPhone);
        if (!$matches) {
            $validationResult->addError("customerPhone", "phone must match regular expression " . $phonePattern);
        }
    }

    private static function processEntries($data)
    {
        $entries = $data["entries"];
        $length = strlen($entries);

        if ($length > 1024) {
            $data["entries"] =  substr($entries, 0, 1024);
        }
    }


    private static function processHttpCode($httpCode)
    {
        if ($httpCode == 400) {
            error_log("RCaller: bad request was sent");
        } else if ($httpCode == 401) {
            error_log("RCaller: bad credentials");
        } else if ($httpCode == 403) {
            error_log("RCaller: negative balance");
        }
    }

    private static function validatePriceCurrency($data, $validationResult)
    {
        $priceCurrency = $data["priceCurrency"];

        $isEmpty = empty($priceCurrency);
        if ($isEmpty) {
            $validationResult->addError("priceCurrency", "priceCurrency field should not be empty");
        }

        $length = strlen($priceCurrency);
        if ($length > 5) {
            $validationResult->addError("priceCurrency", "priceCurrency field length should be 1-5");
        }
    }

    private static function validatePrice($data, $validationResult)
    {
        $price = $data["price"];
        $isNumeric = is_numeric($price);
        if (!$isNumeric) {
            $validationResult->addError("price", "price field should be a number, but was: " . $price);
        }
    }

    private static function validateCustomerNameField($data, $validationResult)
    {
        $customerName = $data["customerName"];

        $isEmpty = empty($customerName);
        if ($isEmpty) {
            $validationResult->addError("customerName", "customerName field should not be empty");
        }

        $length = strlen($customerName);
        if ($length > 255) {
            $validationResult->addError("customerName", "customerName field length should be 1-255");
        }
    }

    private static function validateCustomerAddressField($data, $validationResult)
    {
        $customerAddress = $data["customerAddress"];

        $isEmpty = empty($customerAddress);
        if ($isEmpty) {
            $validationResult->addError("customerAddress", "customerAddress field should not be empty");
        }

        $length = strlen($customerAddress);
        if ($length > 255) {
            $validationResult->addError("customerAddress", "customerAddress field length should be 1-255");
        }
    }

    private static function validateEntriesField($data, $validationResult)
    {
        $entries = $data["entries"];
        $isEmpty = empty($entries);
        if ($isEmpty) {
            $validationResult->addError("entries", "entries field should not be empty");
        }

        $length = strlen($entries);
        if ($length > 1024) {
            $validationResult->addError("entries", "entries field length should be 1-1024");
        }
    }

    /**
     * @param $data
     */
    private static function sanitizeCustomerPhone($data)
    {
        $customerPhone = $data["customerPhone"];
        $phoneChars = str_split($customerPhone);
        $phoneNumbers = array();
        foreach ($phoneChars as $char) {
            if (is_numeric($char)) {
                array_push($phoneNumbers, $char);
            }
        }
        $phoneNumber = "+" . implode($phoneNumbers);
        $data["customerPhone"] = $phoneNumber;
    }

    private static function processRequestBody($data)
    {
        self::sanitizeCustomerPhone($data);
        self::processEntries($data);
    }

    function sendOrderToRCaller($orderId, $fieldValues, $arParams)
    {
        try {
            $data = static::populateRequestBody($orderId, $fieldValues);
            static::processRequestBody($data);
            $validationResult = static::validateRequestBody($data);

            if (!$validationResult->hasErrors()) {
                $username = \COption::GetOptionString(self::MODULE_NAME, self::USERNAME_OPTION_NAME);
                $password = \COption::GetOptionString(self::MODULE_NAME, self::API_KEY_OPTION_NAME);
                $httpCode = static::sendOrderToRCallerInternal($data, $username, $password);

                static::processHttpCode($httpCode);
            } else {
                error_log($validationResult);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    private static function sendOrderToRCallerInternal($data, $username, $password)
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
     * @param $username
     * @param $password
     * @return int
     */
    public static function checkRCallerCredentials($username, $password)
    {
        $rcallerConfig = parse_ini_file("rcaller-config.ini");
        $curl = curl_init($rcallerConfig["rcaller.ping.url"]);
        curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $rcallerConfig["rcaller.connectionTimeOut"]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $httpCode;
    }

    /**
     * @param $basket
     * @return string
     */
    private static function getEntriesAsString($basket)
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

        return join(self::ENTRIES_DELIMITER, $entriesAsStrings);
    }

    /**
     * @param $orderId
     * @param $fieldValues
     * @return array
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     */
    private static function populateRequestBody($orderId, $fieldValues)
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

    private static function validateRequestBody($data)
    {
        $validationResult = new ValidationResult();

        // todo[Mikhail_Asadchy] comment out during pre-prod testing
//        self::validateCustomerPhone($data, $validationResult);
        self::validatePrice($data, $validationResult);
        self::validateEntriesField($data, $validationResult);
        self::validateCustomerAddressField($data, $validationResult);
        self::validateCustomerNameField($data, $validationResult);
        self::validatePriceCurrency($data, $validationResult);

        return $validationResult;
    }
}

?>