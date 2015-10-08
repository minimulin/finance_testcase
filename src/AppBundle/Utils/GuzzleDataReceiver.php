<?php

namespace AppBundle\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

class GuzzleDataReceiver implements IDataReceiver
{

    public function load(array $urlPool, \Closure $successReponce)
    {
        $client = new Client();

        foreach ($urlPool as $url) {
            $requests[] = new GuzzleRequest('GET', $url);
        }

        $pool = new Pool($client, $requests, [
            'concurrency' => 5,
            'fulfilled' => $successReponce,
            'rejected' => function ($reason, $index) {},
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }
}
