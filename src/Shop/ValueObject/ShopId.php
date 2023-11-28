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

namespace PrestaShop\Module\PrestashopCheckout\Shop\ValueObject;

use PrestaShop\Module\PrestashopCheckout\Shop\Exception\ShopException;

class ShopId
{
    /**
     * @var int
     */
    private $shopId;

    /**
     * @param int $shopId
     *
     * @throws ShopException
     */
    public function __construct($shopId)
    {
        $this->assertIntegerIsGreaterThanZero($shopId);

        $this->shopId = $shopId;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     *
     * @throws ShopException
     */
    public function assertIntegerIsGreaterThanZero($shopId)
    {
        if (!is_int($shopId) || 0 >= $shopId) {
            throw new ShopException(sprintf('Cart id %s is invalid. Cart id must be number that is greater than zero.', var_export($shopId, true)), ShopException::INVALID_ID);
        }
    }
}
