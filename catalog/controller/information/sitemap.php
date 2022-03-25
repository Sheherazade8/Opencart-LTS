<?php
class ControllerInformationSitemap extends Controller {
	public function index() {
		$this->load->language('information/sitemap');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/sitemap')
		);

		$this->load->model('catalog/exam');

		$data['exams'] = array();

		$exams_1 = $this->model_catalog_exam->getExams(0);

		foreach ($exams_1 as $exam_1) {
			$level_2_data = array();

			$exams_2 = $this->model_catalog_exam->getExams($exam_1['exam_id']);

			foreach ($exams_2 as $exam_2) {
				$level_3_data = array();

				$exams_3 = $this->model_catalog_exam->getExams($exam_2['exam_id']);

				foreach ($exams_3 as $exam_3) {
					$level_3_data[] = array(
						'name' => $exam_3['name'],
						'href' => $this->url->link('assessment/exam', 'path=' . $exam_1['exam_id'] . '_' . $exam_2['exam_id'] . '_' . $exam_3['exam_id'])
					);
				}

				$level_2_data[] = array(
					'name'     => $exam_2['name'],
					'children' => $level_3_data,
					'href'     => $this->url->link('assessment/exam', 'path=' . $exam_1['exam_id'] . '_' . $exam_2['exam_id'])
				);
			}

			$data['exams'][] = array(
				'name'     => $exam_1['name'],
				'children' => $level_2_data,
				'href'     => $this->url->link('assessment/exam', 'path=' . $exam_1['exam_id'])
			);
		}

		$data['special'] = $this->url->link('assessment/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['password'] = $this->url->link('account/password', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		$data['history'] = $this->url->link('account/order', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['search'] = $this->url->link('assessment/search');
		$data['contact'] = $this->url->link('information/contact');

		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			$data['informations'][] = array(
				'title' => $result['title'],
				'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
			);
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/sitemap', $data));
	}
}