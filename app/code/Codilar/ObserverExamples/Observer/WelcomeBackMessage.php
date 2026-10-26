<?php

declare(strict_types=1);

namespace Codilar\ObserverExamples\Observer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Greets the customer by name right after they log in.
 */
class WelcomeBackMessage implements ObserverInterface
{
    public function __construct(
        private ManagerInterface $messageManager
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var Customer $customer */
        $customer = $observer->getEvent()->getData('customer');

        $this->messageManager->addSuccessMessage(
            __('Welcome back, %1! Good to see you again.', $customer->getFirstname())
        );
    }
}
