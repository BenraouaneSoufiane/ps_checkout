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
use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\Entity\PaypalAccount;
use PrestaShop\Module\PrestashopCheckout\Updater\PaypalAccountUpdater;

class AdminPaypalOnboardingPrestashopCheckoutController extends ModuleAdminController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * {@inheritdoc}
     */
    public function postProcess()
    {
        $env = new \PrestaShop\Module\PrestashopCheckout\Environment\PaymentEnv();
        $qaMode = "1" === $env->getEnv('PS_CHECKOUT_QA_MODE');

        if ($qaMode) {
            $this->setMockPostValues();
        }

        try {
            $idMerchant = Tools::getValue('merchantIdInPayPal');

            if (true === empty($idMerchant)) {
                $this->errors[] = $this->module->l('We didn\'t receive your PayPal Merchant identifier.');

                return false;
            }

            if (!Validate::isGenericName($idMerchant) || PaypalAccountUpdater::MIN_ID_LENGTH > strlen($idMerchant)) {
                $this->errors[] = $this->module->l('Your PayPal Merchant identifier seems invalid.');

                return false;
            }

            $paypalAccount = new PaypalAccount($idMerchant);

            /** @var \PrestaShop\Module\PrestashopCheckout\PersistentConfiguration $persistentConfiguration */
            $persistentConfiguration = $this->module->getService('ps_checkout.persistent.configuration');

            if ($persistentConfiguration->savePaypalAccount($paypalAccount)) {
                // Update onboarding session
                /** @var \PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionManager $onboardingSessionManager */
                $onboardingSessionManager = $this->module->getService('ps_checkout.session.onboarding.manager');
                $openedSession = $onboardingSessionManager->getOpened();
                $data = json_decode($openedSession->getData());
                $data->shop->merchant_id = $idMerchant;
                $data->shop->permissions_granted = Tools::getValue('permissionsGranted');
                $data->shop->consent_status = Tools::getValue('consentStatus');
                $data->shop->risk_status = Tools::getValue('riskStatus');
                $data->shop->account_status = Tools::getValue('accountStatus');
                $data->shop->is_email_confirmed = Tools::getValue('isEmailConfirmed');

                $openedSession->setData(json_encode($data));
                $onboardingSessionManager->apply('onboard_paypal', $openedSession->toArray(true));
            }

            /** @var PaypalAccountUpdater $accountUpdater */
            $accountUpdater = $this->module->getService('ps_checkout.updater.paypal.account');
            $accountUpdater->update($paypalAccount);

            if ($paypalAccount->getCardPaymentStatus() === PaypalAccountUpdater::SUBSCRIBED) {
                // track account paypal fully approved
                $this->module->getService('ps_checkout.segment.tracker')->track('Account Paypal Fully Approved', Shop::getContextListShopID());
            }

            if ($qaMode) {
                $this->updatePayPalAccountWithMockCredentials();
            }

            Tools::redirect(
                (new LinkAdapter($this->context->link))->getAdminLink(
                    'AdminModules',
                    true,
                    [],
                    [
                        'configure' => 'ps_checkout',
                    ]
                )
            );
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function initCursedPage()
    {
        if (!$this->checkToken()) {
            $this->errors[] = $this->module->l('It seems your employee token is invalid.');
        }

        if (file_exists(_PS_ROOT_DIR_ . '/' . $this->admin_webpath . '/themes/' . $this->bo_theme . '/public/theme.css')) {
            $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/public/theme.css', 'all', 0);
        } elseif (file_exists(_PS_ROOT_DIR_ . '/' . $this->admin_webpath . '/themes/new-theme/public/theme.css')) {
            $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/new-theme/public/theme.css', 'all', 1);
        } elseif (isset($this->bo_css) && file_exists(_PS_ROOT_DIR_ . '/' . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/' . $this->bo_css)) {
            $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/' . $this->bo_css, 'all', 0);
        }

        $this->context->smarty->assign([
            'img_dir' => _PS_IMG_,
            'iso' => $this->context->language->iso_code,
            'shop_name' => Configuration::get('PS_SHOP_NAME'),
            'meta_title' => $this->module->displayName,
            'navigationPipe' => Configuration::get('PS_NAVIGATION_PIPE') ? Configuration::get('PS_NAVIGATION_PIPE') : '>',
            'css_files' => $this->css_files,
            'js_files' => array_unique($this->js_files),
            'errors' => $this->errors,
            'logoSrc' => $this->module->getPathUri() . 'logo.png',
            'moduleLink' => (new LinkAdapter($this->context->link))->getAdminLink(
                'AdminModules',
                true,
                [],
                [
                    'configure' => 'ps_checkout',
                ]
            ),
        ]);

        echo $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/cursedPage.tpl');

        exit;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!isset($this->context->employee) || !$this->context->employee->isLoggedBack()) {
            // Avoid redirection to Login page because we want display additional information
            $this->errors[] = $this->module->l('It seems you are logged out.');
            $this->initCursedPage();
        }

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    protected function isAnonymousAllowed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function display()
    {
        if ($this->errors) {
            $this->initCursedPage();
        }

        parent::display();
    }

    private function setMockPostValues()
    {
        $_GET["merchantIdInPayPal"] = $_ENV['QA_MERCHANT_ID_PAYPAL'];
        $_GET["merchantId"] = $_ENV['QA_MERCHANT_ID'];
        $_GET["permissionsGranted"] = $_ENV['QA_PERMISSIONS_GRANTED'];
        $_GET["accountStatus"] = $_ENV['QA_ACCOUNT_STATUS'];
        $_GET["consentStatus"] = $_ENV['QA_CONSENT_STATUS'];
        $_GET["productIntentId"] = $_ENV['QA_PRODUCT_INTENT_ID'];
        $_GET["isEmailConfirmed"] = $_ENV['QA_EMAIL_CONFIRMED'];
        $_GET["returnMessage"] = $_ENV['QA_RETURN_MESSAGE'];
        $_GET["riskStatus"] = $_ENV['QA_RISK_STATUS'];
        $_GET["productIntentID"] = $_ENV['QA_PRODUCT_INTENT_ID'];
    }

    private function updatePayPalAccountWithMockCredentials()
    {
        $paypalAccount = new PaypalAccount(
            $_ENV['QA_MERCHANT_ID'],
            $_ENV['QA_PAYPAL_EMAIL'],
            $_ENV['QA_PAYPAL_EMAIL_VERIFIED'],
            $_ENV['QA_PAYPAL_PAYMENT_STATUS'],
            $_ENV['QA_PAYPAL_CARD_PAYMENT_STATUS'],
            $_ENV['QA_PAYPAL_MERCHANT_COUNTRY']
        );

        /** @var \PrestaShop\Module\PrestashopCheckout\PersistentConfiguration $persistentConfiguration */
        $persistentConfiguration = $this->module->getService('ps_checkout.persistent.configuration');

        $persistentConfiguration->savePaypalAccount($paypalAccount);
    }
}
