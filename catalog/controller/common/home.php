<?php
class ControllerCommonHome extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

		$this->load->language('common/home');

		$this->load->model('catalog/exam');

		$bestsellers = $this->model_catalog_exam->getExamsByFilter('Bestsellers');
		$data['bestsellers'] = array();

		foreach ($bestsellers as $bestseller) {
			$data['bestsellers'][] = array(
				'name'     => $bestseller['name'],
				'price'    => $bestseller['price'],
				'href'     => $this->url->link('assessment/exam', 'path=' . $bestseller['path'])
			);
		}

		$products = $this->model_catalog_exam->getExamsByFilter('Preparation Products');
		$data['preparation_products'] = array();

		foreach ($bproducts as $product) {
			$data['preparation_products'][] = array(
				'name'     => $product['name'],
				'price'    => $product['price'],
				'href'     => $this->url->link('assessment/exam', 'path=' . $product['path'])
			);
		}


		$data['preparation_products'] = $this->model_catalog_exam->getExamsByFilter('Preparation Products');


		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['search'] = $this->load->controller('common/search');

		$this->response->setOutput($this->load->view('common/home', $data));
	}
}
