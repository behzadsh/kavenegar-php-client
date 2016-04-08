<?php
namespace Quince\kavenegar;

use Curl\Curl;
use Quince\kavenegar\Exceptions\ClientException;

class Client
{

    const API_PATH = "http://api.kavenegar.com/v1/%s/%s/%s.json/";

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $sender;

    /**
     * @var bool
     */
    protected $debug;

    protected $headers = [
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
        'charset: utf-8'
    ];

    /**
     * Client constructor.
     *
     * @param Curl   $curl
     * @param string $apiKey
     * @param string $sender
     * @param bool   $debug
     */
    public function __construct(Curl $curl, $apiKey, $sender = null, $debug = false)
    {
        $this->curl = $curl;
        $this->apiKey = $apiKey;
        $this->sender = $sender;
        $this->debug = $debug;
    }

    public function send($receptor, $message, $date = null, $type = null, $localId = null, $sender = null)
    {
        if (is_array($receptor) && is_array($localId) && count($receptor) != count($localId)) {
            throw new ClientException("Count of receptors and local ids should be equal!");
        }

        $params = [
            'receptor' => implode(',', (array) $receptor),
            'sender'   => $this->getSender($sender),
            'message'  => $message,
            'data'     => $date,
            'type'     => $type,
            'localid'  => $localId
        ];

        return $this->run('send', $params);
    }

    public function bulkSend(array $receptors, $message, $date = null, $type = null, $localId = null, $sender = null)
    {
        $receptorCount = count($receptors);

        $params = [
            'receptor' => json_encode($receptors),
            'sender'   => $this->pad($receptorCount, $this->getSender($sender)),
            'message'  => $this->pad($receptorCount, $message),
            'data'     => $date,
            'type'     => $this->pad($receptorCount, $type),
            'localid'  => $this->pad($receptorCount, $localId)
        ];

        return $this->run('sendarray', $params);
    }

    public function getMessageStatus($messageId)
    {
        $params = [
            'messageid' => implode(',', (array) $messageId)
        ];

        return $this->run('status', $params);
    }

    public function getMessageStatusByLocalId($localId)
    {
        $params = [
            'localid' => implode(',', (array) $localId)
        ];

        return $this->run('statuslocalmessageid', $params);
    }

    public function getMessageDetail($messageId)
    {
        $params = [
            'messageid' => implode(',', (array) $messageId)
        ];

        return $this->run('select', $params);
    }

    public function getOutbox($startDate, $endDate = null, $sender = null)
    {
        $params = [
            'startdate' => $startDate,
            'enddate'   => $endDate,
            'sender'    => $this->getSender($sender)
        ];

        return $this->run('selectoutbox', $params);
    }

    public function getRecentOutbox($pageSize = 10, $sender = null)
    {
        $params = [
            'pagesize' => $pageSize,
            'sender'   => $this->getSender($sender)
        ];

        return $this->run('latestoutbox', $params);
    }

    public function getOutboxCount($startDate, $endDate = null, $sender = null)
    {
        $params = [
            'startdate' => $startDate,
            'enddate'   => $endDate,
            'sender'    => $this->getSender($sender)
        ];

        return $this->run('countoutbox', $params);
    }

    public function cancelMessage($messageId)
    {
        $params = [
            'messageid' => implode(',', (array) $messageId)
        ];

        return $this->run('cancel', $params);
    }

    public function getInbox($onlyRead = false, $line = null)
    {
        $params = [
            'linenumber' => $this->getSender($line),
            'isread'     => (int) $onlyRead
        ];

        return $this->run('receive', $params);
    }

    public function getInboxCount($startDate, $endDate = null, $line = null, $onlyRead = false)
    {
        $params = [
            'startdate'  => $startDate,
            'enddate'    => $endDate,
            'linenumber' => $this->getSender($line),
            'isread'     => (int) $onlyRead
        ];

        return $this->run('countinbox', $params);
    }

    public function phoneCountByPostalCode($postalCode)
    {
        return $this->run('countpostalcode', [
            'postalcode' => $postalCode
        ]);
    }

    public function sendByPostalCode(
        $postalcode,
        $message,
        $mciStartIndex,
        $mciCount,
        $mtnStartIndex,
        $mtnCount,
        $date = null,
        $sender = null
    ) {
        $params = [
            'postalcode'    => $postalcode,
            'sender'        => $this->getSender($sender),
            'message'       => $message,
            'mcistartindex' => $mciStartIndex,
            'mcicount'      => $mciCount,
            'mtnstartindex' => $mtnStartIndex,
            'mtncount'      => $mtnCount,
            'date'          => $date
        ];

        return $this->run('sendbypostalcode', $params);
    }

    public function getAccountInfo()
    {
        return $this->run('info', [], 'account');
    }

    public function getAccountConfigs($params = [])
    {
        return $this->run('config', $params, 'account');
    }

    public function sendVerificationCode($receptor, $token, $template)
    {
        $params = [
            'receptor' => $receptor,
            'token'    => $token,
            'template' => $template
        ];

        return $this->run('lookup', $params, 'verify');
    }

    /**
     * @return string
     * @throws ClientException
     */
    public function getApiKey()
    {
        if ($this->apiKey !== null) {
            return $this->apiKey;
        }

        throw new ClientException("Api key is not set!");
    }

    protected function getPath($method, $base)
    {
        return sprintf(self::API_PATH, $this->getApiKey(), $base, $method);
    }

    protected function getSender($overWritingSender)
    {
        if ($overWritingSender !== null) {
            return $overWritingSender;
        }

        return $this->sender;
    }

    protected function run($method, $params = [], $base = 'sms')
    {
        $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->headers);
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);

        $this->curl->post($this->getPath($method, $base), $this->pruneParams($params));

        if ($this->curl->error) {
            throw new ClientException("Request done with error #{$this->curl->curl_error_code}");
        }

        $jsonResponse = json_decode($this->curl->response);

        if ($this->curl->http_status_code != 200 && $jsonResponse === null) {
            throw new ClientException("[{$this->curl->http_status_code}] Api call has failed.");
        } else {
            if ($jsonResponse->return->status != 200) {
                throw new ClientException("[{$jsonResponse->return->status}] {$jsonResponse->return->message}");
            }

            return $jsonResponse->entries;
        }
    }

    protected function pad($size, $value)
    {
        if (is_array($value)) {
            if (count($value) != $size) {
                throw new ClientException("Parament should have $size item, when it passes as array!");
            }

            return $value;
        }

        return json_encode(array_fill(0, $size, $value));
    }

    protected function pruneParams($params)
    {
        return array_filter($params, function ($var) {
            return $var !== null;
        });
    }

}