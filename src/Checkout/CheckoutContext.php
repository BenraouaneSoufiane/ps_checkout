<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\PrestashopCheckout\Checkout;

use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\AmountEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CountryEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CurrencyEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\Rule\AndRule;
use PrestaShop\Module\PrestashopCheckout\Rule\RulesEngine;

class CheckoutContext
{
    /**
     * - Shop (Name, Url, Logo)
     * - Merchant (Name, Addresses, Contact, Logo)
     * - Customer (Addresses, Groups, CartRules, Vouchers, Orders)
     * - Session (Id, CartId, CustomerId, LanguageId, CurrencyId, CountryId, ShippingAddressId, BillingAddressId)
     * - Cart (Products, Shipping, PaymentMethods, Discounts, Vouchers, Taxes, Total)
     * - Order
     */
    public function getPayPalSDKParameters()
    {
        $parameters = [
            // From environment
            'client-id' => '',
            'merchant-id' => '',
            'data-partner-attribution-id' => '',
            // From configuration
            'integration-date' => '',
            // From Request
            'page-type' => '', // Based on current controller (Tools::getValue('controller'))
            'locale' => '', // Compute from PS Context Country and Language
            'currency' => '', // Depends of PS Context
            // Conditionally computed from Context
            'disable-funding' => [], // Depends of payment source eligible and configuration
            'enable-funding' => [], // Depends of payment source eligible and configuration
            'commit' => true, // Depends if we have all data required to create an order (billing address, shipping address if needed, delivery method if needed, payment method)
            'components' => [], // Depends of payment source eligible (card-fields for card, payment-fields for APMs, applepay for Apple Pay, googlepay for Google Pay...)

            'intent' => '', // For now always CAPTURE but we can imagine that if a product is out of stock we can switch to AUTHORIZE
            'vault' => false, // If customer is logged in and configuration is enabled and merchant eligible
            // If customer is logged in
            'client-token' => '',
            'user-id-token' => '',
        ];

        $sandbox = true; // Depends of configuration

        if ($sandbox) {
            // From configuration
            $parameters['buyer-country'] = 'US'; // Could be set by configuration
            $parameters['debug'] = true; // Could be set by configuration
        }

        return $parameters;
    }

    public function isPaymentSourceEligible(\Cart $cart)
    {
        $rulesEngine = new RulesEngine();
        $andRules = new AndRule([
            new AmountEligibilityRule($cart->getOrderTotal(), '1'),
            new CountryEligibilityRule($cart->getTaxCountry()->iso_code, ['US']),
            new CurrencyEligibilityRule(\Currency::getIsoCodeById($cart->id_currency), ['USD']),
        ]);
        $rulesEngine->addRule($andRules);

        return $rulesEngine->evaluate();
    }
}
