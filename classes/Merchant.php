<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Api\Maasland;

/**
 * Check and set the merchant status
 */
class Merchant
{
    const SUBSCRIBED = 'SUBSCRIBED';
    const NEED_MORE_DATA = 'NEED_MORE_DATA';
    const IN_REVIEW = 'IN_REVIEW';
    const DENIED = 'DENIED';
    const LIMITED = 'LIMITED';

    /**
     * @var string
     */
    private $merchantId;

    public function __construct($merchantId)
    {
        $this->setMerchantId($merchantId);
    }

    /**
     * Update the merchant status
     */
    public function update()
    {
        $response = $this->getMerchantIntegration();

        if (false === $response) {
            return false;
        }

        $this->setEmail($response['primary_email'], $response['primary_email_confirmed']);
        $this->setPaypalStatus($response['payments_receivable']);
        $this->setCardStatus($this->getCardStatus($response));
    }

    /**
     * Determine the status for hosted fields
     *
     * @param array $response
     *
     * @return string $status status to set in database
     */
    public function getCardStatus($response)
    {
        // PPCP_CUSTOM = product pay by card (hosted fields)
        $cardProductIndex = array_search('PPCP_CUSTOM', array_column($response['products'], 'name'));

        // if product 'PPCP_CUSTOM' doesn't exist disable directly hosted fields
        if (false === $cardProductIndex) {
            return self::DENIED;
        }

        $cardProduct = $response['products'][$cardProductIndex];

        switch ($cardProduct['vetting_status']) {
            case self::SUBSCRIBED:
                $status = $this->cardIsLimited($response);
                break;
            case self::NEED_MORE_DATA:
                $status = self::NEED_MORE_DATA;
                break;
            case self::DENIED:
                $status = self::DENIED;
                break;
            case self::IN_REVIEW:
                $status = self::IN_REVIEW;
                break;
            default:
                $status = self::DENIED;
                break;
        }

        return $status;
    }

    /**
     * Check if the card is limited in the case where the card is in SUBSCRIBED
     *
     * @param array $response
     *
     * @return string $status
     */
    public function cardIsLimited($response)
    {
        $findCapability = array_search('CUSTOM_CARD_PROCESSING', array_column($response['capabilities'], 'name'));
        $capability = $response['capabilities'][$findCapability];

        if (isset($capability['limits'])) {
            return self::LIMITED;
        }

        return self::SUBSCRIBED;
    }

    /**
     * Save in database the email merchant and his status
     *
     * @param string $email email of the merchant
     * @param bool $status if the email has been validated or not
     */
    public function setEmail($email, $status)
    {
        \Configuration::updateValue('PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT', $email);
        \Configuration::updateValue('PS_CHECKOUT_PAYPAL_EMAIL_STATUS', $status ? 1 : 0);
    }

    /**
     * Save the status of payment with paypal
     *
     * @param bool $paymentReceivable
     */
    public function setPaypalStatus($paymentReceivable)
    {
        \Configuration::updateValue('PS_CHECKOUT_PAYPAL_PAYMENT_STATUS', $paymentReceivable ? 1 : 0);
    }

    /**
     * Save the status of payment with card (hosted fields)
     *
     * @param string $status
     */
    public function setCardStatus($status)
    {
        \Configuration::updateValue('PS_CHECKOUT_CARD_PAYMENT_STATUS', $status);
    }

    /**
     * Get the merchant integration
     *
     * @param array|bool response or false
     */
    public function getMerchantIntegration()
    {
        $merchantIntegration = (new Maasland(\Context::getContext()->link))->getMerchantIntegration($this->merchantId);

        if (false === $merchantIntegration
            || !isset($merchantIntegration['merchant_integrations'])
        ) {
            return false;
        }

        return $merchantIntegration['merchant_integrations'];
    }

    /**
     * Setter for merchantId
     *
     * @param string $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }
}
