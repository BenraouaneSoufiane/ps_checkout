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

namespace PrestaShop\Module\PrestashopCheckout\Api;

use Http\Client\Exception\RequestException;
use PrestaShop\Module\PrestashopCheckout\Api\Exception\AuthenticationFailureException;
use PrestaShop\Module\PrestashopCheckout\Api\Exception\InternalServerErrorException;
use PrestaShop\Module\PrestashopCheckout\Api\Exception\InvalidRequestException;
use PrestaShop\Module\PrestashopCheckout\Api\Exception\NotAuthorizedException;
use PrestaShop\Module\PrestashopCheckout\Api\Exception\UnprocessableEntityException;
use Psr\Http\Client\ClientInterface;

class PaymentService
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @param ClientInterface $httpClient
     */
    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function createOrder(array $payload)
    {
        try {
            // For unit testing purpose :

            // throw new AuthenticationFailureException();
            // throw new InternalServerErrorException();
            // throw new InvalidRequestException();
            // throw new NotAuthorizedException();
            // throw new UnprocessableEntityException();

            $response = $this->httpClient->sendRequest();
        } catch (RequestException $exception) {
            // In case of BadRequestException, we can retrieve the response and the request
            // Depending on the response, we can throw a dedicated Exception (e.g. InvalidPayloadException etc...)
            $exception->getResponse();
        }
    }
}
