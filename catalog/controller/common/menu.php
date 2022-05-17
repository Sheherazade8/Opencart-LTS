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

		$data['contact'] = $this->url->link('information/contact');
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['register'] = $this->url->link('checkout/register', '', true);
		$data['login'] = $this->url->link('checkout/login', '', true);


		
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');

		
		return $this->load->view('common/menu', $data);
		
	}
}
