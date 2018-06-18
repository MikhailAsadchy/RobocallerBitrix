<?

namespace rcaller\adapter;
use COption;
use rcaller\lib\adapterInterfaces\OptionRepository;
use rcaller\RCallerConstants;

class BitrixOptionRepository implements OptionRepository
{

    public function addOrUpdateOption($name, $value)
    {
        COption::SetOptionString(RCallerConstants::MODULE_CODE, $name, $value);
    }

    public function removeOption($name)
    {
        COption::RemoveOption(RCallerConstants::MODULE_CODE, $name);
    }

    public function getOption($name)
    {
        return COption::GetOptionString(RCallerConstants::MODULE_CODE, $name);
    }
}
