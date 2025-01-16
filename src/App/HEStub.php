<?php

namespace App;

use App\Abstracts\AbstractStub;
use Lib\Http\Client;
use App\Model\Record;
use Lib\Http\Cache\Cache;
use Lib\Http\Model\Response;

/**
 * Class HEStub
 */
class HEStub extends AbstractStub
{

    /** @var Client */
    protected $client;

    /** @var Record */
    protected $record;

    /** @var array */
    protected $config;

    /** @var array */
    protected $recordsMapping = [
        'A'     => 0,
        //'AAAA'  => 28, Needs more logic because its reserved for ipv6 addresses
        'TXT'   => 11,
        'CNAME' => 10
    ];

    /**
     * HEStub constructor.
     *
     * @param Client $client
     * @param Record $record
     * @param array $config
     */
    public function __construct(Client $client, Record $record, array $config = null)
    {
        $this->client = $client;
        $this->record = $record;
        $this->config = $config;
    }

    /**
     * Process every script parameter and determine record strategy (create, update or delete)
     *
     * @return Response
     */
    public function processOptions()
    {
        if (!$this->isLoggedIn()) {
            throw new \InvalidArgumentException('Couldn\'t login into account, please check credentials in ./config/config.ini');
        }

        $this->client->getCacheHandler()->setActive(true);

        $domains  = $this->getDomains();
        $hostList = $this->getDomainHostsList();

        $this->validateOptions($domains);

        $host     = $this->record->getHost() . '.' . $this->record->getDomain();
        $entry    = isset($hostList[$host]) ? $hostList[$host] : null;
        $strategy = 'update';

        if ($entry === null) {
            $strategy = 'create';
        }

        if ($this->record->getDelete()) {
            $strategy = 'delete';
        }

        switch ($strategy) {

            case 'create':
                $result = $this->createRecord();
                break;

            case 'update':
                $result = $this->updateRecord($entry);
                break;

            case 'delete':
                $result = $this->deleteRecord($entry);
                break;

            default:
                exit(1);
                break;

        }

        return $result;
    }

    /**
     * Validate option parameter
     *
     * @param $domains
     */
    protected function validateOptions(array $domains)
    {
        if (!in_array($this->record->getDomain(), $domains)) {
            throw new \InvalidArgumentException('No valid domain name given: "' . $this->record->getDomain() . '"');
        }

        if (!empty($this->record->getRecordType())
            && !isset($this->recordsMapping[$this->record->getRecordType()])
        ) {
            throw new \InvalidArgumentException('No valid record type given: "' . $this->record->getRecordType() . '"');
        }

        if ($this->record->getRecordType() === 'TXT' && empty($this->record->getAdditional())) {
            throw new \InvalidArgumentException('Missing additional value for TXT record (did you forget to append "-a"?)');
        }

        if ($this->record->getRecordType() === 'CNAME' && empty($this->record->getAdditional())) {
            throw new \InvalidArgumentException('Missing alias in CNAME record (did you forget to append "-a"?)');
        }
    }

    /**
     * Returns current ip address
     *
     * @return string
     */
    public function getCurrentIp()
    {
        $this->client->getCacheHandler()->setActive(false);
        $response = $this->client->send('https://api.ipify.org/');
        $this->record->setPointer($response->getContent());
        return $response;
    }

    /**
     * Returns current login status
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        $this->client->getCacheHandler()->setActive(false);
        return !empty($this->getDomains());
    }

    /**
     * Allowed arguments for console options
     *
     * @return array
     */
    public static function getArgsOptions()
    {
        return [
            '-d'       => 'Select the domain name on which the changes should be applied',
            '-h'       => 'Define the host name which should be updated/created',
            '-t'       => 'Define a record type for update (i.e. A or TXT)',
            '-a'       => 'Handle additional option values, written INSIDE QUOTES (i.e. for TXT or CNAME record types)',
            '--delete' => 'Removes specific record (additional --force option needed)',
            '--force'  => 'Force update or delete process',
            '--debug'  => 'Show debug information',
            '--help'   => 'Show this information'
        ];
    }

    /* --- Protected Scope ------------------------------------------------------------------------------------------ */

    /**
     * Create a new record
     *
     * @return Response
     */
    protected function createRecord()
    {
        $recordType = empty($this->record->getRecordType())
            ? 'A'
            : $this->recordsMapping[$this->record->getRecordType()];

        // Generate url for creation procedure
        $url = $this->generateUrl([
            'record_type' => $recordType,
            'pointer'     => $this->record->getPointer(),
            'host_name'   => $this->record->getHost()
        ], 'create');

        $response = $this->client->send($url);

        // Invalidate cache because there are new entries which are not yet added to cache
        Cache::invalidate();

        return $response; // Response
    }

    /**
     * Update an existing record
     *
     * @param $entry
     * @return Response
     * @throws \Exception If no matching host name can be found
     */
    protected function updateRecord($entry)
    {
        if (empty($entry)) {
            throw new \Exception('No matching host name for "' . $this->record->getHost() . '" found');
        }

        $this->onBeforeUpdate($entry);

        $recordType = empty($this->record->getRecordType())
            ? $entry['record_type']
            : $this->recordsMapping[$this->record->getRecordType()];

        // Generate url for update procedure
        $url = $this->generateUrl([
            'record_type' => $recordType,
            'pointer'     => $this->record->getPointer(),
            'host_id'     => $entry['host_id']
        ], 'update');

        $response = $this->client->send($url);

        return $response;
    }

    /**
     * Delete an existing record
     *
     * @param array $entry
     * @return Response
     * @throws \Exception If no matching host name can be found
     */
    protected function deleteRecord($entry)
    {
        if (empty($entry)) {
            throw new \Exception('No matching host name for "' . $this->record->getHost() . '" found');
        }

        $recordType = empty($this->record->getRecordType())
            ? $entry['record_type']
            : $this->recordsMapping[$this->record->getRecordType()];

        // Generate url for delete procedure
        $url = $this->generateUrl([
            'record_type' => $recordType,
            'pointer'     => $this->record->getPointer(),
            'host_id'     => $entry['host_id']
        ], 'delete');

        $response = $this->client->send($url);

        // Invalidate cache because there are now less entries as defined in cache file
        Cache::invalidate();

        return $response;
    }

    /**
     * Generate the request url for host changes
     *
     * @param $data
     * @param string $strategy
     * @return string
     */
    protected function generateUrl($data, $strategy = 'update')
    {
        $forceAction = '';
        $config      = $this->config['HostEurope'];
        $baseUrl     = $this->getBaseUrl();
        $listQuery   = $config['domain_list_query'];
        $query       = $config['host_' . $strategy . '_query'];
        $credentials = $this->getLoginDataAsQuery();

        if ($data['record_type'] === 10 || $data['record_type'] === 11) {
            $data['pointer'] = urlencode($this->record->getAdditional());
        }

        $listQuery = str_replace('[domain]', $this->record->getDomain(), $listQuery);

        foreach ($data AS $key => $value) {
            $query = str_replace('[' . $key . ']', $value, $query);
        }

        // Trigger force flag
        if ($this->record->getForceUpdate()) {
            $forceAction .= $config['force_parameter'];
        }

        return $baseUrl . $listQuery . $query . $credentials . $forceAction;
    }

    /**
     * Validates the inputs against the current values on HE
     *
     * @param array $entry
     * @throws \Exception
     */
    protected function onBeforeUpdate(array $entry)
    {
        // Check if A record has changed
        // Abort update process if ip hasn't changed
        if ($entry['pointer'] === $this->record->getPointer()
            && $entry['record_type'] === 0)
        {
            throw new \Exception('The remote record data is the same as currently given, so there\'s nothing to do');
        }

        // Check if TXT or CNAME record has changed
        if ($entry['pointer'] === $this->record->getAdditional()
            && ($entry['record_type'] === 10 || $entry['record_type'] === 11))
        {
            throw new \Exception('The remote record data is the same as currently given, so there\'s nothing to do');
        }

        // Don't do anything if we doesn't get enough parameters when current record type is CNAME or TXT
        if (empty($this->record->getRecordType())
            && ($entry['record_type'] === 10 || $entry['record_type'] === 11)
        ) {
            $rt = [10 => 'CNAME', 11 => 'TXT'];
            throw new \InvalidArgumentException(
                'Record has currently the type ' . $rt[$entry['record_type']] . ' and you doesn\'t provide enough parameters.'
                . "\n"
                . 'This record type don\'t need continuing updates because it has static values.'
                . "\n"
                . 'If you want to change the type you have to provide additional information like option -t for '
                . 'the type and option -a for the value.'
            );
        }

        Cache::invalidate();
    }

    /**
     * Returns a list of currently registered domain names
     *
     * @return array
     */
    protected function getDomains()
    {
        $domains  = [];
        $url      = $this->getBaseUrl() . $this->getLoginDataAsQuery();
        $response = $this->client->send($url);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->getContent());

        $xpath = new \DOMXPath($dom);
        $tags  = $xpath->query("//input[@name='domain']");

        for ($i = 0; $i < $tags->length; $i++) {

            $value = $tags->item($i)->attributes->getNamedItem('value');

            if (empty($value->textContent) || in_array($value->textContent, $domains)) {
                continue;
            }

            $domains[] = $value->textContent;

        }

        return $domains;
    }

    /**
     * Returns a list of defined hosts for a domain
     *
     * @return array
     */
    protected function getDomainHostsList()
    {
        $result = [];
        $config = $this->config['HostEurope'];

        $url  = $this->getBaseUrl();
        $url .= str_replace('[domain]', $this->record->getDomain(), $config['domain_list_query']);
        $url .= $this->getLoginDataAsQuery();

        $response = $this->client->send($url);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->getContent());

        $xpath  = new \DOMXPath($dom);
        $fields = $xpath->query("//input[@name='pointer']");

        for ($i = 0; $i < $fields->length; $i++) {

            $recordType = null;
            $hostId     = null;

            $row = $fields->item($i)->parentNode->parentNode;

            // Get hostname
            $firstCell = $row->firstChild->textContent;
            $firstCell = explode('ergibt:', $firstCell)[0];

            // Get selected record type
            $secondCell = $row->firstChild->nextSibling;
            $options    = $secondCell->childNodes->item(0)->childNodes->item(1)->childNodes;

            for ($z = 0; $z < $options->length; $z++) {

                $selected = $options->item($z)->attributes->getNamedItem('selected');

                if (empty($selected->textContent)) {
                    continue;
                }

                if ($selected->textContent === 'selected') {
                    $recordType = $options->item($z)->attributes->getNamedItem('value')->textContent;
                }

            }

            // Get host_id
            for ($x = 0; $x < $row->childNodes->length; $x++) {

                $hiddenInput = $row->childNodes->item($x)->attributes->getNamedItem('name');

                if (empty($hiddenInput)) {
                    continue;
                }

                if ($hiddenInput->textContent === 'hostid') {
                    $hostId = $row->childNodes->item($x)->attributes->getNamedItem('value')->textContent;
                }

            }

            $result[$firstCell] = [
                'pointer'     => $fields->item($i)->attributes->getNamedItem('value')->textContent,
                'record_type' => (int)$recordType,
                'host_id'     => $hostId
            ];

        }

        return $result;
    }

    /**
     * Get login data from configuration file
     *
     * @return mixed
     */
    protected function getLoginData()
    {
        return $this->config['Credentials'];
    }

    /**
     * Transform login data to url query compatible
     *
     * @return mixed
     */
    protected function getLoginDataAsQuery()
    {
        $creds = $this->getLoginData();

        $queryString = $this->config['HostEurope']['login_credentials_query'];

        $queryString = str_replace(
            ['[kdnr]', '[password]'],
            [$creds['username'], $creds['password']],
            $queryString
        );

        return $queryString;
    }

    /**
     * Get hosteurope's base url and path (i.e. https://kis.hosteurope.de/administration/domainservices/index.php)
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        $config = $this->config['HostEurope'];
        return $config['host'] . $config['path'] . $config['general_list_query'];
    }

}