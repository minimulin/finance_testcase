<?php

namespace FinanceBundle\Service;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

/**
 * Сервис для работы с API Yahoo Finance
 */
class YahooFinanceService
{

    /**
     * Возвращает данные по переданным акциям за 2 года
     * @param  Doctrine\ORM\PersistentCollection $shares Коллекция акций
     * @return array         Массив с данными по стоимости акций
     */
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
            //Поскольку Yahoo Finance не отдаёт данные за большие промежутки, то пришлось делать 2 запроса.
            $requests[] = static::createRequest(sprintf($str, $twoYearsAgo->format("Y-m-d"), $oneYearAgo->format("Y-m-d"), $share->getCode()));
            $index_pool[$index++] = $share->getName();

            $requests[] = static::createRequest(sprintf($str, $oneYearAgo->format("Y-m-d"), $today->format("Y-m-d"), $share->getCode()));
            $index_pool[$index++] = $share->getName();
        }

        $historicalData = [];
        //Все данные запрашиваются параллельно и асинхронно, чтобы побыстрее закончить. И по мере готовности собираются в один массив
        $pool = new Pool($client, $requests, [
            'concurrency' => 5,
            'fulfilled' => function ($response, $index) use ($index_pool, &$historicalData) {
                $data = json_decode((string) $response->getBody(), true)['query']['results']['quote'];
                foreach ($data as $dayData) {
                    $historicalData[$dayData['Date']][$index_pool[$index]] = $dayData['Close'];
                }
            },
            'rejected' => function ($reason, $index) {
                
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        return $historicalData;
    }

    /**
     * Подготовка url для запроса к APPI YF
     * @param  String $query Запрос
     * @return String        URL
     */
    protected static function createUrl($query)
    {
        $params = array(
            'env' => "http://datatables.org/alltables.env",
            'format' => "json",
            'q' => $query,
        );
        return "http://query.yahooapis.com/v1/public/yql?" . http_build_query($params);
    }

    protected static function createRequest($url)
    {
        $uri = static::createUrl($url);
        return new GuzzleRequest('GET', $uri);
    }
}
