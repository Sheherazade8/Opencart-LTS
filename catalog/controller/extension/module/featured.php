<?php
class ControllerExtensionModuleFeatured extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/featured');

		$this->load->model('catalog/assessment');

		$this->load->model('tool/image');

		$data['assessments'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if (!empty($setting['assessment'])) {
			$assessments = array_slice($setting['assessment'], 0, (int)$setting['limit']);

			foreach ($assessments as $assessment_id) {
				$assessment_info = $this->model_catalog_assessment->getAssessment($assessment_id);

				if ($assessment_info) {
					if ($assessment_info['image']) {
						$image = $this->model_tool_image->resize($assessment_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($assessment_info['price'], $assessment_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if (!is_null($assessment_info['special']) && (float)$assessment_info['special'] >= 0) {
						$special = $this->currency->format($this->tax->calculate($assessment_info['special'], $assessment_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$tax_price = (float)$assessment_info['special'];
					} else {
						$special = false;
						$tax_price = (float)$assessment_info['price'];
					}
		
					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format($tax_price, $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $assessment_info['rating'];
					} else {
						$rating = false;
					}

					$data['assessments'][] = array(
						'assessment_id'  => $assessment_info['assessment_id'],
						'thumb'       => $image,
						'name'        => $assessment_info['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($assessment_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_assessment_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('assessment/assessment', 'assessment_id=' . $assessment_info['assessment_id'])
					);
				}
			}
		}

		if ($data['assessments']) {
			return $this->load->view('extension/module/featured', $data);
		}
	}
}