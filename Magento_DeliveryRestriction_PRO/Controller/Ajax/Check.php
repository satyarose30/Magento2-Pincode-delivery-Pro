<?php
declare(strict_types=1);

namespace MageArray\CheckDelivery\Controller\Ajax;

use MageArray\CheckDelivery\Model\OpenSearchLookup;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class Check extends Action
{
    private const INVALID_PINCODE_MESSAGE = 'Please enter a valid 6-digit pincode.';
    private const UNAVAILABLE_MESSAGE = 'Delivery not available for this pincode.';

    private JsonFactory $jsonFactory;
    private OpenSearchLookup $lookup;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        OpenSearchLookup $lookup
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->lookup = $lookup;
    }

    public function execute(): Json
    {
        $result = $this->jsonFactory->create();
        $pincode = trim((string) $this->getRequest()->getParam('pincode', ''));

        if (!preg_match('/^\d{6}$/', $pincode)) {
            return $result->setData(['success' => false, 'message' => self::INVALID_PINCODE_MESSAGE]);
        }

        $data = $this->lookup->checkPincode($pincode);

        if (!is_array($data)) {
            return $result->setData(['success' => false, 'message' => self::UNAVAILABLE_MESSAGE]);
        }

        $deliveryDays = isset($data['delivery_days']) ? (string) $data['delivery_days'] : 'N/A';
        $message = sprintf('Delivery available. Estimated %s days.', $deliveryDays);

        return $result->setData(['success' => true, 'message' => $message]);
    }
}
