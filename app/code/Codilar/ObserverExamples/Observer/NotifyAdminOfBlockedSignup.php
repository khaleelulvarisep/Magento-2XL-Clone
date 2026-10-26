<?php

declare(strict_types=1);

namespace Codilar\ObserverExamples\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Notification\NotifierInterface;

/**
 * Listens to our own "codilar_signup_blocked" event and adds a message to the admin notification bell.
 */
class NotifyAdminOfBlockedSignup implements ObserverInterface
{
    public function __construct(
        private NotifierInterface $notifier
    ) {
    }

    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();

        $this->notifier->addMajor(
            (string) __('Blocked sign-up from a disposable email'),
            (string) __(
                'Someone tried to register with %1 (domain: %2). The account was not created.',
                $event->getData('email'),
                $event->getData('domain')
            )
        );
    }
}
