<?

use app\helpers\storage;

$dateTime = new \DateTime();

$ds = storage::getInstance();

$ds->apiFolder = 'api';

$ds->time = [
    'dateTime' => $dateTime,
    'timeStamp' => $dateTime->getTimestamp() * 1000,
	'dateTimeString' => $dateTime->format(\DateTimeInterface::ATOM)
];

$ds->docRoot = $_SERVER['DOCUMENT_ROOT'];

$ds->apiLocation = preg_replace(['#documentation#'], [''], $ds->docRoot);

$ds->apiData = [
    'deployments' => $ds->apiLocation . DIRECTORY_SEPARATOR . "data/deployments" . DIRECTORY_SEPARATOR,
    'documents' => $ds->apiLocation . DIRECTORY_SEPARATOR . "data/documents" . DIRECTORY_SEPARATOR,
    'logs' => $ds->apiLocation . DIRECTORY_SEPARATOR . "data/logs/",
];

$ds->apiModules = $ds->apiLocation . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR;

$ds->apiSettings = $ds->apiLocation . DIRECTORY_SEPARATOR . "settings" . DIRECTORY_SEPARATOR;

$ds->simpaLocation = $ds->apiLocation . DIRECTORY_SEPARATOR . "vendor/martinsvb/simpa/";

$ds->web = "https://www.spanielovasvj.cz";

$ds->imgResizeOpt = [
    'bigImgHeight' => 1080,
    'thumbnailHeight' => 200
];

$ds->email = ["spanielovasvj@spanielovasvj.cz"];

$ds->feDev = "http://localhost:3000";

$ds->uniqueId = $ds->getUniqueId();
