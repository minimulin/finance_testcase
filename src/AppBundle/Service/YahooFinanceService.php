<?php

namespace AppBundle\Service;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

/**
 * Сервис для работы с API Yahoo Finance
 */
class YahooFinanceService
{
    const SHARES_QUERY_STRING = "select * from yahoo.finance.historicaldata where startDate='%s' and endDate='%s' and symbol='%s'";

    /**
     * Возвращает данные по переданным акциям за 2 года
     * @param  array $symbols Массив с кодами акций
     * @return array         Массив с данными по стоимости акций
     */
    public function getDataForLast2Years(array $symbols)
    {
        $client = new Client();

        list($index_pool, $requests) = static::generateRequestsForShares($symbols);

        $historicalData = [];
        //Все данные запрашиваются параллельно и асинхронно, чтобы побыстрее закончить. И по мере готовности собираются в один массив
        $pool = new Pool($client, $requests, [
            'concurrency' => 5,
            'fulfilled' => function ($response, $index) use ($index_pool, &$historicalData) {
                $data = json_decode((string) $response->getBody(), true)['query']['results']['quote'];
                foreach ($data as $dayData) {
                    if (isset($dayData['Date']) && isset($dayData['Close'])) {
                        $historicalData[$dayData['Date']][$index_pool[$index]] = $dayData['Close'];
                    }
                }
            },
            'rejected' => function ($reason, $index) {},
        ]);

        $promise = $pool->promise();
        $promise->wait();
        ksort($historicalData);

        return $historicalData;
    }

    protected static function generateRequestsForShares(array $symbols)
    {
        $requests = [];
        $index_pool = [];

        foreach ($symbols as $symbol) {
            //Поскольку Yahoo Finance не отдаёт данные за большие промежутки, то пришлось делать 2 запроса.
            $requests[] = static::createRequest(sprintf(
                static::SHARES_QUERY_STRING,
                (new DateTime('2 year ago'))->format("Y-m-d"),
                (new DateTime('1 year ago'))->format("Y-m-d"),
                $symbol));
            $index_pool[] = $symbol;

            $requests[] = static::createRequest(sprintf(
                static::SHARES_QUERY_STRING,
                (new DateTime('1 year ago'))->format("Y-m-d"),
                (new DateTime('NOW'))->format("Y-m-d"),
                $symbol));
            $index_pool[] = $symbol;
        }

        return [$index_pool, $requests];
    }

    /**
     * Подготовка url для запроса к API YF
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

    public static function calculateSummaryShareCostPerDay(array $historicalData, array $share_codes)
    {
        $summary_key = 'summary';

        foreach ($historicalData as $key => $dayData) {
            //Если за день нет данных по какому-либо коду
            $historicalData[$key] = array_merge(array_fill_keys($share_codes, 0), $historicalData[$key]);
            $historicalData[$key][$summary_key] = 0;

            foreach ($share_codes as $name) {
                if (!isset($dayData[$name])) {
                    $historicalData[$key][$name] = 0;
                } else {
                    $historicalData[$key][$summary_key] += $historicalData[$key][$name];
                }
            }
        }

        return $historicalData;
    }
}
