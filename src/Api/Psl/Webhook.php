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

namespace PrestaShop\Module\PrestashopCheckout\Api\Psl;

use GuzzleHttp\Client;
use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Api\Psl\Client\PslClient;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Handler\ExceptionHandler;
use PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionManager;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Handle Webhook requests
 */
class Webhook extends PslClient
{
    /**
     * @var OnboardingSessionManager
     */
    private $onboardingSessionManager;

    public function __construct(
        ExceptionHandler $exceptionHandler,
        LoggerInterface $logger,
        PrestaShopConfiguration $prestaShopConfiguration,
        PrestaShopContext $prestaShopContext,
        ShopUuidManager $shopUuidManager,
        LinkAdapter $linkAdapter,
        CacheInterface $cache,
        Token $token,
        Client $client = null,
        OnboardingSessionManager $onboardingSessionManager
    ) {
        parent::__construct($exceptionHandler, $logger, $prestaShopConfiguration, $prestaShopContext, $shopUuidManager, $linkAdapter, $cache, $token, $client);
        $this->onboardingSessionManager = $onboardingSessionManager;
    }

    /**
     * Tells if the webhook came from the PSL
     *
     * @param array $payload
     *
     * @return array
     */
    public function getShopSignature(array $payload)
    {
        $openedOnboardingSession = $this->onboardingSessionManager->getOpened();

        $this->setRoute("/webhooks/${payload['id']}/verify");

        return $this->post([
            'headers' => [
                'X-Correlation-Id' => $openedOnboardingSession->getCorrelationId(),
                'Session-Token' => $openedOnboardingSession->getAuthToken(),
            ],
            'json' => $payload,
        ]);
    }
}
