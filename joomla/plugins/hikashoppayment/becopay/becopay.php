<?php
/**
 * @package    Hikashop-Becopay-Gateway
 *
 * @author     Becopay Team <io@becopay.com>
` * @copyright  (C) 2018-2019 Becopay. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 * @link       http://becopay.com/en
 */

defined('_JEXEC') or die('Restricted access');

include_once "vendor/autoload.php";

use Becopay\PaymentGateway;

/**
 * Class plgHikashoppaymentBecopay
 *
 * @since 1.0.0
 */
class plgHikashoppaymentBecopay extends hikashopPaymentPlugin
{
    /**
     * Return order url
     *
     * @since 1.0.0
     */
    Const RETURN_URL = "index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=";
    /**
     * Cancel order url
     *
     * @since 1.0.0
     */
    Const CANCEL_URL = "index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=";
    /**
     * Notify order url
     *
     * @since 1.0.0
     */
    Const NOTIFY_URL = "index.php?option=com_hikashop&ctrl=checkout&task=notify&amp;notif_payment=";
    /**
     * Checkout order url
     *
     * @since 1.0.0
     */
    Const CHECKOUT_URL = "index.php?option=com_hikashop&ctrl=checkout";

    /**
     * Merchant Default Currency
     *
     * @since 1.0.0
     */
    Const MERCHANT_CURRENCY = 'IRR';
    /**
     * List of the plugin's accepted currencies.
     * The plugin won't appear on the checkout if the current currency is not in that list.
     *
     * @var array
     * @since 1.0.0
     */
    public $accepted_currencies = array("EUR", "USD", "IRR", "TOM");

    /**
     * List of currency must be convert before create becopay invoice
     *
     * @var array
     * @since 1.0.0
     */
    private $convert_currecny = array(
        'TOM' => array(
            'to' => 'IRR',
            'ratio' => '10'
        )
    );

    /**
     * Multiple plugin configurations.
     * It should usually be set to true
     *
     * @var bool
     * @since 1.0.0
     */
    public $multiple = true;

    /**
     * Payment plugin name (the name of the PHP file)
     *
     * @var string
     * @since 1.0.0
     */
    public $name = 'becopay';

    /**
     * This array contains the specific configuration needed (Back end > payment plugin edition), depending of the
     * plugin requirements. They will vary based on your needs for the integration with your payment gateway. The first
     * parameter is the name of the field. In upper case for a translation key. The available types (second parameter)
     * are: input (an input field), html (when you want to display some custom HTML to the shop owner), textarea (when
     * you want the shop owner to write a bit more than in an input field), big-textarea (when you want the shop owner
     * to write a lot more than in an input field), boolean (for a yes/no choice), checkbox (for checkbox selection),
     * list (for dropdown selection) , orderstatus (to be able to select between the available order statuses) The
     * third parameter is the default value.
     *
     * @var array
     * @since 1.0.0
     */
    public $pluginConfig = array(
        'mobile' => array("MOBILE", 'input'),
        'api_base_url' => array("API_BASE_URL", 'input'),
        'api_key' => array("API_KEY", 'input'),
        'merchant_currency' => array("MERCHANT_CURRENCY", 'input'),
        'debug' => array('DEBUG', 'boolean', '0'),
        'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
        'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
        'notify_url' => array('CALLBACK_URL', 'html', '')
    );

    /**
     * The constructor is optional if you don't need to initialize some parameters of some fields of the configuration
     * and not that it can also be done in the getPaymentDefaultValues function as you will see later on
     *
     * @since 1.0.0
     *
     * @param $subject
     * @param $config
     */
    public function __construct(&$subject, $config)
    {
        //Load language file.
        JPlugin::loadLanguage('plg_hikashoppayment_becopay', JPATH_ADMINISTRATOR);

        // This is the "notification" URL of HikaShop that should be given to the payment gateway so that it can send a request to that URL in order to tell HikaShop that the payment has been done (sometimes the payment gateway doesn't do that and passes the information to the return URL, in which case you need to use that notification URL as return URL and redirect the user to the HikaShop return URL at the end of the onPaymentNotification function)
        $this->pluginConfig['notify_url'][2] = HIKASHOP_LIVE . self::NOTIFY_URL . $this->name . '&tmpl=component&orderId=';

        return parent::__construct($subject, $config);
    }


    /**
     * This function called before the checkout
     * This function checking the gateway configuration requirement
     *
     * @since 1.0.0
     *
     * @param $order
     * @param $do
     * @return bool
     */
    public function onBeforeOrderCreate(&$order, &$do)
    {
        if (parent::onBeforeOrderCreate($order, $do) === true) {
            return true;
        }

        if (empty($this->payment_params->mobile)) {
            // Enqueued messages will appear to the user, as Joomla's error messages
            $this->displayError('MOBILE_EMPTY' );
            $do = false;
        }

        if (empty($this->payment_params->api_base_url)) {
            // Enqueued messages will appear to the user, as Joomla's error messages
            $this->displayError('API_URL_EMPTY');
            $do = false;
        }

        if (empty($this->payment_params->api_key)) {
            // Enqueued messages will appear to the user, as Joomla's error messages
            $this->displayError('API_KEY_EMPTY');
            $do = false;
        }
    }


    /**
     * This function is called at the end of the checkout.
     * That's the function which should display your payment gateway redirection form with the data from HikaShop
     *
     * @since 1.0.0
     *
     * @param $order
     * @param $methods
     * @param $method_id
     *
     * @return bool
     */
    public function onAfterOrderConfirm(&$order, &$methods, $method_id)
    {
        // This is a mandatory line in order to initialize the attributes of the payment method
        parent::onAfterOrderConfirm($order, $methods, $method_id);

        $checkout_url = HIKASHOP_LIVE . self::CHECKOUT_URL;

        $price = $this->getPrice($order->cart->full_total->prices[0]->price_value_with_tax);
        $order_id = $order->order_id;

        $description = implode(array(
            'orderId:' . $order_id,
            'order number : ' . $order->order_number,
            'price:' . $price->total,
            'currency:' . $price->currency,
            'merchant currency:' . $price->merchant_currency,
            'customer email:' . $order->customer->user_email,
        ), ', ');


        try {
            $payment = new PaymentGateway(
                $this->payment_params->api_base_url,
                $this->payment_params->api_key,
                $this->payment_params->mobile
            );

            $invoice = $payment->create($order_id, $price->total, $description, $price->currency, $price->merchant_currency);
            if (!$invoice) {
                $this->displayError($payment->error);
                return $this->app->redirect($checkout_url);
            }

            if (
                $invoice->payerAmount != $price->total ||
                $invoice->payerCur != $price->currency ||
                $invoice->merchantCur != $price->merchant_currency
            ) {
                $this->displayError(JText::_('INVALID_INVOICE'));
                return $this->app->redirect($checkout_url);
            }

            $this->payment_params->payment_url = $invoice->gatewayUrl;

            // Ending the checkout, ready to be redirect to the plateform payment final form
            return $this->showPage('end');


        } catch (Exception $e) {
            $this->displayError($e->getMessage());
            return $this->app->redirect($checkout_url);
        }
    }

    /**
     * After submiting the plateform payment form, this is where the website will receive the response information
     * from the payment gateway servers and then validate or not the order
     *
     * @since 1.0.0
     *
     * @param $statuses
     * @return bool
     */
    public function onPaymentNotification(&$statuses)
    {

        $filter = JFilterInput::getInstance();

        if(!isset($_REQUEST['orderId']) || empty($_REQUEST['orderId']))
            return false;


        // The load the parameters of the plugin in $this->payment_params and the order data based on the order_id coming from the payment platform
        $order_id = $filter->clean($_REQUEST['orderId'],'INTEGER');
        $dbOrder = $this->getOrder($order_id);

        // With the order, we can load the payment method, and thus all the payment parameters
        $this->loadPaymentParams($dbOrder);
        if (empty($this->payment_params))
            return false;
        $this->loadOrderData($dbOrder);

        //get order price
        $price = $this->getPrice($dbOrder->order_full_price);

        $return_url = HIKASHOP_LIVE . self::RETURN_URL . $order_id . $this->url_itemid;
        $cancel_url = HIKASHOP_LIVE . self::CANCEL_URL . $order_id . $this->url_itemid;

        try {
            $payment = new PaymentGateway(
                $this->payment_params->api_base_url,
                $this->payment_params->api_key,
                $this->payment_params->mobile
            );

            $invoice = $payment->checkByOrderId((string)$order_id);
            if (!$invoice) {
                // This function modifies the order with the id $order_id, to attribute it the status invalid_status.
                $this->modifyOrder($order_id, $this->payment_params->invalid_status);

                $this->displayError($payment->error);
                $this->app->redirect($cancel_url);
                return false;
            }
            if (
                $invoice->payerAmount != $price->total ||
                $invoice->payerCur != $price->currency ||
                $invoice->merchantCur != $price->merchant_currency
            ) {
                $this->displayError(JText::sprintf('INVALID_INVOICE_RESPONSE',$invoice->id), true);
                $this->displayError(json_encode(array(
                    'request' => $price,
                    'response' => array(
                        'amount' => $invoice->payerAmount,
                        'currency' => $invoice->payerCur,
                        'merchant_currency' => $invoice->merchantCur
                    ),
                )));
                $this->app->redirect($cancel_url);
                return false;
            }

            if ($invoice->status == 'success') {
                $this->modifyOrder($order_id, $this->payment_params->verified_status, null, true);
                $this->app->redirect($return_url);
                return true;
            }

        } catch (Exception $e) {
            $this->displayError($e->getMessage());
        }

        $this->displayError(JText::_('OPERATION_ERROR'), true);
        $this->modifyOrder($order_id, $this->payment_params->invalid_status, null, false);
        $this->app->redirect($cancel_url);
        return false;
    }


    /**
     * To set the specific configuration (back end) default values (see $pluginConfig array)
     *
     * @since 1.0.0
     *
     * @param $element
     */
    function getPaymentDefaultValues(&$element)
    {
        $element->payment_name = JText::_('BECOPAY');
        $element->payment_description = JText::_('DECOPAY_DESCRIPTION');
        $element->payment_images = '';
        $element->payment_params->invalid_status = 'cancelled';
        $element->payment_params->verified_status = 'confirmed';
        $element->payment_params->merchant_currency = self::MERCHANT_CURRENCY;
    }

    /**
     * Validate the payment configuration parameters
     *
     * @param $element
     * @since 1.0.0
     *
     * @return bool
     * @throws Exception
     */
    public function onPaymentConfigurationSave(&$element)
    {
        if (empty($element->payment_params->mobile))
            JFactory::getApplication()->enqueueMessage(JText::_('MOBILE_REQUIRED'), 'error');

        if (empty($element->payment_params->api_base_url))
            JFactory::getApplication()->enqueueMessage(JText::_('API_URL_REQUIRED'), 'error');

        if (empty($element->payment_params->api_key))
            JFactory::getApplication()->enqueueMessage(JText::_('API_KEY_REQUIRED'), 'error');


        $element->payment_params->merchant_currency = strtoupper($element->payment_params->merchant_currency);
        if (
            !empty($element->payment_params->merchant_currency) &&
            !in_array($element->payment_params->merchant_currency, $this->accepted_currencies)
        )
            JFactory::getApplication()->enqueueMessage(JText::_('API_KEY_REQUIRED'), 'error');

        try {
            new PaymentGateway(
                $element->payment_params->api_base_url,
                $element->payment_params->api_key,
                $element->payment_params->mobile
            );
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return true;
    }


    /**
     * return order price and currency and merchant currency
     *
     * @param $price
     * @since 1.0.0
     *
     * @return object
     */
    private function getPrice($price)
    {
        $currency = $this->currency->currency_code;
        $merchant_currency = $this->payment_params->merchant_currency ?: self::MERCHANT_CURRENCY;

        if (isset($this->convert_currecny[$currency]))
            return (object)array(
                'total' => round($price, 2) * $this->convert_currecny[$currency]['ratio'],
                'currency' => round($price, 2) * $this->convert_currecny[$currency]['to'],
                'merchant_currency' => $merchant_currency
            );
        else
            return (object)array(
                'total' => round($price, 2),
                'currency' => $currency,
                'merchant_currency' => $merchant_currency
            );
    }

    /**
     * Display the error detail if debug mode is enabled
     *
     * @param $msg
     * @param $alwaysShow
     * @since 1.0.0
     *
     * @return bool
     */
    private function displayError($msg, $alwaysShow = false)
    {
        if ($this->payment_params->debug || $alwaysShow)
            $this->app->enqueueMessage(JText::_($msg), 'error');
        else
            $this->app->enqueueMessage(JText::_('OPERATION_ERROR'), 'error');

        return false;
    }
}
