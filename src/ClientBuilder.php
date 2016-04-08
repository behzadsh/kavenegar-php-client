<?php
namespace Quince\kavenegar;

use Curl\Curl;

class ClientBuilder
{

    /**
     * Build and return the client
     *
     * @param string      $apiKey
     * @param string|null $sender
     * @return Client
     */
    public static function build($apiKey, $sender = null)
    {
        return new Client(
            new Curl(),
            $apiKey,
            $sender
        );
    }

}