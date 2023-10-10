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

namespace Tests\Unit\Api;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Http\Client\Exception\HttpException;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Api\Exception\AuthenticationFailureException;
use PrestaShop\Module\PrestashopCheckout\Api\Exception\InternalServerErrorException;
use PrestaShop\Module\PrestashopCheckout\Api\Exception\InvalidRequestException;
use PrestaShop\Module\PrestashopCheckout\Api\Exception\NotAuthorizedException;
use PrestaShop\Module\PrestashopCheckout\Api\Exception\UnprocessableEntityException;
use PrestaShop\Module\PrestashopCheckout\Api\PaymentService;
use Psr\Http\Client\ClientInterface;

class CreateOrderPayloadBuilderTest extends TestCase
{
    public function testAuthenticationFailureException() {
        $request = new Request('POST', 'https://api.prestashop.com');
        $response = new Response(
            401,
            [
                "Content-Type" => "application/json",
            ],
            '{
                "name": "AUTHENTICATION_FAILURE",
                "message": "Authentication failed due to invalid authentication credentials or a missing Authorization header.",
                "links": [
                    {
                        "href": "https://developer.paypal.com/docs/api/overview/#error",
                        "rel": "information_link"
                    }
                ]
            }'
        );
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willThrowException(new HttpException(
            'AUTHENTICATION_FAILURE',
            $request,
            $response
        ));

        $paymentService = new PaymentService($httpClient);
        try {
            $paymentService->createOrder([]);
        } catch (AuthenticationFailureException $e) {
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Wrong exception type : ' . get_class($e));
        }
    }

    public function testInternalServerErrorException() {
        $request = new Request('POST', 'https://api.prestashop.com');
        $response = new Response(
            500,
            [
                "Content-Type" => "application/json",
            ],
            '{
                "name": "INTERNAL_SERVER_ERROR",
                "message": "An internal server error has occurred.",
                "debug_id": "90957fca61718",
                "links": [
                    {
                        "href": "https://developer.paypal.com/docs/api/orders/v2/#error-INTERNAL_SERVER_ERROR",
                        "rel": "information_link",
                        "method": "GET"
                    }
                ]
            }'
        );
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willThrowException(new HttpException(
            'INTERNAL_SERVER_ERROR',
            $request,
            $response
        ));

        $paymentService = new PaymentService($httpClient);
        try {
            $paymentService->createOrder([]);
        } catch (InternalServerErrorException $e) {
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Wrong exception type : ' . get_class($e));
        }
    }

    public function testInvalidRequestException() {
        $request = new Request('POST', 'https://api.prestashop.com');
        $response = new Response(
            400,
            [
                "Content-Type" => "application/json",
            ],
            '{
                "name": "INVALID_REQUEST",
                "details": [
                    {
                        "field": "/purchase_units",
                        "value": "[]",
                        "location": "body",
                        "issue": "INVALID_ARRAY_MIN_ITEMS",
                        "description": "The number of items in an array parameter is too small."
                    }
                ],
                "message": "Request is not well-formed, syntactically incorrect, or violates schema.",
                "debug_id": "10398537340c8",
                "links": [
                    {
                        "href": "https://developer.paypal.com/docs/api/orders/v2/#error-INVALID_ARRAY_MIN_ITEMS",
                        "rel": "information_link"
                    }
                ]
            }'
        );
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willThrowException(new HttpException(
            'INVALID_REQUEST',
            $request,
            $response
        ));

        $paymentService = new PaymentService($httpClient);
        try {
            $paymentService->createOrder([]);
        } catch (InvalidRequestException $e) {
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Wrong exception type : ' . get_class($e));
        }
    }

    public function testNotAuthorizedException() {
        $request = new Request('POST', 'https://api.prestashop.com');
        $response = new Response(
            403,
            [
                "Content-Type" => "application/json",
            ],
            '{
                "name": "NOT_AUTHORIZED",
                "message": "Token is invalid",
                "debug_id": "970e6a10938c5",
                "informationLink": "https://developer.paypal.com/docs/api/orders#errors"
            }'
        );
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willThrowException(new HttpException(
            'NOT_AUTHORIZED',
            $request,
            $response
        ));

        $paymentService = new PaymentService($httpClient);
        try {
            $paymentService->createOrder([]);
        } catch (NotAuthorizedException $e) {
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Wrong exception type : ' . get_class($e));
        }
    }

    public function testUnprocessableEntityException() {
        $request = new Request('POST', 'https://api.prestashop.com');
        $response = new Response(
            422,
            [
                "Content-Type" => "application/json",
            ],
            '{
                "name": "UNPROCESSABLE_ENTITY",
                "details": [
                    {
                        "issue": "PAYMENT_NOT_APPROVED",
                        "description": "The customer has not approved payment."
                    }
                ],
                "message": "The requested action could not be completed, was semantically incorrect, or failed business validation.",
                "debug_id": "90957fca61718",
                "links": [
                    {
                        "href": "https://developer.paypal.com/docs/api/orders/v2/#error-PAYMENT_NOT_APPROVED",
                        "rel": "information_link",
                        "method": "GET"
                    }
                ]
            }'
        );
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willThrowException(new HttpException(
            'UNPROCESSABLE_ENTITY',
            $request,
            $response
        ));

        $paymentService = new PaymentService($httpClient);
        try {
            $paymentService->createOrder([]);
        } catch (UnprocessableEntityException $e) {
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Wrong exception type : ' . get_class($e));
        }
    }
}
