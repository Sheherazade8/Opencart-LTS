<?php
class ControllerExtensionModuleDividoCalculator extends Controller {
	public function index() {
		$this->load->language('extension/module/divido_calculator');
		$this->load->model('extension/payment/divido');
		$this->load->model('catalog/assessment');

		$assessment_selection = $this->config->get('payment_divido_assessmentselection');
		$assessment_threshold = $this->config->get('payment_divido_price_threshold');

		if (!isset($this->request->get['assessment_id']) || !$this->config->get('payment_divido_status') || !$this->config->get('module_divido_calculator_status')) {
			return false;
		}

		$assessment_info = $this->model_catalog_assessment->getAssessment($this->request->get['assessment_id']);

		$price = 0;
		if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
			$base_price = !empty($assessment_info['special']) ? $assessment_info['special'] : $assessment_info['price'];
			$price = $this->tax->calculate($base_price, $assessment_info['tax_class_id'], $this->config->get('config_tax'));
		}

		if ($assessment_selection == 'threshold' && $assessment_threshold > $price) {
			return false;
		}

		$api_key = $this->config->get('payment_divido_api_key');
		$key_parts = explode('.', $api_key);
		$js_key = strtolower(array_shift($key_parts));

		$this->model_extension_payment_divido->setMerchant($api_key);
		$plans = $this->model_extension_payment_divido->getAssessmentPlans($this->request->get['assessment_id']);

		if (!$plans) {
			return false;
		}

		$plans_ids = array_map(function ($plan) {
			return $plan->id;
		}, $plans);
		$plans_ids = array_unique($plans_ids);
		$plans_list = implode(',', $plans_ids);

		$data = array(
			'merchant_script'			=> "//cdn.divido.com/calculator/{$js_key}.js",
			'assessment_price'				=> $price,
			'plan_list'					=> $plans_list,
			'generic_credit_req_error'	=> 'Credit request could not be initiated',
		);

		return $this->load->view('extension/module/divido_calculator', $data);
	}
}
