<?php
class ControllerAccountWishList extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/wishlist', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/wishlist');

		$this->load->model('account/wishlist');

		$this->load->model('catalog/assessment');

		$this->load->model('tool/image');

		if (isset($this->request->get['remove'])) {
			// Remove Wishlist
			$this->model_account_wishlist->deleteWishlist($this->request->get['remove']);

			$this->session->data['success'] = $this->language->get('text_remove');

			$this->response->redirect($this->url->link('account/wishlist'));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/wishlist')
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['assessments'] = array();

		$results = $this->model_account_wishlist->getWishlist();

		foreach ($results as $result) {
			$assessment_info = $this->model_catalog_assessment->getAssessment($result['assessment_id']);

			if ($assessment_info) {
				if ($assessment_info['image']) {
					$image = $this->model_tool_image->resize($assessment_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_height'));
				} else {
					$image = false;
				}

				if ($assessment_info['quantity'] <= 0) {
					$stock = $assessment_info['stock_status'];
				} elseif ($this->config->get('config_stock_display')) {
					$stock = $assessment_info['quantity'];
				} else {
					$stock = $this->language->get('text_instock');
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($assessment_info['price'], $assessment_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$assessment_info['special']) {
					$special = $this->currency->format($this->tax->calculate($assessment_info['special'], $assessment_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				$data['assessments'][] = array(
					'assessment_id' => $assessment_info['assessment_id'],
					'thumb'      => $image,
					'name'       => $assessment_info['name'],
					'model'      => $assessment_info['model'],
					// Nouveau code
					'date' => $assessment_info['date'],
					'exam' => $assessment_info['exam'],

					'stock'      => $stock,
					'price'      => $price,
					'special'    => $special,
					'href'       => $this->url->link('assessment/assessment', 'assessment_id=' . $assessment_info['assessment_id']),
					'remove'     => $this->url->link('account/wishlist', 'remove=' . $assessment_info['assessment_id'])
				);
			} else {
				$this->model_account_wishlist->deleteWishlist($result['assessment_id']);
			}
		}

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/wishlist', $data));
	}

	public function add() {
		$this->load->language('account/wishlist');

		$json = array();

		if (isset($this->request->post['assessment_id'])) {
			$assessment_id = $this->request->post['assessment_id'];
		} else {
			$assessment_id = 0;
		}

		$this->load->model('catalog/assessment');

		$assessment_info = $this->model_catalog_assessment->getAssessment($assessment_id);

		if ($assessment_info) {
			if ($this->customer->isLogged()) {
				// Edit customers cart
				$this->load->model('account/wishlist');

				$this->model_account_wishlist->addWishlist($this->request->post['assessment_id']);

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('assessment/assessment', 'assessment_id=' . (int)$this->request->post['assessment_id']), $assessment_info['name'], $this->url->link('account/wishlist'));

				$json['total'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
			} else {
				if (!isset($this->session->data['wishlist'])) {
					$this->session->data['wishlist'] = array();
				}

				$this->session->data['wishlist'][] = $this->request->post['assessment_id'];

				$this->session->data['wishlist'] = array_unique($this->session->data['wishlist']);

				$json['success'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true), $this->url->link('assessment/assessment', 'assessment_id=' . (int)$this->request->post['assessment_id']), $assessment_info['name'], $this->url->link('account/wishlist'));

				$json['total'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
