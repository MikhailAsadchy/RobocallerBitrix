<?

namespace rcaller\adapter;

use rcaller\lib\util\StrictImporter;

class RCallerAdapterImport
{
    public static function importAdapter()
    {
        $files = array();

        $currentFileLocation = dirname(__FILE__);
        array_push($files, $currentFileLocation . "/BitrixAdaptedIOC.php");
        array_push($files, $currentFileLocation . "/BitrixChannelNameProvider.php");
        array_push($files, $currentFileLocation . "/BitrixEventService.php");
        array_push($files, $currentFileLocation . "/BitrixLogger.php");
        array_push($files, $currentFileLocation . "/BitrixOptionRepository.php");
        array_push($files, $currentFileLocation . "/BitrixOrderEntryFieldResolver.php");

        StrictImporter::importFiles($files);
    }
}
