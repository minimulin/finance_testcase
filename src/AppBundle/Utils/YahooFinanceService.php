<?php

namespace AppBundle\Utils;

/**
 * Сервис для работы с API Yahoo Finance
 */
class YahooFinanceService
{
    private $parser;
    private $dataReciever;
    private $format;

    public function __construct(IYahooResponseParser $parser, IDataReceiver $dataReciever, $format)
    {
        $this->parser = $parser;
        $this->dataReciever = $dataReciever;
        $this->format = $format;
    }

    /**
     * Возвращает данные по переданным акциям за 2 года
     * @param  array $symbols Массив с кодами акций
     * @return array         Массив с данными по стоимости акций
     */
    public function getDataWithSummary(array $symbols, $period = '2y')
    {
        $urls = [];
        foreach ($symbols as $key => $symbol) {
            $urls[] = sprintf('http://chartapi.finance.yahoo.com/instrument/1.0/%s/chartdata;type=quote;range=%s/%s', $symbol, $period, $this->format);
        }
        $historicalData = [];
        $this->dataReciever->load($urls, function ($response, $index) use (&$historicalData, $symbols) {
            $responseArray = $this->parser->parse((string) $response->getBody());

            foreach ($responseArray['series'] as $dayData) {
                if (isset($dayData['Date']) && isset($dayData['close'])) {
                    $historicalData[$dayData['Date']][$symbols[$index]] = $dayData['close'];
                }
            }
        });

        return static::calculateSummaryOfHistoricalData($historicalData, $symbols);
    }

    protected static function calculateSummaryOfHistoricalData(array $historicalData, array $symbols)
    {
        $summary_key = 'summary';

        foreach ($historicalData as $key => $dayData) {
            //Если за день нет данных по какому-либо коду
            $historicalData[$key] = array_merge(array_fill_keys($symbols, 0), $historicalData[$key]);
            $historicalData[$key][$summary_key] = 0;

            foreach ($symbols as $symbol) {
                if (isset($dayData[$symbol])) {
                    $historicalData[$key][$summary_key] += $historicalData[$key][$symbol];
                }
            }
        }

        ksort($historicalData);

        return $historicalData;
    }
}
