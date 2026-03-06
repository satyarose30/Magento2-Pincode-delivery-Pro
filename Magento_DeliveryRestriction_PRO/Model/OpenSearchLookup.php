<?php
declare(strict_types=1);

namespace MageArray\CheckDelivery\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class OpenSearchLookup
{
    private const OPENSEARCH_URL = 'http://localhost:9200/pincode_index/_search';
    private const REQUEST_TIMEOUT_SECONDS = 5;

    private Curl $curl;
    private LoggerInterface $logger;

    public function __construct(Curl $curl, LoggerInterface $logger)
    {
        $this->curl = $curl;
        $this->logger = $logger;
    }

    /**
     * @return array<string, mixed>|false
     */
    public function checkPincode(string $pincode)
    {
        $query = [
            'size' => 1,
            'query' => [
                'term' => [
                    'pincode' => $pincode,
                ],
            ],
        ];

        try {
            $this->curl->setTimeout(self::REQUEST_TIMEOUT_SECONDS);
            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->post(self::OPENSEARCH_URL, (string) json_encode($query));
        } catch (LocalizedException $exception) {
            $this->logger->warning(
                sprintf('OpenSearch lookup failed for pincode %s: %s', $pincode, $exception->getMessage())
            );
            return false;
        }

        if ($this->curl->getStatus() !== 200) {
            return false;
        }

        $data = json_decode($this->curl->getBody(), true);

        if (!is_array($data)) {
            return false;
        }

        return $data['hits']['hits'][0]['_source'] ?? false;
    }
}
