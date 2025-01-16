<?php

chdir(__DIR__);

define('CONFIG_PATH', __DIR__ . '/config/config.ini');
define('CACHE_DIR', __DIR__ . '/cache');

require_once __DIR__ . '/src/Autoload.php';

use App\HEStub;
use App\Model\Record;
use Lib\ArgsParser;
use Lib\Http\Client;
use Lib\Http\Cache\Cache;

$arg    = new ArgsParser($argv, HEStub::getArgsOptions());
$rec    = new Record();
$client = new Client(null, $arg->get('debug'));
$cache  = new Cache($client, $arg->get('debug'));

// Set cache to active
$cache->setActive(true);
$client->setCacheHandler($cache);

// Set options in record entity
$rec->setDomain($arg->get('d'));
$rec->setHost($arg->get('h'));
$rec->setRecordType($arg->get('t'));
$rec->setAdditional($arg->get('a'));
$rec->setForceUpdate($arg->get('force'));
$rec->setDelete($arg->get('delete'));

// Create HEStub
$he = new HEStub($client, $rec, parse_ini_file(CONFIG_PATH, true));

// If help is needed
if ($arg->get('help')) {
    $arg->showHelp();
}

// Title/Initialization
$arg->writeBlankLine();
$arg->write('HostEurope DNS Updater', 'headline');
$arg->writeBlankLine();

// Get current ip address
$arg->write('Receiving current ip address...');
$ip = $he->getCurrentIp();
$arg->write('Done.');

try {

    // Parse options and process them
    $arg->writeBlankLine();
    $arg->write('Processing options...');
    $he->processOptions();

} catch (\Exception $e) {

    $arg->write($e->getMessage(), 'error');
    exit(1);

}

// Processing of options went fine
$arg->write('Done.');

// All done
$arg->write('Changes were successfully applied', 'success');
$arg->writeBlankLine();

exit(0);
