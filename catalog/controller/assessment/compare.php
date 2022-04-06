<?php
class ControllerAssessmentCompare extends Controller {
	public function index() {
		$this->load->language('assessment/compare');

		$this->load->model('catalog/assessment');

		$this->load->model('tool/image');

		if (!isset($this->session->data['compare'])) {
			$this->session->data['compare'] = array();
		}

		if (isset($this->request->get['remove'])) {
			$key = array_search($this->request->get['remove'], $this->session->data['compare']);

			if ($key !== false) {
				unset($this->session->data['compare'][$key]);

				$this->session->data['success'] = $this->language->get('text_remove');
			}

			$this->response->redirect($this->url->link('assessment/compare'));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('assessment/compare')
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['review_status'] = $this->config->get('config_review_status');

		$data['assessments'] = array();

		$data['attribute_groups'] = array();

		foreach ($this->session->data['compare'] as $key => $assessment_id) {
			$assessment_info = $this->model_catalog_assessment->getAssessment($assessment_id);

			if ($assessment_info) {
				if ($assessment_info['image']) {
					$image = $this->model_tool_image->resize($assessment_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_compare_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_compare_height'));
				} else {
					$image = false;
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($assessment_info['price'], $assessment_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if (!is_null($assessment_info['special']) && (float)$assessment_info['special'] >= 0) {
					$special = $this->currency->format($this->tax->calculate($assessment_info['special'], $assessment_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($assessment_info['quantity'] <= 0) {
					$availability = $assessment_info['stock_status'];
				} elseif ($this->config->get('config_stock_display')) {
					$availability = $assessment_info['quantity'];
				} else {
					$availability = $this->language->get('text_instock');
				}

				$attribute_data = array();

				$attribute_groups = $this->model_catalog_assessment->getAssessmentAttributes($assessment_id);

				foreach ($attribute_groups as $attribute_group) {
					foreach ($attribute_group['attribute'] as $attribute) {
						$attribute_data[$attribute['attribute_id']] = $attribute['text'];
					}
				}

				$data['assessments'][$assessment_id] = array(
					'assessment_id'   => $assessment_info['assessment_id'],
					'name'         => $assessment_info['name'],
					'thumb'        => $image,
					'price'        => $price,
					'special'      => $special,
					'description'  => utf8_substr(strip_tags(html_entity_decode($assessment_info['description'], ENT_QUOTES, 'UTF-8')), 0, 200) . '..',
					'model'        => $assessment_info['model'],
					'manufacturer' => $assessment_info['manufacturer'],
					'availability' => $availability,
					'minimum'      => $assessment_info['minimum'] > 0 ? $assessment_info['minimum'] : 1,
					'rating'       => (int)$assessment_info['rating'],
					'reviews'      => sprintf($this->language->get('text_reviews'), (int)$assessment_info['reviews']),
					// 'weight'       => $this->weight->format($assessment_info['weight'], $assessment_info['weight_class_id']),
					// 'length'       => $this->length->format($assessment_info['length'], $assessment_info['length_class_id']),
					// 'width'        => $this->length->format($assessment_info['width'], $assessment_info['length_class_id']),
					// 'height'       => $this->length->format($assessment_info['height'], $assessment_info['length_class_id']),
					'attribute'    => $attribute_data,
					'href'         => $this->url->link('assessment/assessment', 'assessment_id=' . $assessment_id),
					'remove'       => $this->url->link('assessment/compare', 'remove=' . $assessment_id)
				);

				foreach ($attribute_groups as $attribute_group) {
					$data['attribute_groups'][$attribute_group['attribute_group_id']]['name'] = $attribute_group['name'];

					foreach ($attribute_group['attribute'] as $attribute) {
						$data['attribute_groups'][$attribute_group['attribute_group_id']]['attribute'][$attribute['attribute_id']]['name'] = $attribute['name'];
					}
				}
			} else {
				unset($this->session->data['compare'][$key]);
			}
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('assessment/compare', $data));
	}

	public function add() {
		$this->load->language('assessment/compare');

		$json = array();

		if (!isset($this->session->data['compare'])) {
			$this->session->data['compare'] = array();
		}

		if (isset($this->request->post['assessment_id'])) {
			$assessment_id = $this->request->post['assessment_id'];
		} else {
			$assessment_id = 0;
		}

		$this->load->model('catalog/assessment');

		$assessment_info = $this->model_catalog_assessment->getAssessment($assessment_id);

		if ($assessment_info) {
			if (!in_array($this->request->post['assessment_id'], $this->session->data['compare'])) {
				if (count($this->session->data['compare']) >= 4) {
					array_shift($this->session->data['compare']);
				}

				$this->session->data['compare'][] = $this->request->post['assessment_id'];
			}

			$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('assessment/assessment', 'assessment_id=' . $this->request->post['assessment_id']), $assessment_info['name'], $this->url->link('assessment/compare'));

			$json['total'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
