<?php

namespace FinanceBundle\Service;

use DateTime;
use FinanceBundle\Entity\Portfolio;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * 
 */
class YahooFinanceService
{

    public function getDataForLast2Years($shares)
    {
        $client = new Client();

        $requests = [];

        $historicalData = [];
        $twoYearsAgo = new DateTime('2 year ago');
        $oneYearAgo = new DateTime('1 year ago');
        $today = new DateTime('NOW');

        $str = "select * from yahoo.finance.historicaldata where startDate='%s' and endDate='%s' and symbol='%s'";

        //Формируем запросы
        $index = 0;
        $index_pool = [];
        foreach ($shares as $share) {
            $uri = $this->createUrl(sprintf($str, $twoYearsAgo->format("Y-m-d"), $oneYearAgo->format("Y-m-d"), $share->getCode()));
            $requests[] = new GuzzleRequest('GET', $uri);
            $index_pool[$index++] = $share->getName();

            $uri = $this->createUrl(sprintf($str, $oneYearAgo->format("Y-m-d"), $today->format("Y-m-d"), $share->getCode()));
            $requests[] = new GuzzleRequest('GET', $uri);
            $index_pool[$index++] = $share->getName();
        }

        $historicalData = [];
        $pool = new Pool($client, $requests, [
            'concurrency' => 5,
            'fulfilled' => function ($response, $index) use ($index_pool, &$historicalData) {
                $data = json_decode((string) $response->getBody(), true)['query']['results']['quote'];
                foreach ($data as $dayData) {
                    $historicalData[$dayData['Date']][$index_pool[$index]] = $dayData['Close'];
                }
            },
            'rejected' => function ($reason, $index) {
                // this is delivered each failed request
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        return $historicalData;
    }

    private function createUrl($query)
    {
        $params = array(
            'env' => "http://datatables.org/alltables.env",
            'format' => "json",
            'q' => $query,
        );
        return "http://query.yahooapis.com/v1/public/yql?" . http_build_query($params);
    }
}
