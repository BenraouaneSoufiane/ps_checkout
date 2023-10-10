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
use PrestaShop\Module\PrestashopCheckout\Api\Exception\UnprocessableEntityException;
use PrestaShop\Module\PrestashopCheckout\Api\PaymentService;
use Psr\Http\Client\ClientInterface;

class CreateOrderPayloadBuilderTest extends TestCase
{
    public function testAuthenticationFailureException() {

    }

    public function testInternalServerErrorException() {

    }

    public function testInvalidRequestException() {

    }

    public function testNotAuthorizedException() {

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
