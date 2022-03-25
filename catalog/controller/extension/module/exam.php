<?php
class ControllerExtensionModuleExam extends Controller {
	public function index() {
		$this->load->language('extension/module/exam');

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		if (isset($parts[0])) {
			$data['exam_id'] = $parts[0];
		} else {
			$data['exam_id'] = 0;
		}

		if (isset($parts[1])) {
			$data['child_id'] = $parts[1];
		} else {
			$data['child_id'] = 0;
		}

		$this->load->model('catalog/exam');

		$this->load->model('catalog/assessment');

		$data['exams'] = array();

		$exams = $this->model_catalog_exam->getExams(0);

		foreach ($exams as $exam) {
			$children_data = array();

			if ($exam['exam_id'] == $data['exam_id']) {
				$children = $this->model_catalog_exam->getExams($exam['exam_id']);

				foreach($children as $child) {
					$filter_data = array('filter_exam_id' => $child['exam_id'], 'filter_sub_exam' => true);

					$children_data[] = array(
						'exam_id' => $child['exam_id'],
						'name' => $child['name'] . ($this->config->get('config_assessment_count') ? ' (' . $this->model_catalog_assessment->getTotalAssessments($filter_data) . ')' : ''),
						'href' => $this->url->link('assessment/exam', 'path=' . $exam['exam_id'] . '_' . $child['exam_id'])
					);
				}
			}

			$filter_data = array(
				'filter_exam_id'  => $exam['exam_id'],
				'filter_sub_exam' => true
			);

			$data['exams'][] = array(
				'exam_id' => $exam['exam_id'],
				'name'        => $exam['name'] . ($this->config->get('config_assessment_count') ? ' (' . $this->model_catalog_assessment->getTotalAssessments($filter_data) . ')' : ''),
				'children'    => $children_data,
				'href'        => $this->url->link('assessment/exam', 'path=' . $exam['exam_id'])
			);
		}

		return $this->load->view('extension/module/exam', $data);
	}
}