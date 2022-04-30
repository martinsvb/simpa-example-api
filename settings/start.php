<?

use app\helpers\storage;

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'appData.php');

/**
 *  Application start
 */
class start
{
    /**
     *  Set error reporting
     *  Set internal encoding
     *  Run Autoloading
     */
    public function __construct()
    {
        if (!ini_get('display_errors')) {
            ini_set('display_errors', 1);
        }

        mb_internal_encoding('UTF-8');
    }
    
    /**
     *  Register autoload function
     */
    private function _runAutoloading()
    {
        /**
         *  Autoload process
         */
        function autoload($class)
        {
            if (str_contains($class, 'Firebase\JWT')) {
                $class = preg_replace('#Firebase\/JWT#', 'jwt/src', $class);
            }

            $ds = storage::getInstance();

            $fullPath = null;

            if (str_contains($class, 'app')) {
                $fullPath = str_replace(
                    "app" . DIRECTORY_SEPARATOR,
                    NULL,
                    $ds->apiLocation
                ) . "$class.php";
            }
            if (str_contains($class, 'modules')) {
                $fullPath = str_replace(
                    "modules" . DIRECTORY_SEPARATOR,
                    NULL,
                    $ds->apiModules
                ) . "$class.php";
            }

            if ($fullPath && !include_once($fullPath)) {
                throw new app\exception\excepAutoLoad("Class loaded error: $class");
            }
        }
        
        spl_autoload_register("autoload");
    }
}

function printArr($arr)
{
    echo "<pre>";
    print_r ($arr);
    echo "</pre>";
}
