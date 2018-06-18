<?

namespace rcaller\adapter;
use rcaller\lib\adapterInterfaces\OrderEntryFieldResolver;

class BitrixOrderEntryFieldResolver implements OrderEntryFieldResolver
{
    public function getName($item)
    {
        return $item->getFieldValues()["NAME"];
    }

    public function getQuantity($item)
    {
        return intval($item->getFieldValues()["QUANTITY"]);
    }

    public function getUnit($item)
    {
        return $item->getFieldValues()["MEASURE_NAME"];
    }
}
