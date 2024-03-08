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

namespace PrestaShop\Module\PrestashopCheckout\PaymentMethodToken\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\PaymentMethodToken\Command\SavePaymentMethodTokenCommand;
use PrestaShop\Module\PrestashopCheckout\PaymentMethodToken\Event\PaymentMethodTokenCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PaymentMethodToken\Event\PaymentMethodTokenDeletedEvent;
use PrestaShop\Module\PrestashopCheckout\PaymentMethodToken\Event\PaymentMethodTokenDeletionInitiatedEvent;
use Ps_checkout;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentMethodTokenEventSubscriber implements EventSubscriberInterface
{
    /** @var Ps_checkout */
    private $module;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(Ps_checkout $module)
    {
        $this->module = $module;
        $this->commandBus = $this->module->getService('ps_checkout.bus.command');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PaymentMethodTokenCreatedEvent::class => [
                ['saveCreatedPaymentMethodToken'],
            ],
            PaymentMethodTokenDeletedEvent::class => [
                ['deletePaymentMethodToken'],
            ],
            PaymentMethodTokenDeletionInitiatedEvent::class => [
                [''], // No sé
            ],
        ];
    }

    public function saveCreatedPaymentMethodToken(PaymentMethodTokenCreatedEvent $event)
    {
        $this->commandBus->handle(new SavePaymentMethodTokenCommand(
            $paymentMethodTokenId,
            $paypalCustomerId,
            $paymentSource,
            $event->getResource()
        ));
    }

    public function deletePaymentMethodToken(PaymentMethodTokenDeletedEvent $event)
    {
    }
}
