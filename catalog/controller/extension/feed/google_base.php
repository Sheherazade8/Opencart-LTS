<?php
class ControllerExtensionFeedGoogleBase extends Controller {
	public function index() {
		if ($this->config->get('feed_google_base_status')) {
			$output  = '<?xml version="1.0" encoding="UTF-8" ?>';
			$output .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
			$output .= '  <channel>';
			$output .= '  <title>' . $this->config->get('config_name') . '</title>';
			$output .= '  <description>' . $this->config->get('config_price') . '</description>';
			$output .= '  <link>' . $this->config->get('config_url') . '</link>';

			$this->load->model('extension/feed/google_base');
			$this->load->model('catalog/exam');
			$this->load->model('catalog/assessment');

			$this->load->model('tool/image');

			$assessment_data = array();

			$google_base_exams = $this->model_extension_feed_google_base->getExams();

			foreach ($google_base_exams as $google_base_exam) {
				$filter_data = array(
					'filter_exam_id' => $google_base_exam['exam_id'],
					'filter_filter'      => false
				);

				$assessments = $this->model_catalog_assessment->getAssessments($filter_data);

				foreach ($assessments as $assessment) {
					if (!in_array($assessment['assessment_id'], $assessment_data) && $assessment['description']) {
						
						$assessment_data[] = $assessment['assessment_id'];
						
						$output .= '<item>';
						$output .= '<title><![CDATA[' . $assessment['name'] . ']]></title>';
						$output .= '<link>' . $this->url->link('assessment/assessment', 'assessment_id=' . $assessment['assessment_id']) . '</link>';
						$output .= '<description><![CDATA[' . strip_tags(html_entity_decode($assessment['description'], ENT_QUOTES, 'UTF-8')) . ']]></description>';
						$output .= '<g:brand><![CDATA[' . html_entity_decode($assessment['manufacturer'], ENT_QUOTES, 'UTF-8') . ']]></g:brand>';
						$output .= '<g:condition>new</g:condition>';
						$output .= '<g:id>' . $assessment['assessment_id'] . '</g:id>';

						if ($assessment['image']) {
							$output .= '  <g:image_link>' . $this->model_tool_image->resize($assessment['image'], 500, 500) . '</g:image_link>';
						} else {
							$output .= '  <g:image_link></g:image_link>';
						}

						$output .= '  <g:model_number>' . $assessment['model'] . '</g:model_number>';

						if ($assessment['mpn']) {
							$output .= '  <g:mpn><![CDATA[' . $assessment['mpn'] . ']]></g:mpn>' ;
						} else {
							$output .= '  <g:identifier_exists>false</g:identifier_exists>';
						}

						if ($assessment['upc']) {
							$output .= '  <g:upc>' . $assessment['upc'] . '</g:upc>';
						}

						if ($assessment['ean']) {
							$output .= '  <g:ean>' . $assessment['ean'] . '</g:ean>';
						}

						$currencies = array(
							'USD',
							'EUR',
							'GBP'
						);

						if (in_array($this->session->data['currency'], $currencies)) {
							$currency_code = $this->session->data['currency'];
							$currency_value = $this->currency->getValue($this->session->data['currency']);
						} else {
							$currency_code = 'USD';
							$currency_value = $this->currency->getValue('USD');
						}

						if ((float)$assessment['special']) {
							$output .= '  <g:price>' .  $this->currency->format($this->tax->calculate($assessment['special'], $assessment['tax_class_id']), $currency_code, $currency_value, false) . '</g:price>';
						} else {
							$output .= '  <g:price>' . $this->currency->format($this->tax->calculate($assessment['price'], $assessment['tax_class_id']), $currency_code, $currency_value, false) . '</g:price>';
						}

						$output .= '  <g:google_assessment_exam>' . $google_base_exam['google_base_exam_id'] . '</g:google_assessment_exam>';

						$exams = $this->model_catalog_assessment->getExams($assessment['assessment_id']);

						foreach ($exams as $exam) {
							$path = $this->getPath($exam['exam_id']);

							if ($path) {
								$string = '';

								foreach (explode('_', $path) as $path_id) {
									$exam_info = $this->model_catalog_exam->getExam($path_id);

									if ($exam_info) {
										if (!$string) {
											$string = $exam_info['name'];
										} else {
											$string .= ' &gt; ' . $exam_info['name'];
										}
									}
								}

								$output .= '<g:assessment_type><![CDATA[' . $string . ']]></g:assessment_type>';
							}
						}

						$output .= '  <g:quantity>' . $assessment['quantity'] . '</g:quantity>';
						$output .= '  <g:weight>' . $this->weight->format($assessment['weight'], $assessment['weight_class_id']) . '</g:weight>';
						$output .= '  <g:availability><![CDATA[' . ($assessment['quantity'] ? 'in stock' : 'out of stock') . ']]></g:availability>';
						$output .= '</item>';
					}
				}
			}

			$output .= '  </channel>';
			$output .= '</rss>';

			$this->response->addHeader('Content-Type: application/rss+xml');
			$this->response->setOutput($output);
		}
	}

	protected function getPath($parent_id, $current_path = '') {
		$exam_info = $this->model_catalog_exam->getExam($parent_id);

		if ($exam_info) {
			if (!$current_path) {
				$new_path = $exam_info['exam_id'];
			} else {
				$new_path = $exam_info['exam_id'] . '_' . $current_path;
			}

			$path = $this->getPath($exam_info['parent_id'], $new_path);

			if ($path) {
				return $path;
			} else {
				return $new_path;
			}
		}
	}
}
