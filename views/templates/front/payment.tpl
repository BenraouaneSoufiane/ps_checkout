{**
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
 *}
{extends file='page.tpl'}
{block name='content'}

    <div class="ps-checkout wrapper">
      <div class="ps-checkout content">
        {if isset($success3DS)}
          <input type="hidden" name="3DS">
          <div class="alert alert-success">
            {l s='3DS verification successful!' mod='ps_checkout'}
          </div>
        {else}
          <div class="alert alert-danger">
            {if isset($error)}
              {$error}
            {else}
              {l s='3DS verification failed, please try again.' mod='ps_checkout'}
            {/if}
          </div>
          <div class="ps-checkout order-link">
            <a href="#">{l s='Back to order page' mod='ps_checkout'}</a>
          </div>
        {/if}
      </div>
    </div>
  {literal}
  <script>
    window.onload = () => {
      if (document.querySelector('input[name="3DS"]')) {
        window.parent.document.dispatchEvent(new Event('3DS-success'));
      }

      if (document.querySelector('.ps-checkout.order-link>a')) {
        const orderLink = document.querySelector('.ps-checkout.order-link>a');
        orderLink.addEventListener('click', (e) => {
          e.preventDefault();
          window.parent.document.dispatchEvent(new Event('3DS-close'));
        })
      }
    };
  </script>
  {/literal}
{/block}
{block name='notifications'}
{/block}
{block name='header'}
{/block}
{block name="footer"}
{/block}
