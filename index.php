<?

include_once('./vendor/autoload.php');

use app\router\router;
use app\exception\excep;
use app\exception\excepAutoLoad;
use app\exception\excepRouter;

include_once('./settings/start.php');

new start();

$excep = new excep();

$process = $_GET['process'] ?? 'api';

if ($process === 'api') {
    try {
        new router();
    } catch (excepAutoLoad $e) {
        $excep->handle($e);
    } catch (excepRouter $e) {
        $excep->handle($e);
    }
}

if ($process === 'documentation') {
    include_once('./vendor/martinsvb/simpa/documentation/index.php');
}
