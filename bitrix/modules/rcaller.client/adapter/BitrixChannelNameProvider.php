<?

namespace rcaller\adapter;
use rcaller\lib\adapterInterfaces\ChannelNameProvider;

class BitrixChannelNameProvider implements ChannelNameProvider
{
    public function getChannelName()
    {
        return "Bitrix";
    }
}
