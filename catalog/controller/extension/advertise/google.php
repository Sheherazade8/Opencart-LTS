<?php

use \googleshopping\traits\StoreLoader;
use \googleshopping\traits\LibraryLoader;

class ControllerExtensionAdvertiseGoogle extends Controller {
    use StoreLoader;
    use LibraryLoader;

    private $store_id = 0;

    public function __construct($registry) {
        parent::__construct($registry);

        if (getenv("ADVERTISE_GOOGLE_STORE_ID")) {
            $this->store_id = (int)getenv("ADVERTISE_GOOGLE_STORE_ID");
        } else {
            $this->store_id = (int)$this->config->get('config_store_id');
        }

        $this->loadStore($this->store_id);
    }

    public function google_global_site_tag(&$route, &$data, &$output) {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return;
        }

        // If there is no tracker, do nothing
        if (!$this->setting->has('advertise_google_conversion_tracker')) {
            return;
        }

        $tracker = $this->setting->get('advertise_google_conversion_tracker');

        // Insert the tags before the closing <head> tag
        $output = str_replace('</head>', $tracker['google_global_site_tag'] . '</head>', $output);
    }

    public function before_checkout_success(&$route, &$data) {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return;
        }

        // If there is no tracker, do nothing
        if (!$this->setting->has('advertise_google_conversion_tracker')) {
            return;
        }

        // In case there is no order, do nothing
        if (!isset($this->session->data['order_id'])) {
            return;
        }

        if (!$this->registry->has('googleshopping')) {
            $this->loadLibrary($this->store_id);
        }

        $this->load->model('checkout/order');
        $this->load->model('extension/advertise/google');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $tracker = $this->setting->get('advertise_google_conversion_tracker');
        $currency = $order_info['currency_code'];
        
        $total = $this->googleshopping->convertAndFormat($order_info['total'], $currency);

        $search = array(
            '{VALUE}',
            '{CURRENCY}'
        );

        $replace = array(
            $total,
            $currency
        );

        $snippet = str_replace($search, $replace, $tracker['google_event_snippet']);

        // Store the snippet to display it in the order success view
        $tax = 0;
        $shipping = 0;
        $coupon = $this->model_extension_advertise_google->getCoupon($order_info['order_id']);

        foreach ($this->model_checkout_order->getOrderTotals($order_info['order_id']) as $order_total) {
            if ($order_total['code'] == 'shipping') {
                $shipping += $this->googleshopping->convertAndFormat($order_total['value'], $currency);
            }

            if ($order_total['code'] == 'tax') {
                $tax += $this->googleshopping->convertAndFormat($order_total['value'], $currency);
            }
        }

        $order_assessments = $this->model_checkout_order->getOrderAssessments($order_info['order_id']);

        foreach ($order_assessments as &$order_assessment) {
            $order_assessment['option'] = $this->model_checkout_order->getOrderOptions($order_info['order_id'], $order_assessment['order_assessment_id']);
        }

        $purchase_data = array(
            'transaction_id' => $order_info['order_id'],
            'value' => $total,
            'currency' => $currency,
            'tax' => $tax,
            'shipping' => $shipping,
            'items' => $this->model_extension_advertise_google->getRemarketingItems($order_assessments, $order_info['store_id']),
            'ecomm_prodid' => $this->model_extension_advertise_google->getRemarketingAssessmentIds($order_assessments, $order_info['store_id'])
        );

        if ($coupon !== null) {
            $purchase_data['coupon'] = $coupon;
        }

        $this->googleshopping->setEventSnippet($snippet);
        $this->googleshopping->setPurchaseData($purchase_data);
    }

    public function google_dynamic_remarketing_purchase(&$route, &$data, &$output) {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return;
        }

        // If the library has not been loaded, or if there is no snippet, do nothing
        if (!$this->registry->has('googleshopping') || $this->googleshopping->getEventSnippet() === null || $this->googleshopping->getPurchaseData() === null) {
            return;
        }

        $data['send_to'] = $this->googleshopping->getEventSnippetSendTo();

        $purchase_data = $this->googleshopping->getPurchaseData();

        $data['transaction_id'] = $purchase_data['transaction_id'];
        $data['value'] = $purchase_data['value'];
        $data['currency'] = $purchase_data['currency'];
        $data['tax'] = $purchase_data['tax'];
        $data['shipping'] = $purchase_data['shipping'];
        $data['items'] = json_encode($purchase_data['items']);
        $data['ecomm_prodid'] = json_encode($purchase_data['ecomm_prodid']);
        $data['ecomm_totalvalue'] = $purchase_data['value'];

        $purchase_snippet = $this->load->view('extension/advertise/google_dynamic_remarketing_purchase', $data);

        // Insert the snippet after the output
        $output = str_replace('</body>', $this->googleshopping->getEventSnippet() . $purchase_snippet . '</body>', $output);
    }

    public function google_dynamic_remarketing_home(&$route, &$data, &$output) {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return;
        }

        // If we are not on the home page, do nothing
        if (isset($this->request->get['route']) && $this->request->get['route'] != $this->config->get('action_default')) {
            return;
        }

        if (!$this->registry->has('googleshopping')) {
            $this->loadLibrary($this->store_id);
        }

        if (null === $this->googleshopping->getEventSnippetSendTo()) {
            return;
        }

        $data = array();
        $data['send_to'] = $this->googleshopping->getEventSnippetSendTo();

        $snippet = $this->load->view('extension/advertise/google_dynamic_remarketing_home', $data);

        // Insert the snippet after the output
        $output = str_replace('</body>', $snippet . '</body>', $output);
    }

    public function google_dynamic_remarketing_searchresults(&$route, &$data, &$output) {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return;
        }

        // If we are not on the search page, do nothing
        if (!isset($this->request->get['route']) || $this->request->get['route'] != 'assessment/search' || !isset($this->request->get['search'])) {
            return;
        }

        if (!$this->registry->has('googleshopping')) {
            $this->loadLibrary($this->store_id);
        }

        if (null === $this->googleshopping->getEventSnippetSendTo()) {
            return;
        }

        $data = array();
        $data['send_to'] = $this->googleshopping->getEventSnippetSendTo();
        $data['search_term'] = $this->request->get['search'];

        $snippet = $this->load->view('extension/advertise/google_dynamic_remarketing_searchresults', $data);

        // Insert the snippet after the output
        $output = str_replace('</body>', $snippet . '</body>', $output);
    }

    public function google_dynamic_remarketing_exam(&$route, &$data, &$output) {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return;
        }

        // If we are not on the search page, do nothing
        if (!isset($this->request->get['route']) || $this->request->get['route'] != 'assessment/exam') {
            return;
        }

        if (!$this->registry->has('googleshopping')) {
            $this->loadLibrary($this->store_id);
        }

        if (null === $this->googleshopping->getEventSnippetSendTo()) {
            return;
        }

        if (isset($this->request->get['path'])) {
            $parts = explode('_', $this->request->get['path']);
            $exam_id = (int)end($parts);
        } else if (isset($this->request->get['exam_id'])) {
            $exam_id = (int)$this->request->get['exam_id'];
        } else {
            $exam_id = 0;
        }

        $this->load->model('extension/advertise/google');

        $data = array();
        $data['send_to'] = $this->googleshopping->getEventSnippetSendTo();
        $data['description'] = str_replace('"', '\\"', $this->model_extension_advertise_google->getHumanReadableOpenCartExam($exam_id));

        $snippet = $this->load->view('extension/advertise/google_dynamic_remarketing_exam', $data);

        // Insert the snippet after the output
        $output = str_replace('</body>', $snippet . '</body>', $output);
    }

    public function google_dynamic_remarketing_assessment(&$route, &$data, &$output) {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return;
        }

        // If we do not know the viewed assessment, do nothing
        if (!isset($this->request->get['assessment_id']) || !isset($this->request->get['route']) || $this->request->get['route'] != 'assessment/assessment') {
            return;
        }

        $this->load->model('catalog/assessment');

        $assessment_info = $this->model_catalog_assessment->getAssessment((int)$this->request->get['assessment_id']);

        // If assessment does not exist, do nothing
        if (!$assessment_info) {
            return;
        }

        if (!$this->registry->has('googleshopping')) {
            $this->loadLibrary($this->store_id);
        }

        if (null === $this->googleshopping->getEventSnippetSendTo()) {
            return;
        }

        $this->load->model('extension/advertise/google');

        $exam_name = $this->model_extension_advertise_google->getHumanReadableExam($assessment_info['assessment_id'], $this->store_id);

        $option_map = $this->model_extension_advertise_google->getSizeAndColorOptionMap($assessment_info['assessment_id'], $this->store_id);

        $data = array();
        $data['send_to'] = $this->googleshopping->getEventSnippetSendTo();
        $data['option_map'] = json_encode($option_map);
        $data['brand'] = $assessment_info['manufacturer'];
        $data['name'] = $assessment_info['name'];
        $data['exam'] = str_replace('"', '\\"', $exam_name);

        $snippet = $this->load->view('extension/advertise/google_dynamic_remarketing_assessment', $data);

        // Insert the snippet after the output
        $output = str_replace('</body>', $snippet . '</body>', $output);
    }

    public function google_dynamic_remarketing_cart(&$route, &$data, &$output) {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return;
        }

        // If we are not on the cart page, do nothing
        if (!isset($this->request->get['route']) || $this->request->get['route'] != 'checkout/cart') {
            return;
        }

        if (!$this->registry->has('googleshopping')) {
            $this->loadLibrary($this->store_id);
        }

        if (null === $this->googleshopping->getEventSnippetSendTo()) {
            return;
        }

        $this->load->model('catalog/assessment');
        $this->load->model('extension/advertise/google');

        $data = array();
        $data['send_to'] = $this->googleshopping->getEventSnippetSendTo();
        $data['ecomm_totalvalue'] = $this->cart->getTotal();
        $data['ecomm_prodid'] = json_encode($this->model_extension_advertise_google->getRemarketingAssessmentIds($this->cart->getAssessments(), $this->store_id));
        $data['items'] = json_encode($this->model_extension_advertise_google->getRemarketingItems($this->cart->getAssessments(), $this->store_id));

        $snippet = $this->load->view('extension/advertise/google_dynamic_remarketing_cart', $data);

        // Insert the snippet after the output
        $output = str_replace('</body>', $snippet . '</body>', $output);
    }

    public function cron($cron_id = null, $code = null, $cycle = null, $date_added = null, $date_modified = null) {
        $this->loadLibrary($this->store_id);

        if (!$this->validateCRON()) {
            // In case this is not a CRON task
            return;
        }

        $this->load->language('extension/advertise/google');

        // Reset taxes to use the store address and zone
        $this->tax->setShippingAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
        $this->tax->setPaymentAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
        $this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));

        $this->googleshopping->cron();
    }

    protected function validateCRON() {
        if (!$this->setting->get('advertise_google_status')) {
            // In case the extension is disabled, do nothing
            return false;
        }

        if (!$this->setting->get('advertise_google_gmc_account_selected')) {
            return false;
        }

        if (!$this->setting->get('advertise_google_gmc_shipping_taxes_configured')) {
            return false;
        }

        try {
            if (count($this->googleshopping->getTargets($this->store_id)) === 0) {
                return false;
            }
        } catch (\RuntimeException $e) {
            return false;
        }

        if (isset($this->request->get['cron_token']) && $this->request->get['cron_token'] == $this->config->get('advertise_google_cron_token')) {
            return true;
        }

        if (defined('ADVERTISE_GOOGLE_ROUTE')) {
            return true;
        }

        return false;
    }
}