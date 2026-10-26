<?php

declare(strict_types=1);

namespace Codilar\PluginExamples\Plugin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * AROUND plugin: wraps AccountManagement::createAccount().
 *  - before $proceed: refuse disposable email domains, in which case the original method is never called
 *  - after $proceed:  log the account that was actually created (we need its new id, so this must come after)
 * Doing both sides in one place, and deciding whether to call the original at all, is what around plugins are for.
 */
class BlockDisposableEmailSignup
{
    /**
     * @param string[] $blockedDomains injected from etc/frontend/di.xml
     */
    public function __construct(
        private LoggerInterface $logger,
        private EventManager $eventManager,
        private array $blockedDomains = []
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function aroundCreateAccount(
        AccountManagementInterface $subject,
        callable $proceed,
        CustomerInterface $customer,
        $password = null,
        $redirectUrl = ''
    ): CustomerInterface {
        $email = (string) $customer->getEmail();
        $domain = strtolower(substr(strrchr($email, '@') ?: '', 1));

        if (in_array($domain, $this->blockedDomains, true)) {
            $this->logger->info(sprintf('Blocked sign-up with disposable email %s', $email));
            // Custom event: any module can react (Codilar_ObserverExamples notifies the admin) without us knowing
            $this->eventManager->dispatch('codilar_signup_blocked', ['email' => $email, 'domain' => $domain]);
            throw new LocalizedException(
                __('Temporary email addresses are not allowed. Please sign up with a permanent email address.')
            );
        }

        $start = microtime(true);
        $createdCustomer = $proceed($customer, $password, $redirectUrl);

        $this->logger->info(sprintf(
            'New customer #%d (%s) registered in %.0f ms',
            $createdCustomer->getId(),
            $email,
            (microtime(true) - $start) * 1000
        ));

        return $createdCustomer;
    }
}
