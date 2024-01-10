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

{**
 * WARNING
 *
 * This file allow only html
 *
 * It will be parsed by PrestaShop Core with PrestaShop\PrestaShop\Core\Payment\PaymentOptionFormDecorator
 *
 * Script tags will be removed and some HTML5 element can cause an Exception due to DOMDocument class
 *}
<form id="ps_checkout-hosted-fields-form" class="form-horizontal loading">
  <div id="ps_checkout-hosted-fields-form-loader">
    <img src="{$modulePath}views/img/tail-spin.svg" alt="">
  </div>
  <div class="form-group">
    <label class="form-control-label" for="ps_checkout-hosted-fields-card-name">{l s='Cardholder Name (optional)' mod='ps_checkout'}</label>
    <div id="ps_checkout-hosted-fields-card-name">
    </div>
  </div>
  <div class="form-group">
    <label class="form-control-label" for="ps_checkout-hosted-fields-card-number">{l s='Card number' mod='ps_checkout'}</label>
    <div id="ps_checkout-hosted-fields-card-number" >
      <div id="card-image">
        <img class="default-credit-card" src="{$modulePath}views/img/credit_card.png" alt="">
      </div>
    </div>
  </div>
  <div class="row">
    <div class="form-group col-xs-6 col-6">
      <label class="form-control-label" for="ps_checkout-hosted-fields-card-expiration-date">{l s='Expiry date' mod='ps_checkout'}</label>
      <div id="ps_checkout-hosted-fields-card-expiration-date" ></div>
    </div>
    <div class="form-group col-xs-6 col-6">
      <label class="form-control-label" for="ps_checkout-hosted-fields-card-cvv">{l s='CVC' mod='ps_checkout'}</label>
      <div class="ps_checkout-info-wrapper">
        <div class="ps_checkout-info-button" onmouseenter="cvvEnter()" onmouseleave="cvvLeave()">i
          <div class="popup-content" id="cvv-popup">
            <img src="{$modulePath}views/img/cvv.svg" alt="">
              {l s='The security code is a' mod='ps_checkout'} <b>{l s='3-digits' mod='ps_checkout'}</b> {l s='code on the back of your credit card. In some cases, it can be 4-digits or on the front of your card.' mod='ps_checkout'}
          </div>
        </div>
      </div>
      <div id="ps_checkout-hosted-fields-card-cvv" ></div>
    </div>
  </div>
  <div id="ps_checkout-hosted-fields-errors">
    <div id="ps_checkout-hosted-fields-error-name" class="alert alert-danger hidden">NAME</div>
    <div id="ps_checkout-hosted-fields-error-number" class="alert alert-danger hidden">NUMBER</div>
    <div id="ps_checkout-hosted-fields-error-expiry" class="alert alert-danger hidden">EXPIRY</div>
    <div id="ps_checkout-hosted-fields-error-cvv" class="alert alert-danger hidden">CVV</div>
  </div>
  <div id="payments-sdk__contingency-lightbox"></div>
</form>
<script>
  function cvvEnter() {
    var popup = document.getElementById("cvv-popup");
    popup.classList.add("show");
  }
  function cvvLeave() {
    var popup = document.getElementById("cvv-popup");
    popup.classList.remove("show");
  }
</script>
