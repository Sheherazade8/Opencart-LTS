<?php
class ControllerCommonMenu extends Controller {
	public function index() {
		$this->load->language('common/menu');

		// Menu
		$this->load->model('catalog/exam');

		$this->load->model('catalog/assessment');

		$data['exams'] = array();

		$exams = $this->model_catalog_exam->getExams(0);

		foreach ($exams as $exam) {
			if ($exam['top']) {
				// Level 2
				$children_data = array();

				$children = $this->model_catalog_exam->getExams($exam['exam_id']);

				foreach ($children as $child) {
					$filter_data = array(
						'filter_exam_id'  => $child['exam_id'],
						'filter_sub_exam' => true
					);

					$children_data[] = array(
						'name'  => $child['name'] . ($this->config->get('config_assessment_count') ? ' (' . $this->model_catalog_assessment->getTotalAssessments($filter_data) . ')' : ''),
						'href'  => $this->url->link('assessment/exam', 'path=' . $exam['exam_id'] . '_' . $child['exam_id'])
					);
				}

				// Level 1
				$data['exams'][] = array(
					'name'     => $exam['name'],
					'children' => $children_data,
					'column'   => $exam['column'] ? $exam['column'] : 1,
					'href'     => $this->url->link('assessment/exam', 'path=' . $exam['exam_id'])
				);
			}
		}

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 2
				$children_data = array();

				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach ($children as $child) {
					$filter_data = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					);

					$children_data[] = array(
						'name'  => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
						'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
					);
				}

				// Level 1
				$data['categories'][] = array(
					'name'     => $category['name'],
					'children' => $children_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}


		$data['contact'] = $this->url->link('information/contact');
		$data['cart'] = $this->url->link('checkout/cart');
		$data['register'] = $this->url->link('checkout/register', '', true);
		$data['login'] = $this->url->link('checkout/login', '', true);


		
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');

		
		return $this->load->view('common/menu', $data);
		
	}
}
