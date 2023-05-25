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

namespace PrestaShop\Module\PrestashopCheckout\Order\QueryHandler;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentRefundedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentRefundedQueryResult;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Cache\CacheInterface;
use PrestaShopDatabaseException;
use PrestaShopException;

class GetOrderForPaymentRefundedQueryHandler
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param GetOrderForPaymentRefundedQuery $query
     *
     * @return GetOrderForPaymentRefundedQueryResult
     *
     * @throws PsCheckoutException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function handle(GetOrderForPaymentRefundedQuery $query)
    {
        /** @var GetOrderForPaymentRefundedQueryResult $result */
        $result = $this->cache->get(CacheInterface::CART_ID . $query->getCartId()->getValue());
        if (!empty($result) && $result instanceof GetOrderForPaymentRefundedQueryResult) {
            return $result;
        }

        $orderId = null;

        // Order::getIdByCartId() is available since PrestaShop 1.7.1.0
        if (method_exists(\Order::class, 'getIdByCartId')) {
            // @phpstan-ignore-next-line
            $orderId = (int) \Order::getIdByCartId($query->getCartId()->getValue());
        }

        // Order::getIdByCartId() is available before PrestaShop 1.7.1.0, removed since PrestaShop 8.0.0
        if (method_exists(\Order::class, 'getOrderByCartId')) {
            // @phpstan-ignore-next-line
            $orderId = (int) \Order::getOrderByCartId($query->getCartId()->getValue());
        }

        if (!$orderId) {
            throw new PsCheckoutException('No PrestaShop Order associated to this PayPal Order at this time.', PsCheckoutException::PRESTASHOP_ORDER_NOT_FOUND);
        }

        $order = new \Order($orderId);

        if (!\Validate::isLoadedObject($order)) {
            throw new PsCheckoutException('No PrestaShop Order associated to this PayPal Order at this time.', PsCheckoutException::PRESTASHOP_ORDER_NOT_FOUND);
        }

        $totalRefund = $this->getTotalRefund($order);

        $result = new GetOrderForPaymentRefundedQueryResult(
            (int) $order->id,
            (int) $order->getCurrentState(),
            (bool) $order->hasBeenPaid(),
            $this->hasBeenTotallyRefunded($totalRefund, $order),
            (string) $order->getTotalProductsWithTaxes(),
            (string) $totalRefund,
            (int) $order->id_currency
        );

        $this->cache->set(CacheInterface::CART_ID . $query->getCartId()->getValue(), $result);

        return $result;
    }

    private function hasBeenTotallyRefunded($refundAmount, $order)
    {
        return $refundAmount >= $order->total_paid;
    }

    private function getTotalRefund(\Order $order)
    {
        $orderSlips = $order->getOrderSlipsCollection();
        $refundAmount = 0;
        /** @var \OrderSlipCore $orderSlip */
        foreach ($orderSlips as $orderSlip) {
            $refundAmount += $orderSlip->amount + $orderSlip->shipping_cost_amount;
        }

        return $refundAmount;
    }
}
