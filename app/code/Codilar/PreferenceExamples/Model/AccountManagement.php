<?php

declare(strict_types=1);

namespace Codilar\PreferenceExamples\Model;

use Magento\Customer\Model\AccountManagement as CoreAccountManagement;
use Magento\Framework\Exception\InputException;

/**
 * PREFERENCE: replaces Customer\Model\AccountManagement to add a rule to the protected checkPasswordStrength().
 *
 * Why not a plugin? Plugins only intercept public methods. checkPasswordStrength() is protected and is the single
 * place core validates a password for createAccount(), changePassword() and resetPassword(), so overriding it
 * covers all three flows at once. (A private method could not be overridden at all; you would have to override
 * its public/protected callers instead.)
 *
 * No constructor here on purpose: the parent has ~30 dependencies, and redeclaring them would break on every
 * upgrade that changes that list. We inherit it, so the weak password list is a constant rather than a DI argument.
 */
class AccountManagement extends CoreAccountManagement
{
    private const WEAK_PASSWORDS = [
        'password@123',
        'password123',
        'welcome@123',
        'admin@123',
        'qwerty@123',
        'abc@12345',
        'test@1234',
        '12345678',
        'iloveyou',
    ];

    /**
     * Keep every core rule (length, spaces, character classes), then add ours.
     *
     * @param string $password
     * @return void
     * @throws InputException
     */
    protected function checkPasswordStrength($password)
    {
        
        parent::checkPasswordStrength($password);

        if (in_array(strtolower((string) $password), self::WEAK_PASSWORDS, true)) {
            throw new InputException(
                __('This password is too common and easy to guess. Please choose a different password.')
            );
        }
    }
}
