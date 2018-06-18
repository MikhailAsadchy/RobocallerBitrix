<?

namespace rcaller\adapter;

use rcaller\lib\ioc\RCallerDependencyContainer;

class BitrixAdaptedIOC
{
    /**
     * @var RCallerDependencyContainer
     */
    private static $ioc;

    public static function getIOC()
    {
        if (self::$ioc === null){
            self::$ioc = new RCallerDependencyContainer(new BitrixEventService(), new BitrixLogger(), new BitrixOptionRepository(), new BitrixChannelNameProvider(), new BitrixOrderEntryFieldResolver());
        }
        return self::$ioc;
    }
}
