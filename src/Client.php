<?php
namespace Quince\kavenegar;

use Curl\Curl;
use Quince\kavenegar\Exceptions\ClientException;

class Client
{

    /**
     * @const string
     */
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
     */
    public function __construct(Curl $curl, $apiKey, $sender = null)
    {
        $this->curl = $curl;
        $this->apiKey = $apiKey;
        $this->sender = $sender;
    }

    /**
     * Send a message to specified receptor phone number
     *
     * @param string|string[]      $receptor
     * @param string               $message
     * @param int|null             $date
     * @param int|null             $type
     * @param string|string[]|null $localId
     * @param string               $sender
     * @return \StdClass
     */
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

    /**
     * Send bulk messages to multiple receptor phone numbers
     *
     * @param array                $receptors
     * @param string|string[]      $message
     * @param int|null             $date
     * @param int|int[]|null       $type
     * @param string|string[]|null $localId
     * @param string|string[]|null $sender
     * @return \StdClass
     */
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

    /**
     * Get status of a sent message by its message id
     *
     * @param string $messageId
     * @return \StdClass
     */
    public function getMessageStatus($messageId)
    {
        $params = [
            'messageid' => implode(',', (array) $messageId)
        ];

        return $this->run('status', $params);
    }

    /**
     * Get status of a sent message by local id set when sending
     *
     * @param string $localId
     * @return \StdClass
     */
    public function getMessageStatusByLocalId($localId)
    {
        $params = [
            'localid' => implode(',', (array) $localId)
        ];

        return $this->run('statuslocalmessageid', $params);
    }

    /**
     * Get details of a message by message id
     *
     * @param string $messageId
     * @return \StdClass
     */
    public function getMessageDetail($messageId)
    {
        $params = [
            'messageid' => implode(',', (array) $messageId)
        ];

        return $this->run('select', $params);
    }

    /**
     * Get all sent message in specified date range (max 3000 message)
     *
     * @param int         $startDate
     * @param int|null    $endDate
     * @param string|null $sender
     * @return \StdClass
     */
    public function getOutbox($startDate, $endDate = null, $sender = null)
    {
        $params = [
            'startdate' => $startDate,
            'enddate'   => $endDate,
            'sender'    => $this->getSender($sender)
        ];

        return $this->run('selectoutbox', $params);
    }

    /**
     * Get list of recent sent messages (max 3000 messages)
     *
     * @param int         $pageSize
     * @param string|null $sender
     * @return \StdClass
     */
    public function getRecentOutbox($pageSize = 10, $sender = null)
    {
        $params = [
            'pagesize' => $pageSize,
            'sender'   => $this->getSender($sender)
        ];

        return $this->run('latestoutbox', $params);
    }

    /**
     * Get count of sent message in specified range
     *
     * @param int         $startDate
     * @param int|null    $endDate
     * @param string|null $sender
     * @return \StdClass
     */
    public function getOutboxCount($startDate, $endDate = null, $sender = null)
    {
        $params = [
            'startdate' => $startDate,
            'enddate'   => $endDate,
            'sender'    => $this->getSender($sender)
        ];

        return $this->run('countoutbox', $params);
    }

    /**
     * Canceling a pending message from sending
     *
     * @param string $messageId
     * @return \StdClass
     */
    public function cancelMessage($messageId)
    {
        $params = [
            'messageid' => implode(',', (array) $messageId)
        ];

        return $this->run('cancel', $params);
    }

    /**
     * Get list of received messages (100 messages per each request)
     *
     * @param bool        $onlyRead
     * @param string|null $line
     * @return \StdClass
     */
    public function getInbox($onlyRead = false, $line = null)
    {
        $params = [
            'linenumber' => $this->getSender($line),
            'isread'     => (int) $onlyRead
        ];

        return $this->run('receive', $params);
    }

    /**
     * Get count of messages in inbox
     *
     * @param int         $startDate
     * @param int|null    $endDate
     * @param string|null $line
     * @param bool        $onlyRead
     * @return \StdClass
     */
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

    /**
     * Get count of phone numbers in a postal code area, categorized by oprator
     *
     * @param string $postalCode
     * @return \StdClass
     */
    public function phoneCountByPostalCode($postalCode)
    {
        return $this->run('countpostalcode', [
            'postalcode' => $postalCode
        ]);
    }

    /**
     * Send message to phone numbers in a postal code area
     *
     * @param string      $postalcode
     * @param string      $message
     * @param int         $mciStartIndex
     * @param int         $mciCount
     * @param int         $mtnStartIndex
     * @param int         $mtnCount
     * @param int|null    $date
     * @param string|null $sender
     * @return \StdClass
     */
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

    /**
     * Get information of an account
     *
     * @return \StdClass
     */
    public function getAccountInfo()
    {
        return $this->run('info', [], 'account');
    }

    /**
     * Get or set configuration of an account
     *
     * $params['apilogs']          = (string) Status of logging API requests; values: `justfaults` (default), `enabled`, 'disabled`
     *        ['dailyreport']      = (string) Status of daily report; values: `enabled`, 'disabled`
     *        ['debugmode']        = (string) Status of debiging/testing mode, when enabled message sending will be mocked; values: `enabled`, 'disabled`
     *        ['defaultsender']    = (string) The default sender number
     *        ['mincreditalarm']   = (int)    The limit to alert when the credit is about to finish
     *        ['resendfailed']     = (string) Whether try to redend when sending failed or not; values: `enabled`, 'disabled`
     *
     * @param array $params
     * @return \StdClass
     */
    public function getAccountConfigs($params = [])
    {
        return $this->run('config', $params, 'account');
    }

    /**
     * Send vrification code, password, authorization code, etc...
     *
     * @param string $receptor
     * @param string $token
     * @param string $template
     * @return \StdClass
     */
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

    /**
     * @param string $method
     * @param array  $params
     * @param string $base
     * @return \StdClass
     */
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