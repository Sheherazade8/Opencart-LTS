<?php
class ControllerCatalogExam extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/exam');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/exam');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/exam');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/exam');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_exam->addExam($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/exam', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/exam');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/exam');
		$this->load->model('catalog/assessment');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_exam->editExam($this->request->get['exam_id'], $this->request->post);
			// Nouveau code pour updateprices

			$this->model_catalog_assessment->updatePrices($this->request->get['exam_id'], $this->request->post);


			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/exam', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/exam');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/exam');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $exam_id) {
				$this->model_catalog_exam->deleteExam($exam_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/exam', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function repair() {
		$this->load->language('catalog/exam');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/exam');

		if ($this->validateRepair()) {
			$this->model_catalog_exam->repairExams();

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/exam', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
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
			'href' => $this->url->link('catalog/exam', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/exam/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/exam/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['repair'] = $this->url->link('catalog/exam/repair', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['exams'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$exam_total = $this->model_catalog_exam->getTotalExams();

		$results = $this->model_catalog_exam->getExams($filter_data);

		foreach ($results as $result) {
			$data['exams'][] = array(
				'exam_id' => $result['exam_id'],
				'name'        => $result['name'],
				'sort_order'  => $result['sort_order'],
				'price'  => $result['price'],
				'edit'        => $this->url->link('catalog/exam/edit', 'user_token=' . $this->session->data['user_token'] . '&exam_id=' . $result['exam_id'] . $url, true),
				'delete'      => $this->url->link('catalog/exam/delete', 'user_token=' . $this->session->data['user_token'] . '&exam_id=' . $result['exam_id'] . $url, true)
			);
		}

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

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('catalog/exam', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_sort_order'] = $this->url->link('catalog/exam', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);
		
		// Nouveau code pour afficher price au lieu de sort_order
		$data['sort_price'] = $this->url->link('catalog/exam', 'user_token=' . $this->session->data['user_token'] . '&sort=price' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $exam_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/exam', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($exam_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($exam_total - $this->config->get('config_limit_admin'))) ? $exam_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $exam_total, ceil($exam_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/exam_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['exam_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

		// // Nouveau code pour rendre price obligatoire

		// if (isset($this->error['price'])) {
		// 	$data['error_price'] = $this->error['price'];
		// } else {
		// 	$data['error_price'] = array();
		// }
		// // Fin nouveau code

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		if (isset($this->error['parent'])) {
			$data['error_parent'] = $this->error['parent'];
		} else {
			$data['error_parent'] = '';
		}

		$url = '';

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
			'href' => $this->url->link('catalog/exam', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['exam_id'])) {
			$data['action'] = $this->url->link('catalog/exam/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/exam/edit', 'user_token=' . $this->session->data['user_token'] . '&exam_id=' . $this->request->get['exam_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/exam', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['exam_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$exam_info = $this->model_catalog_exam->getExam($this->request->get['exam_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($exam_info)) {
			$data['name'] = $exam_info['name'];
		} else {
			$data['name'] = '';
		}


		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['exam_description'])) {
			$data['exam_description'] = $this->request->post['exam_description'];
		} elseif (isset($this->request->get['exam_id'])) {
			$data['exam_description'] = $this->model_catalog_exam->getExamDescription($this->request->get['exam_id']);
		} else {
			$data['exam_description'] = array();
		}

		if (isset($this->request->post['meta_title'])) {
			$data['meta_title'] = $this->request->post['meta_title'];
		} elseif (!empty($exam_info)) {
			$data['meta_title'] = $exam_info['meta_title'];
		} else {
			$data['meta_title'] = '';
		}

		if (isset($this->request->post['price'])) {
			$data['price'] = $this->request->post['price'];
		} elseif (!empty($exam_info)) {
			$data['price'] = $exam_info['price'];
		} else {
			$data['price'] = '';
		}

		if (isset($this->request->post['meta_keyword'])) {
			$data['meta_keyword'] = $this->request->post['meta_keyword'];
		} elseif (!empty($exam_info)) {
			$data['meta_keyword'] = $exam_info['meta_keyword'];
		} else {
			$data['meta_keyword'] = '';
		}



		if (isset($this->request->post['path'])) {
			$data['path'] = $this->request->post['path'];
		} elseif (!empty($exam_info)) {
			$data['path'] = $exam_info['path'];
		} else {
			$data['path'] = '';
		}

		if (isset($this->request->post['parent_id'])) {
			$data['parent_id'] = $this->request->post['parent_id'];
		} elseif (!empty($exam_info)) {
			$data['parent_id'] = $exam_info['parent_id'];
		} else {
			$data['parent_id'] = 0;
		}

		$this->load->model('catalog/filter');

		if (isset($this->request->post['exam_filter'])) {
			$filters = $this->request->post['exam_filter'];
		} elseif (isset($this->request->get['exam_id'])) {
			$filters = $this->model_catalog_exam->getExamFilters($this->request->get['exam_id']);
		} else {
			$filters = array();
		}

		$data['exam_filters'] = array();

		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);

			if ($filter_info) {
				$data['exam_filters'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
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

		if (isset($this->request->post['exam_store'])) {
			$data['exam_store'] = $this->request->post['exam_store'];
		} elseif (isset($this->request->get['exam_id'])) {
			$data['exam_store'] = $this->model_catalog_exam->getExamStores($this->request->get['exam_id']);
		} else {
			$data['exam_store'] = array(0);
		}

		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($exam_info)) {
			$data['image'] = $exam_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($exam_info) && is_file(DIR_IMAGE . $exam_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($exam_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['top'])) {
			$data['top'] = $this->request->post['top'];
		} elseif (!empty($exam_info)) {
			$data['top'] = $exam_info['top'];
		} else {
			$data['top'] = 0;
		}

		if (isset($this->request->post['column'])) {
			$data['column'] = $this->request->post['column'];
		} elseif (!empty($exam_info)) {
			$data['column'] = $exam_info['column'];
		} else {
			$data['column'] = 1;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($exam_info)) {
			$data['sort_order'] = $exam_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($exam_info)) {
			$data['status'] = $exam_info['status'];
		} else {
			$data['status'] = true;
		}

		// Nouveau code pour ajouter options à Exam
		// Attention : l'emplacement compte beaucoup ! $data doit respecter l'ordre d'affichage

		// Options
		$this->load->model('catalog/option');
		$this->load->model('catalog/exam');

		if (isset($this->request->post['exam_option'])) {
			$exam_options = $this->request->post['exam_option'];
		} elseif (isset($this->request->get['exam_id'])) {
			$exam_options = $this->model_catalog_exam->getExamOptions($this->request->get['exam_id']);
		} else {
			$exam_options = array();
		}

		$data['exam_options'] = array();

		foreach ($exam_options as $exam_option) {
			$exam_option_value_data = array();

			if (isset($exam_option['exam_option_value'])) {
				foreach ($exam_option['exam_option_value'] as $exam_option_value) {
					$exam_option_value_data[] = array(
						'exam_option_value_id' => $exam_option_value['exam_option_value_id'],
						'option_value_id'         => $exam_option_value['option_value_id'],
						'subtract'                => $exam_option_value['subtract'],
						'price'                   => $exam_option_value['price'],
						'price_prefix'            => $exam_option_value['price_prefix'],
						'points'                  => $exam_option_value['points'],
						'points_prefix'           => $exam_option_value['points_prefix']
					);
				}
			}
			
			$data['exam_options'][] = array(
				'exam_option_id'    => $exam_option['exam_option_id'],
				'exam_option_value' => $exam_option_value_data,
				'option_id'            => $exam_option['option_id'],
				'name'                 => $exam_option['name'],
				'type'                 => $exam_option['type'],
				'value'                => isset($exam_option['value']) ? $exam_option['value'] : '',
				'required'             => $exam_option['required']
			);
		}

		$data['option_values'] = array();

		foreach ($data['exam_options'] as $exam_option) {
			if ($exam_option['type'] == 'select' || $exam_option['type'] == 'radio' || $exam_option['type'] == 'checkbox' || $exam_option['type'] == 'image') {
				if (!isset($data['option_values'][$exam_option['option_id']])) {
					$data['option_values'][$exam_option['option_id']] = $this->model_catalog_option->getOptionValues($exam_option['option_id']);
				}
			}
		}

	
		// Fin nouveau code


		if (isset($this->request->post['exam_seo_url'])) {
			$data['exam_seo_url'] = $this->request->post['exam_seo_url'];
		} elseif (isset($this->request->get['exam_id'])) {
			$data['exam_seo_url'] = $this->model_catalog_exam->getExamSeoUrls($this->request->get['exam_id']);
		} else {
			$data['exam_seo_url'] = array();
		}

		if (isset($this->request->post['exam_layout'])) {
			$data['exam_layout'] = $this->request->post['exam_layout'];
		} elseif (isset($this->request->get['exam_id'])) {
			$data['exam_layout'] = $this->model_catalog_exam->getExamLayouts($this->request->get['exam_id']);
		} else {
			$data['exam_layout'] = array();
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/exam_form', $data));

	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/exam')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

			if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 255)) {
				$this->error['name'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['meta_title']) < 1) || (utf8_strlen($this->request->post['meta_title']) > 255)) {
				$this->error['meta_title'] = $this->language->get('error_meta_title');
			}

			// // Nouveau code pour rendre price obligatoire
			// if (empty($value['price'])) {
			// 	$this->error['price'][$language_id] = $this->language->get('error_price');
			// }
			// //Fin nouveau code

		

		if (isset($this->request->get['exam_id']) && $this->request->post['parent_id']) {
			$results = $this->model_catalog_exam->getExamPath($this->request->post['parent_id']);

			foreach ($results as $result) {
				if ($result['path_id'] == $this->request->get['exam_id']) {
					$this->error['parent'] = $this->language->get('error_parent');

					break;
				}
			}
		}

		if ($this->request->post['exam_seo_url']) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['exam_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['exam_id']) || ($seo_url['query'] != 'exam_id=' . $this->request->get['exam_id']))) {
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
		if (!$this->user->hasPermission('modify', 'catalog/exam')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateRepair() {
		if (!$this->user->hasPermission('modify', 'catalog/exam')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/exam');
			$this->load->model('catalog/option');
		
			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_catalog_exam->getExams($filter_data);

			foreach ($results as $result) {
			// Nouveau code pour ajouter options à Exam

				$option_data = array();

				$exam_options = $this->model_catalog_exam->getExamOptions($result['exam_id']);

				foreach ($exam_options as $exam_option) {
					$option_info = $this->model_catalog_option->getOption($exam_option['option_id']);

					if ($option_info) {
						$exam_option_value_data = array();

						foreach ($exam_option['exam_option_value'] as $exam_option_value) {
							$option_value_info = $this->model_catalog_option->getOptionValue($exam_option_value['option_value_id']);

							if ($option_value_info) {
								$exam_option_value_data[] = array(
									'exam_option_value_id' => $exam_option_value['exam_option_value_id'],
									'option_value_id'         => $exam_option_value['option_value_id'],
									'name'                    => $option_value_info['name'],
									'price'                   => (float)$exam_option_value['price'] ? $this->currency->format($exam_option_value['price'], $this->config->get('config_currency')) : false,
									'price_prefix'            => $exam_option_value['price_prefix']
								);
							}
						}

						$option_data[] = array(
							'exam_option_id'    => $exam_option['exam_option_id'],
							'exam_option_value' => $exam_option_value_data,
							'option_id'            => $exam_option['option_id'],
							'name'                 => $option_info['name'],
							'type'                 => $option_info['type'],
							'value'                => $exam_option['value'],
							'required'             => $exam_option['required']
						);
					}
				}
				// Fin Nouveau code
				$json[] = array(
					'exam_id' => $result['exam_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'option'     => $option_data
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
