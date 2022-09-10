<?php

namespace Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class OrderPayloadBuilderTest extends TestCase
{
    public function testOrderPayloadBuilderIntentException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage(sprintf('Passed intent %s is unsupported', 'FAILURE'));
        $orderPayloadBuilder->checkBaseNode($this->nodeProvider('FAILURE'));
    }

    public function testOrderPayloadBuilderCurrencyCodeException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage(sprintf('Passed currency %s is invalid', 'XXX'));
        $orderPayloadBuilder->checkBaseNode($this->nodeProvider('XXX'));
    }

    public function testOrderPayloadBuilderAmountException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage(sprintf('Passed amount %s is less or equal to zero', -1));
        $orderPayloadBuilder->checkBaseNode($this->nodeProvider('-1'));
    }

    public function testOrderPayloadBuilderMerchantIdException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage(sprintf('Passed merchant id %s is invalid', ''));
        $orderPayloadBuilder->checkBaseNode($this->nodeProvider(''));
    }

    public function testOrderPayloadBuilderShippingNameException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('shipping name is empty');
        $orderPayloadBuilder->checkShippingNode($this->shippingNodeProvider('0'));
    }

    public function testOrderPayloadBuilderShippingAddressException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('shipping address is empty');
        $orderPayloadBuilder->checkShippingNode($this->shippingNodeProvider('1'));
    }

    public function testOrderPayloadBuilderShippingCityException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('shipping city is empty');
        $orderPayloadBuilder->checkShippingNode($this->shippingNodeProvider('2'));
    }

    public function testOrderPayloadBuilderShippingCountryCodeException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $node = $this->shippingNodeProvider('3');
        $code = $node['shipping']['address']['country_code'];
        $this->expectExceptionMessage(sprintf('Unsupported country code, given %s', var_export($code, true)));
        $orderPayloadBuilder->checkShippingNode($this->shippingNodeProvider('3'));
    }

    public function testOrderPayloadBuilderShippingPostalCodeException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('shipping postal code is empty');
        $orderPayloadBuilder->checkShippingNode($this->shippingNodeProvider('4'));
    }

    public function testOrderPayloadBuilderPayerGivenNameException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('payer given name is empty');
        $orderPayloadBuilder->checkPayerNode($this->payerNodeProvider('0'));
    }

    public function testOrderPayloadBuilderPayerSurnameException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('payer surname is empty');
        $orderPayloadBuilder->checkPayerNode($this->payerNodeProvider('1'));
    }

    public function testOrderPayloadBuilderPayerEmailAddressException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('payer email_address is empty');
        $orderPayloadBuilder->checkPayerNode($this->payerNodeProvider('2'));
    }

    public function testOrderPayloadBuilderPayerStreetAddressException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('payer address street is empty');
        $orderPayloadBuilder->checkPayerNode($this->payerNodeProvider('3'));
    }

    public function testOrderPayloadBuilderPayerCityAddressException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('payer address city is empty');
        $orderPayloadBuilder->checkPayerNode($this->payerNodeProvider('4'));
    }

    public function testOrderPayloadBuilderPayerCountryCodeException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $node = $this->payerNodeProvider('5');
        $code = $node['payer']['address']['country_code'];
        $this->expectExceptionMessage(sprintf('Unsupported country code, given %s', var_export($code, true)));
        $orderPayloadBuilder->checkPayerNode($this->payerNodeProvider('5'));
    }

    public function testOrderPayloadBuilderPayerPostalCodeException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('payer address country code is empty');
        $orderPayloadBuilder->checkPayerNode($this->payerNodeProvider('6'));
    }

    public function testOrderPayloadBuilderApplicationContextBrandNameException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('application contex brand name is missed');
        $orderPayloadBuilder->checkApplicationContextNode($this->applicationContextNodeProvider('0'));
    }

    public function testOrderPayloadBuilderApplicationContextShippingPreferenceException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('application contex shipping preference is missed');
        $orderPayloadBuilder->checkApplicationContextNode($this->applicationContextNodeProvider('1'));
    }

    public function testOrderPayloadBuilderAmountBreakDownItemNameException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('item name is empty');
        $orderPayloadBuilder->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('0'));
    }

    public function testOrderPayloadBuilderAmountBreakDownItemNameSkuException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('item sku is empty');
        $orderPayloadBuilder->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('1'));
    }

    public function testOrderPayloadBuilderAmountBreakDownItemUnitAmountCurrencyCodeException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('item unit_amount currency code is not valid');
        $orderPayloadBuilder->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('2'));
    }

    public function testOrderPayloadBuilderAmountBreakDownItemUnitAmountValueException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('item unit_amount value is empty');
        $orderPayloadBuilder->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('3'));
    }

    public function testOrderPayloadBuilderAmountBreakDownItemTaxCurrencyCodeException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('item tax currency code is empty');
        $orderPayloadBuilder->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('4'));
    }

    public function testOrderPayloadBuilderAmountBreakDownItemTaxValueException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('item tax value is empty');
        $orderPayloadBuilder->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('5'));
    }

    public function testOrderPayloadBuilderAmountBreakDownItemQuantityException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('item quantity is empty');
        $orderPayloadBuilder->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('6'));
    }

    public function testOrderPayloadBuilderAmountBreakDownItemCategoryException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('item category is empty');
        $orderPayloadBuilder->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('7'));
    }

    public function cartProvider()
    {
        return ['key' => 'value'];
    }

    public function nodeProvider($value)
    {
        return [
            'intent' => $value == 'FAILURE' ? $value : 'CAPTURE', // capture or authorize
            'custom_id' => $value == '123' ? $value : 'abcd', // id_cart or id_order // link between paypal order and prestashop order
            'invoice_id' => $value == 2 ? $value : '',
            'description' => $value == 3 ? $value : 'Checking out with your cart abcd from  ShopName',
            'amount' => [
                'currency_code' => $value == 'XXX' ? $value : 'EUR',
                'value' => $value == -1 ? $value : 123,
            ],
            'payee' => [
                'merchant_id' => $value == '' ? $value : 'ABCD',
            ],
        ];
    }

    public function shippingNodeProvider($i)
    {
        $node = ['0' => ['shipping' => [
            'name' => [
                'full_name' => '',
            ],
            'address' => [
                'address_line_1' => 'Kalno 5',
                'address_line_2' => 'Taraku 4',
                'admin_area_1' => 'Lithuania',
                'admin_area_2' => 'Kaunas',
                'country_code' => 'LT',
                'postal_code' => '50286',
            ],
        ]],
            '1' => ['shipping' => [
                'name' => [
                    'full_name' => 'Jonas',
                ],
                'address' => [
                    'address_line_1' => '',
                    'address_line_2' => 'Taraku 39',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'LT',
                    'postal_code' => '50280',
                ],
            ]],
            '2' => ['shipping' => [
                'name' => [
                    'full_name' => 'Jonas',
                ],
                'address' => [
                    'address_line_1' => 'Malku 480',
                    'address_line_2' => 'Slieku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => '',
                    'country_code' => 'LT',
                    'postal_code' => '50285',
                ],
            ]],
            '3' => ['shipping' => [
                'name' => [
                    'full_name' => 'Jonas',
                ],
                'address' => [
                    'address_line_1' => 'Malku 48',
                    'address_line_2' => 'Taraku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'XX',
                    'postal_code' => '50285',
                ],
            ]],
            '4' => ['shipping' => [
                'name' => [
                    'full_name' => 'Jonas',
                ],
                'address' => [
                    'address_line_1' => 'Malku 48',
                    'address_line_2' => 'Taraku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'LT',
                    'postal_code' => '',
                ],
            ]],
        ];

        return $node[$i];
    }

    public function payerNodeProvider($i)
    {
        $node = ['0' => ['payer' => [
            'name' => [
                'given_name' => '',
                'surname' => 'Lennon',
            ],
            'email_address' => 'foo@bar.com',
            'address' => [
                'address_line_1' => 'Kalno 5',
                'address_line_2' => 'Taraku 4',
                'admin_area_1' => 'Lithuania',
                'admin_area_2' => 'Kaunas',
                'country_code' => 'LT',
                'postal_code' => '50286',
            ],
        ]],
            '1' => ['payer' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => '',
                ],
                'email_address' => 'foo@bar.com',
                'address' => [
                    'address_line_1' => 'klinciu 2',
                    'address_line_2' => 'Taraku 39',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'LT',
                    'postal_code' => '50280',
                ],
            ]],
            '2' => ['payer' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => 'Lennon',
                ],
                'email_address' => '',
                'address' => [
                    'address_line_1' => 'Malku 480',
                    'address_line_2' => 'Slieku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'LT',
                    'postal_code' => '50285',
                ],
            ]],
            '3' => ['payer' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => 'Lennon',
                ],
                'email_address' => 'foo@bar.com',
                'address' => [
                    'address_line_1' => '',
                    'address_line_2' => 'Taraku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'LT',
                    'postal_code' => '50285',
                ],
            ]],
            '4' => ['payer' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => 'Lennon',
                ],
                'email_address' => 'foo@bar.com',
                'address' => [
                    'address_line_1' => 'Malku 48',
                    'address_line_2' => 'Taraku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => '',
                    'country_code' => 'LT',
                    'postal_code' => '56023',
                ],
            ]],
            '5' => ['payer' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => 'Lennon',
                ],
                'email_address' => 'foo@bar.com',
                'address' => [
                    'address_line_1' => 'Malku 48',
                    'address_line_2' => 'Taraku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'XX',
                    'postal_code' => '56023',
                ],
            ]],
            '6' => ['payer' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => 'Lennon',
                ],
                'email_address' => 'foo@bar.com',
                'address' => [
                    'address_line_1' => 'Malku 48',
                    'address_line_2' => 'Taraku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Vilnius',
                    'country_code' => 'LT',
                    'postal_code' => '',
                ],
            ]],
        ];

        return $node[$i];
    }

    public function applicationContextNodeProvider($i)
    {
        $node =
            ['0' => ['application_context' => [
                'brand_name' => '',
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
            ],
            ],
                '1' => ['application_context' => [
                    'brand_name' => 'MyShop',
                    'shipping_preference' => '',
                ],
                ],
            ];

        return $node[$i];
    }

    public function amountBreakDownNodeProvider($i)
    {
        switch ($i) {
            case '0':
                return [
                    'items' => [
                        '0' => [
                            'name' => '',
                            'description' => 'Apie nieka',
                            'sku' => 'demo_12',
                            'unit_amount' => ['currency_code' => 'EUR', 'value' => '12.9'],
                            'tax' => ['currency_code' => 'EUR', 'value' => '12.9'],
                            'quantity' => '1',
                            'category' => 'gems',
                        ],
                    ],
                ];
            case '1':
                return [
                    'items' => ['1' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => '',
                        'unit_amount' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'tax' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'quantity' => '1',
                        'category' => 'gems',
                    ],
                    ],
                ];
            case '2':
                return [
                    'items' => ['2' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => 'demo_12',
                        'unit_amount' => ['currency_code' => 'XX', 'value' => '12.9'],
                        'tax' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'quantity' => '1',
                        'category' => 'gems',
                    ],
                    ],
                ];
            case '3':
                return [
                    'items' => ['3' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => 'demo_12',
                        'unit_amount' => ['currency_code' => 'EUR', 'value' => ''],
                        'tax' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'quantity' => '1',
                        'category' => 'gems',
                    ],
                    ],
                ];
            case '4':
                return [
                    'items' => ['4' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => 'demo_12',
                        'unit_amount' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'tax' => ['currency_code' => '', 'value' => '2.3'],
                        'quantity' => '1',
                        'category' => 'gems',
                    ],
                    ],
                ];
            case '5':
                return [
                    'items' => ['5' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => 'demo_12',
                        'unit_amount' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'tax' => ['currency_code' => 'EUR', 'value' => ''],
                        'quantity' => '1',
                        'category' => 'gems',
                    ],
                    ],
                ];
            case '6':
                return [
                    'items' => ['6' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => 'demo_12',
                        'unit_amount' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'tax' => ['currency_code' => 'EUR', 'value' => '2.9'],
                        'quantity' => '',
                        'category' => 'gems',
                    ],
                    ],
                ];
            case '7':
                return [
                    'items' => ['7' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => 'demo_12',
                        'unit_amount' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'tax' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'quantity' => '12',
                        'category' => '',
                    ],
                    ],
                ];
        }

        return true;
    }
}
