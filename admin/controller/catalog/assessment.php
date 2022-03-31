<?php
class ControllerCatalogAssessment extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/assessment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/assessment');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/assessment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/assessment');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_assessment->addAssessment($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			// Nouveau code pour ajouter un filtre exam

			if (isset($this->request->get['filter_exam'])) {
				$url .= '&filter_exam=' . urlencode(html_entity_decode($this->request->get['filter_exam'], ENT_QUOTES, 'UTF-8'));
			}
			//  Fin nouveau code

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_date'])) {
				$url .= '&filter_date=' . $this->request->get['filter_date'];
			}

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/assessment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/assessment');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_assessment->editAssessment($this->request->get['assessment_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			// Nouveau code pour ajouter un filtre exam

			if (isset($this->request->get['filter_exam'])) {
				$url .= '&filter_exam=' . urlencode(html_entity_decode($this->request->get['filter_exam'], ENT_QUOTES, 'UTF-8'));
			}
			//  Fin nouveau code

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_date'])) {
				$url .= '&filter_date=' . $this->request->get['filter_date'];
			}

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/assessment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/assessment');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $assessment_id) {
				$this->model_catalog_assessment->deleteAssessment($assessment_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			// Nouveau code pour ajouter un filtre exam

			if (isset($this->request->get['filter_exam'])) {
				$url .= '&filter_exam=' . urlencode(html_entity_decode($this->request->get['filter_exam'], ENT_QUOTES, 'UTF-8'));
			}
			//  Fin nouveau code

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_date'])) {
				$url .= '&filter_date=' . $this->request->get['filter_date'];
			}

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function copy() {
		$this->load->language('catalog/assessment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/assessment');

		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $assessment_id) {
				$this->model_catalog_assessment->copyAssessment($assessment_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			// Nouveau code pour ajouter un filtre exam

			if (isset($this->request->get['filter_exam'])) {
				$url .= '&filter_exam=' . urlencode(html_entity_decode($this->request->get['filter_exam'], ENT_QUOTES, 'UTF-8'));
			}
			//  Fin nouveau code

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_date'])) {
				$url .= '&filter_date=' . $this->request->get['filter_date'];
			}

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		// Nouveau code pour ajouter un filtre exam

		if (isset($this->request->get['filter_exam'])) {
			$filter_exam = $this->request->get['filter_exam'];
		} else {
			$filter_exam = '';
		}
		//  Fin nouveau code

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = '';
		}

		if (isset($this->request->get['filter_date'])) {
			$filter_date = $this->request->get['filter_date'];
		} else {
			$filter_date = '';
		}

		if (isset($this->request->get['filter_quantity'])) {
			$filter_quantity = $this->request->get['filter_quantity'];
		} else {
			$filter_quantity = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pd.name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		// Nouveau code pour ajouter un filtre exam

		if (isset($this->request->get['filter_exam'])) {
			$url .= '&filter_exam=' . urlencode(html_entity_decode($this->request->get['filter_exam'], ENT_QUOTES, 'UTF-8'));
		}
		//  Fin nouveau code

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/assessment/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['copy'] = $this->url->link('catalog/assessment/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/assessment/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['assessments'] = array();

		$filter_data = array(
			// Nouveau code pour ajouter un filtre exam
			'filter_exam'     => $filter_exam,
			'filter_name'	  => $filter_name,
			'filter_model'	  => $filter_model,
			'filter_date'	  => $filter_date,
			'filter_quantity' => $filter_quantity,
			'filter_status'   => $filter_status,
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           => $this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');

		$assessment_total = $this->model_catalog_assessment->getTotalAssessments($filter_data);

		$results = $this->model_catalog_assessment->getAssessments($filter_data);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$special = false;

			$assessment_specials = $this->model_catalog_assessment->getAssessmentSpecials($result['assessment_id']);

			foreach ($assessment_specials  as $assessment_special) {
				if (($assessment_special['date_start'] == '0000-00-00' || strtotime($assessment_special['date_start']) < time()) && ($assessment_special['date_end'] == '0000-00-00' || strtotime($assessment_special['date_end']) > time())) {
					$special = $this->currency->format($assessment_special['price'], $this->config->get('config_currency'));

					break;
				}
			}

			// Nouveau code pour afficher exam

			// $exam_name = 'No exam';
			// $assessment_exams = $this->model_catalog_assessment->getAssessmentExam($result['assessment_id']);
			// foreach ($assessment_exams as $assessment_exam){
			// 	$this->load->model('catalog/exam');
			// 	$exam_info = $this->model_catalog_exam->getExam($assessment_exam);
			// 	if ( isset($exam_info['parent_id']) ){
			// 		$exam_name = $exam_info['name'];
			// 		break;
			// 	}
			// }
			
			
			$data['assessments'][] = array(
				'assessment_id' => $result['assessment_id'],
				'image'      => $image,
				'name'       => $result['assessment_name'],
				'model'      => $result['model'],
				// Nouveau code
				'exam' => $result['exam_name'],
				'date'       => date($result['date']),
				// 'price'      =>$this->currency->format($result['price'], $this->config->get('config_currency'))),
				'special'    => $special,
				'quantity'   => $result['quantity'],
				'status'     => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'edit'       => $this->url->link('catalog/assessment/edit', 'user_token=' . $this->session->data['user_token'] . '&assessment_id=' . $result['assessment_id'] . $url, true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		// Nouveau code pour ajouter un filtre exam

		if (isset($this->request->get['filter_exam'])) {
			$url .= '&filter_exam=' . urlencode(html_entity_decode($this->request->get['filter_exam'], ENT_QUOTES, 'UTF-8'));
		}
		//  Fin nouveau code
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		// Nouveau code pour ajouter le filtre exam
		$data['sort_exam'] = $this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . '&sort=cd.name' . $url, true);

		$data['sort_name'] = $this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $url, true);
		$data['sort_model'] = $this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . '&sort=p.model' . $url, true);
		$data['sort_date'] = $this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.date' . $url, true);
		$data['sort_quantity'] = $this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . '&sort=p.quantity' . $url, true);
		$data['sort_status'] = $this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . '&sort=p.status' . $url, true);
		$data['sort_order'] = $this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . '&sort=p.sort_order' . $url, true);

		$url = '';

		// Nouveau code pour ajouter un filtre exam

		if (isset($this->request->get['filter_exam'])) {
			$url .= '&filter_exam=' . urlencode(html_entity_decode($this->request->get['filter_exam'], ENT_QUOTES, 'UTF-8'));
		}
		//  Fin nouveau code
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $assessment_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($assessment_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($assessment_total - $this->config->get('config_limit_admin'))) ? $assessment_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $assessment_total, ceil($assessment_total / $this->config->get('config_limit_admin')));

		// Nouveau code pour ajouter un filtre exam

		$data['filter_exam'] = $filter_exam;
		
		$data['filter_name'] = $filter_name;
		$data['filter_model'] = $filter_model;
		$data['filter_date'] = $filter_date;
		$data['filter_quantity'] = $filter_quantity;
		$data['filter_status'] = $filter_status;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/assessment_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['assessment_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = array();
		}

		// Nouveau code pour rendre date obligatoire
		if (isset($this->error['date'])) {
			$data['error_date'] = $this->error['date'];
		} else {
			$data['error_date'] = array();
		}
		// Fin nouveau code

		if (isset($this->error['model'])) {
			$data['error_model'] = $this->error['model'];
		} else {
			$data['error_model'] = '';
		}

		// Nouveau code pour rendre exam obligatoire
		if (isset($this->error['exam'])) {
			$data['error_exam'] = $this->error['exam'];
		} else {
			$data['error_exam'] = array();
		}
		// Fin nouveau code

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';
		
		// Nouveau code pour ajouter un filtre exam

		if (isset($this->request->get['filter_exam'])) {
			$url .= '&filter_exam=' . urlencode(html_entity_decode($this->request->get['filter_exam'], ENT_QUOTES, 'UTF-8'));
		}
		//  Fin nouveau code

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['assessment_id'])) {
			$data['action'] = $this->url->link('catalog/assessment/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/assessment/edit', 'user_token=' . $this->session->data['user_token'] . '&assessment_id=' . $this->request->get['assessment_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/assessment', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['assessment_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$assessment_info = $this->model_catalog_assessment->getAssessment($this->request->get['assessment_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['assessment_description'])) {
			$data['assessment_description'] = $this->request->post['assessment_description'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$data['assessment_description'] = $this->model_catalog_assessment->getAssessmentDescriptions($this->request->get['assessment_id']);
		} else {
			$data['assessment_description'] = array();
		}

		if (isset($this->request->post['model'])) {
			$data['model'] = $this->request->post['model'];
		} elseif (!empty($assessment_info)) {
			$data['model'] = $assessment_info['model'];
		} else {
			$data['model'] = '';
		}

		if (isset($this->request->post['location'])) {
			$data['location'] = $this->request->post['location'];
		} elseif (!empty($assessment_info)) {
			$data['location'] = $assessment_info['location'];
		} else {
			$data['location'] = '';
		}

		$this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		if (isset($this->request->post['assessment_store'])) {
			$data['assessment_store'] = $this->request->post['assessment_store'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$data['assessment_store'] = $this->model_catalog_assessment->getAssessmentStores($this->request->get['assessment_id']);
		} else {
			$data['assessment_store'] = array(0);
		}

		if (isset($this->request->post['shipping'])) {
			$data['shipping'] = $this->request->post['shipping'];
		} elseif (!empty($assessment_info)) {
			$data['shipping'] = $assessment_info['shipping'];
		} else {
			$data['shipping'] = 1;
		}

		if (isset($this->request->post['price'])) {
			$data['price'] = $this->request->post['price'];
		} elseif (!empty($assessment_info)) {
			$data['price'] = $assessment_info['price'];
		} else {
			$data['price'] = '';
		}

		$this->load->model('catalog/recurring');

		$data['recurrings'] = $this->model_catalog_recurring->getRecurrings();

		if (isset($this->request->post['assessment_recurrings'])) {
			$data['assessment_recurrings'] = $this->request->post['assessment_recurrings'];
		} elseif (!empty($assessment_info)) {
			$data['assessment_recurrings'] = $this->model_catalog_assessment->getRecurrings($assessment_info['assessment_id']);
		} else {
			$data['assessment_recurrings'] = array();
		}

		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['tax_class_id'])) {
			$data['tax_class_id'] = $this->request->post['tax_class_id'];
		} elseif (!empty($assessment_info)) {
			$data['tax_class_id'] = $assessment_info['tax_class_id'];
		} else {
			$data['tax_class_id'] = 0;
		}

		if (isset($this->request->post['date_available'])) {
			$data['date_available'] = $this->request->post['date_available'];
		} elseif (!empty($assessment_info)) {
			$data['date_available'] = ($assessment_info['date_available'] != '0000-00-00') ? $assessment_info['date_available'] : '';
		} else {
			$data['date_available'] = date('Y-m-d');
		}

		if (isset($this->request->post['quantity'])) {
			$data['quantity'] = $this->request->post['quantity'];
		} elseif (!empty($assessment_info)) {
			$data['quantity'] = $assessment_info['quantity'];
		} else {
			$data['quantity'] = 1;
		}

		if (isset($this->request->post['minimum'])) {
			$data['minimum'] = $this->request->post['minimum'];
		} elseif (!empty($assessment_info)) {
			$data['minimum'] = $assessment_info['minimum'];
		} else {
			$data['minimum'] = 1;
		}

		if (isset($this->request->post['subtract'])) {
			$data['subtract'] = $this->request->post['subtract'];
		} elseif (!empty($assessment_info)) {
			$data['subtract'] = $assessment_info['subtract'];
		} else {
			$data['subtract'] = 1;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($assessment_info)) {
			$data['sort_order'] = $assessment_info['sort_order'];
		} else {
			$data['sort_order'] = 1;
		}

		$this->load->model('localisation/stock_status');

		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

		if (isset($this->request->post['stock_status_id'])) {
			$data['stock_status_id'] = $this->request->post['stock_status_id'];
		} elseif (!empty($assessment_info)) {
			$data['stock_status_id'] = $assessment_info['stock_status_id'];
		} else {
			$data['stock_status_id'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($assessment_info)) {
			$data['status'] = $assessment_info['status'];
		} else {
			$data['status'] = true;
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->post['manufacturer_id'])) {
			$data['manufacturer_id'] = $this->request->post['manufacturer_id'];
		} elseif (!empty($assessment_info)) {
			$data['manufacturer_id'] = $assessment_info['manufacturer_id'];
		} else {
			$data['manufacturer_id'] = 0;
		}

		if (isset($this->request->post['manufacturer'])) {
			$data['manufacturer'] = $this->request->post['manufacturer'];
		} elseif (!empty($assessment_info)) {
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($assessment_info['manufacturer_id']);

			if ($manufacturer_info) {
				$data['manufacturer'] = $manufacturer_info['name'];
			} else {
				$data['manufacturer'] = '';
			}
		} else {
			$data['manufacturer'] = '';
		}

		// Exams
		$this->load->model('catalog/exam');

		if (isset($this->request->post['exam'])) {
			$exam_id = $this->request->post['exam'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$exam_array = $this->model_catalog_assessment->getAssessmentExam($this->request->get['assessment_id']);
			$exam_id = $exam_array[0];
		} else {
			$exam_id = '';
		}


		$data['assessment_exam'] = array();

		// foreach ($exams as $exam_id) {
			$exam_info = $this->model_catalog_exam->getExam($exam_id);

			if ($exam_info) {
				$data['assessment_exam'] = array(
					'exam_id' => $exam_info['exam_id'],
					'name'        => ($exam_info['path']) ? $exam_info['path'] . ' &gt; ' . $exam_info['name'] : $exam_info['name']
				);
			}
		// }

		// Nouveau code pour obternir la liste des exams
		$exams_to_select = $this->model_catalog_exam->getExams();

		$data['exams_to_select'] = array();
		foreach ($exams_to_select as $exam) {
			if ($exam['parent_id'] != 0) {
				$data['exams_to_select'][] = array(
					'exam_id' => $exam['exam_id'],
					'name'    => $exam['name']
				);	
			}
		}


		// Filters
		$this->load->model('catalog/filter');

		if (isset($this->request->post['assessment_filter'])) {
			$filters = $this->request->post['assessment_filter'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$filters = $this->model_catalog_assessment->getAssessmentFilters($this->request->get['assessment_id']);
		} else {
			$filters = array();
		}

		$data['assessment_filters'] = array();

		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);

			if ($filter_info) {
				$data['assessment_filters'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
		}

		// Attributes
		$this->load->model('catalog/attribute');

		if (isset($this->request->post['assessment_attribute'])) {
			$assessment_attributes = $this->request->post['assessment_attribute'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$assessment_attributes = $this->model_catalog_assessment->getAssessmentAttributes($this->request->get['assessment_id']);
		} else {
			$assessment_attributes = array();
		}

		$data['assessment_attributes'] = array();

		foreach ($assessment_attributes as $assessment_attribute) {
			$attribute_info = $this->model_catalog_attribute->getAttribute($assessment_attribute['attribute_id']);

			if ($attribute_info) {
				$data['assessment_attributes'][] = array(
					'attribute_id'                  => $assessment_attribute['attribute_id'],
					'name'                          => $attribute_info['name'],
					'assessment_attribute_description' => $assessment_attribute['assessment_attribute_description']
				);
			}
		}

		// Options
		$this->load->model('catalog/option');

		if (isset($this->request->post['assessment_option'])) {
			$assessment_options = $this->request->post['assessment_option'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$assessment_options = $this->model_catalog_assessment->getAssessmentOptions($this->request->get['assessment_id']);
		} else {
			$assessment_options = array();
		}

		$data['assessment_options'] = array();

		foreach ($assessment_options as $assessment_option) {
			$assessment_option_value_data = array();

			if (isset($assessment_option['assessment_option_value'])) {
				foreach ($assessment_option['assessment_option_value'] as $assessment_option_value) {
					$assessment_option_value_data[] = array(
						'assessment_option_value_id' => $assessment_option_value['assessment_option_value_id'],
						'option_value_id'         => $assessment_option_value['option_value_id'],
						'quantity'                => $assessment_option_value['quantity'],
						'subtract'                => $assessment_option_value['subtract'],
						'price'                   => $assessment_option_value['price'],
						'price_prefix'            => $assessment_option_value['price_prefix'],
						'points'                  => $assessment_option_value['points'],
						'points_prefix'           => $assessment_option_value['points_prefix'],
						'weight'                  => $assessment_option_value['weight'],
						'weight_prefix'           => $assessment_option_value['weight_prefix']
					);
				}
			}

			$data['assessment_options'][] = array(
				'assessment_option_id'    => $assessment_option['assessment_option_id'],
				'assessment_option_value' => $assessment_option_value_data,
				'option_id'            => $assessment_option['option_id'],
				'name'                 => $assessment_option['name'],
				'type'                 => $assessment_option['type'],
				'value'                => isset($assessment_option['value']) ? $assessment_option['value'] : '',
				'required'             => $assessment_option['required']
			);
		}

		$data['option_values'] = array();

		foreach ($data['assessment_options'] as $assessment_option) {
			if ($assessment_option['type'] == 'select' || $assessment_option['type'] == 'radio' || $assessment_option['type'] == 'checkbox' || $assessment_option['type'] == 'image') {
				if (!isset($data['option_values'][$assessment_option['option_id']])) {
					$data['option_values'][$assessment_option['option_id']] = $this->model_catalog_option->getOptionValues($assessment_option['option_id']);
				}
			}
		}

		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		if (isset($this->request->post['assessment_discount'])) {
			$assessment_discounts = $this->request->post['assessment_discount'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$assessment_discounts = $this->model_catalog_assessment->getAssessmentDiscounts($this->request->get['assessment_id']);
		} else {
			$assessment_discounts = array();
		}

		$data['assessment_discounts'] = array();

		foreach ($assessment_discounts as $assessment_discount) {
			$data['assessment_discounts'][] = array(
				'customer_group_id' => $assessment_discount['customer_group_id'],
				'quantity'          => $assessment_discount['quantity'],
				'priority'          => $assessment_discount['priority'],
				'price'             => $assessment_discount['price'],
				'date_start'        => ($assessment_discount['date_start'] != '0000-00-00') ? $assessment_discount['date_start'] : '',
				'date_end'          => ($assessment_discount['date_end'] != '0000-00-00') ? $assessment_discount['date_end'] : ''
			);
		}

		if (isset($this->request->post['assessment_special'])) {
			$assessment_specials = $this->request->post['assessment_special'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$assessment_specials = $this->model_catalog_assessment->getAssessmentSpecials($this->request->get['assessment_id']);
		} else {
			$assessment_specials = array();
		}

		$data['assessment_specials'] = array();

		foreach ($assessment_specials as $assessment_special) {
			$data['assessment_specials'][] = array(
				'customer_group_id' => $assessment_special['customer_group_id'],
				'priority'          => $assessment_special['priority'],
				'price'             => $assessment_special['price'],
				'date_start'        => ($assessment_special['date_start'] != '0000-00-00') ? $assessment_special['date_start'] : '',
				'date_end'          => ($assessment_special['date_end'] != '0000-00-00') ? $assessment_special['date_end'] :  ''
			);
		}

		// Image
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($assessment_info)) {
			$data['image'] = $assessment_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($assessment_info) && is_file(DIR_IMAGE . $assessment_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($assessment_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		// Images
		if (isset($this->request->post['assessment_image'])) {
			$assessment_images = $this->request->post['assessment_image'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$assessment_images = $this->model_catalog_assessment->getAssessmentImages($this->request->get['assessment_id']);
		} else {
			$assessment_images = array();
		}

		$data['assessment_images'] = array();

		foreach ($assessment_images as $assessment_image) {
			if (is_file(DIR_IMAGE . $assessment_image['image'])) {
				$image = $assessment_image['image'];
				$thumb = $assessment_image['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

			$data['assessment_images'][] = array(
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
				'sort_order' => $assessment_image['sort_order']
			);
		}

		// Downloads
		$this->load->model('catalog/download');

		if (isset($this->request->post['assessment_download'])) {
			$assessment_downloads = $this->request->post['assessment_download'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$assessment_downloads = $this->model_catalog_assessment->getAssessmentDownloads($this->request->get['assessment_id']);
		} else {
			$assessment_downloads = array();
		}

		$data['assessment_downloads'] = array();

		foreach ($assessment_downloads as $download_id) {
			$download_info = $this->model_catalog_download->getDownload($download_id);

			if ($download_info) {
				$data['assessment_downloads'][] = array(
					'download_id' => $download_info['download_id'],
					'name'        => $download_info['name']
				);
			}
		}

		if (isset($this->request->post['assessment_related'])) {
			$assessments = $this->request->post['assessment_related'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$assessments = $this->model_catalog_assessment->getAssessmentRelated($this->request->get['assessment_id']);
		} else {
			$assessments = array();
		}

		$data['assessment_relateds'] = array();

		foreach ($assessments as $assessment_id) {
			$related_info = $this->model_catalog_assessment->getAssessment($assessment_id);

			if ($related_info) {
				$data['assessment_relateds'][] = array(
					'assessment_id' => $related_info['assessment_id'],
					'name'       => $related_info['name']
				);
			}
		}

		if (isset($this->request->post['points'])) {
			$data['points'] = $this->request->post['points'];
		} elseif (!empty($assessment_info)) {
			$data['points'] = $assessment_info['points'];
		} else {
			$data['points'] = '';
		}

		if (isset($this->request->post['assessment_reward'])) {
			$data['assessment_reward'] = $this->request->post['assessment_reward'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$data['assessment_reward'] = $this->model_catalog_assessment->getAssessmentRewards($this->request->get['assessment_id']);
		} else {
			$data['assessment_reward'] = array();
		}

		if (isset($this->request->post['assessment_seo_url'])) {
			$data['assessment_seo_url'] = $this->request->post['assessment_seo_url'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$data['assessment_seo_url'] = $this->model_catalog_assessment->getAssessmentSeoUrls($this->request->get['assessment_id']);
		} else {
			$data['assessment_seo_url'] = array();
		}

		if (isset($this->request->post['assessment_layout'])) {
			$data['assessment_layout'] = $this->request->post['assessment_layout'];
		} elseif (isset($this->request->get['assessment_id'])) {
			$data['assessment_layout'] = $this->model_catalog_assessment->getAssessmentLayouts($this->request->get['assessment_id']);
		} else {
			$data['assessment_layout'] = array();
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/assessment_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/assessment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['assessment_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}

			if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}

			// Nouveau code pour rendre date obligatoire
			if ( ($value['date'] < date("Y-m-d")) || (utf8_strlen($value['date']) < 1) || (utf8_strlen($value['date']) > 16) ) {
				$this->error['date'][$language_id] = $this->language->get('error_date');
			}
			// Fin nouveau code

		}

		// Nouveau code pour rendre exam obligatoire
		if ( (utf8_strlen($this->request->post['exam']) < 1) || ($this->request->post['exam'] == 0 ) ) {
			$this->error['exam'] = $this->language->get('error_exam');
		}
		// Fin nouveau code

		if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
			$this->error['model'] = $this->language->get('error_model');
		}

		if ($this->request->post['assessment_seo_url']) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['assessment_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['assessment_id']) || (($seo_url['query'] != 'assessment_id=' . $this->request->get['assessment_id'])))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');

								break;
							}
						}
					}
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/assessment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateCopy() {
		if (!$this->user->hasPermission('modify', 'catalog/assessment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model']) || isset($this->request->get['filter_exam'])) {
			$this->load->model('catalog/assessment');
			$this->load->model('catalog/option');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}

			// Nouveau code pour autocomplete filter exam
			if (isset($this->request->get['filter_exam'])) {
				$filter_exam = $this->request->get['filter_exam'];
			} else {
				$filter_exam = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = (int)$this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter_exam'  => $filter_exam,
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_catalog_assessment->getAssessments($filter_data);

			foreach ($results as $result) {
				$option_data = array();

				$assessment_options = $this->model_catalog_assessment->getAssessmentOptions($result['assessment_id']);

				foreach ($assessment_options as $assessment_option) {
					$option_info = $this->model_catalog_option->getOption($assessment_option['option_id']);

					if ($option_info) {
						$assessment_option_value_data = array();

						foreach ($assessment_option['assessment_option_value'] as $assessment_option_value) {
							$option_value_info = $this->model_catalog_option->getOptionValue($assessment_option_value['option_value_id']);

							if ($option_value_info) {
								$assessment_option_value_data[] = array(
									'assessment_option_value_id' => $assessment_option_value['assessment_option_value_id'],
									'option_value_id'         => $assessment_option_value['option_value_id'],
									'name'                    => $option_value_info['name'],
									'price'                   => (float)$assessment_option_value['price'] ? $this->currency->format($assessment_option_value['price'], $this->config->get('config_currency')) : false,
									'price_prefix'            => $assessment_option_value['price_prefix']
								);
							}
						}

						$option_data[] = array(
							'assessment_option_id'    => $assessment_option['assessment_option_id'],
							'assessment_option_value' => $assessment_option_value_data,
							'option_id'            => $assessment_option['option_id'],
							'name'                 => $option_info['name'],
							'type'                 => $option_info['type'],
							'value'                => $assessment_option['value'],
							'required'             => $assessment_option['required']
						);
					}
				}

				$json[] = array(
					'assessment_id' => $result['assessment_id'],
					// Nouveau code pour autocomplete le filtre exam
					'exam' => strip_tags(html_entity_decode($result['exam_name'], ENT_QUOTES, 'UTF-8')),
					'name'       => strip_tags(html_entity_decode($result['assessment_name'], ENT_QUOTES, 'UTF-8')),
					'model'      => $result['model'],
					'option'     => $option_data,
					'price'      => $result['price']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}

