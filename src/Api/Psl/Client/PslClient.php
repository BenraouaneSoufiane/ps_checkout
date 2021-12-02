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

namespace PrestaShop\Module\PrestashopCheckout\Api\Psl\Client;

use GuzzleHttp\Client;
use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Api\GenericClient;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Environment\PslEnv;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutSessionException;
use PrestaShop\Module\PrestashopCheckout\Handler\ExceptionHandler;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;
use Ps_checkout;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Construct the client used to make call to PSL API
 */
class PslClient extends GenericClient
{
    /**
     * @var string
     */
    protected $shopUuid;
    /**
     * @var CacheInterface;
     */
    protected $cache;
    /**
     * @var ShopUuidManager;
     */
    protected $shopUuidManager;
    /**
     * @var Token
     */
    private $token;

    public function __construct(
        ExceptionHandler $exceptionHandler,
        LoggerInterface $logger,
        PrestaShopConfiguration $prestaShopConfiguration,
        PrestaShopContext $prestaShopContext,
        ShopUuidManager $shopUuidManager,
        LinkAdapter $linkAdapter,
        CacheInterface $cache,
        Token $token,
        Client $client = null
    ) {
        parent::__construct($exceptionHandler, $logger, $prestaShopConfiguration, $prestaShopContext, $shopUuidManager, $linkAdapter);

        $this->cache = $cache;
        $this->shopUuidManager = $shopUuidManager;
        $this->token = $token;

        $shopId = $this->prestaShopContext->getShopId();
        $this->shopUuid = $this->shopUuidManager->getForShop($shopId);

        // Client can be provided for tests
        if (null === $client) {
            $client = new Client([
                'base_url' => (new PslEnv())->getPslApiUrl(),
                'defaults' => [
                    'verify' => $this->getVerify(),
                    'timeout' => $this->timeout,
                    'exceptions' => $this->catchExceptions,
                    'headers' => [
                        'Content-Type' => 'application/json', // api version to use (psl side)
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->token->getToken(),
                        'Shop-Id' => $this->shopUuid,
                        'Hook-Url' => $this->linkAdapter->getModuleLink(
                            'ps_checkout',
                            'DispatchWebHook',
                            [],
                            true,
                            null,
                            $shopId
                        ),
                        'Module-Version' => Ps_checkout::VERSION, // version of the module
                        'Prestashop-Version' => _PS_VERSION_, // prestashop version
                        'Shop-Url' => $this->prestaShopContext->getShopUrl(),
                    ],
                ],
            ]);
        }

        $this->setClient($client);
    }

    /**
     * Check PSl response
     *
     * @param string $callType
     * @param array $response
     *
     * @return bool
     */
    public function checkResponse($callType, $response)
    {
        if (!$response || !$response['status']) {
            $exceptionMessage = null;
            $exceptionCode = null;

            if (!$response) {
                $exceptionMessage = 'Unable to contatct PSL';
                $exceptionCode = PsCheckoutSessionException::UNABLE_TO_CONTACT_PSL;
            } elseif (!$response['status']) {
                switch ($callType) {
                    case 'createShopUuid':
                        $exceptionMessage = 'Unable to retrieve shop UUID from PSL';
                        $exceptionCode = PsCheckoutSessionException::UNABLE_TO_RETRIEVE_SHOP_UUID;
                        break;
                    case 'getAuthToken':
                        $exceptionMessage = 'Unable to retrieve authentication token from PSL';
                        $exceptionCode = PsCheckoutSessionException::UNABLE_TO_RETRIEVE_TOKEN;
                        break;
                    case 'createShop':
                    case 'updateShop':
                        $exceptionMessage = 'Unable to retrieve shop from PSL';
                        $exceptionCode = PsCheckoutSessionException::UNABLE_TO_RETRIEVE_SHOP;
                        break;
                    case 'forceUpdateMerchantIntegrations':
                        $exceptionMessage = 'Unable to force update merchant integrations from PSL';
                        $exceptionCode = PsCheckoutSessionException::UNABLE_TO_FORCE_UPDATE_MERCHANT_INTEGRATIONS;
                        break;
                    default:
                        $exceptionMessage = 'Unable to retrieve authentication token from PSL';
                        $exceptionCode = PsCheckoutSessionException::UNABLE_TO_RETRIEVE_SHOP_UUID;
                }
            }

            $this->logger->error(
                $exceptionMessage,
                [
                    'response' => $response,
                ]
            );

            $error = [
                'exceptionCode' => $exceptionCode,
                'exceptionMessage' => $exceptionMessage,
            ];

            $this->cache->set('session-error', $error);

            return false;
        }

        return true;
    }
}
