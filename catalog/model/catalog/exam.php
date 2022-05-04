<?php
class ModelCatalogExam extends Model {
	public function getExam($exam_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "exam e LEFT JOIN " . DB_PREFIX . "exam_description ed ON (e.exam_id = ed.exam_id) LEFT JOIN " . DB_PREFIX . "exam_to_store e2s ON (e.exam_id = e2s.exam_id) WHERE e.exam_id = '" . (int)$exam_id . "' AND ed.language_id = '" . (int)$this->config->get('config_language_id') . "' AND e2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND e.status = '1'");

		return $query->row;
	}

	public function getExams($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "exam e LEFT JOIN " . DB_PREFIX . "exam_description ed ON (e.exam_id = ed.exam_id) LEFT JOIN " . DB_PREFIX . "exam_to_store e2s ON (e.exam_id = e2s.exam_id) WHERE e.parent_id = '" . (int)$parent_id . "' AND ed.language_id = '" . (int)$this->config->get('config_language_id') . "' AND e2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND e.status = '1' ORDER BY e.sort_order, LCASE(e.name)");

		return $query->rows;
	}

	public function getExamFilters($exam_id) {
		$implode = array();

		$query = $this->db->query("SELECT filter_id FROM " . DB_PREFIX . "exam_filter WHERE exam_id = '" . (int)$exam_id . "'");

		foreach ($query->rows as $result) {
			$implode[] = (int)$result['filter_id'];
		}

		$filter_group_data = array();

		if ($implode) {
			$filter_group_query = $this->db->query("SELECT DISTINCT f.filter_group_id, fgd.name, fg.sort_order FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_group fg ON (f.filter_group_id = fg.filter_group_id) LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fg.filter_group_id = fgd.filter_group_id) WHERE f.filter_id IN (" . implode(',', $implode) . ") AND fgd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY f.filter_group_id ORDER BY fg.sort_order, LCASE(fgd.name)");

			foreach ($filter_group_query->rows as $filter_group) {
				$filter_data = array();

				$filter_query = $this->db->query("SELECT DISTINCT f.filter_id, fd.name FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) WHERE f.filter_id IN (" . implode(',', $implode) . ") AND f.filter_group_id = '" . (int)$filter_group['filter_group_id'] . "' AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY f.sort_order, LCASE(fd.name)");

				foreach ($filter_query->rows as $filter) {
					$filter_data[] = array(
						'filter_id' => $filter['filter_id'],
						'name'      => $filter['name']
					);
				}

				if ($filter_data) {
					$filter_group_data[] = array(
						'filter_group_id' => $filter_group['filter_group_id'],
						'name'            => $filter_group['name'],
						'filter'          => $filter_data
					);
				}
			}
		}

		return $filter_group_data;
	}

	public function getExamLayoutId($exam_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "exam_to_layout WHERE exam_id = '" . (int)$exam_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}

	public function getExamOptions($exam_id) {
		$exam_option_data = array();

		$exam_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "exam_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.exam_id = '" . (int)$exam_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

		foreach ($exam_option_query->rows as $exam_option) {
			$exam_option_value_data = array();

			$exam_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "exam_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.exam_id = '" . (int)$exam_id . "' AND pov.exam_option_id = '" . (int)$exam_option['exam_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

			foreach ($exam_option_value_query->rows as $exam_option_value) {
				$exam_option_value_data[] = array(
					'exam_option_value_id' => $exam_option_value['exam_option_value_id'],
					'option_value_id'         => $exam_option_value['option_value_id'],
					'name'                    => $exam_option_value['name'],
					'image'                   => $exam_option_value['image'],
					'subtract'                => $exam_option_value['subtract'],
					'price'                   => $exam_option_value['price'],
					'price_prefix'            => $exam_option_value['price_prefix']
				);
			}

			$exam_option_data[] = array(
				'exam_option_id'    => $exam_option['exam_option_id'],
				'exam_option_value' => $exam_option_value_data,
				'option_id'            => $exam_option['option_id'],
				'name'                 => $exam_option['name'],
				'type'                 => $exam_option['type'],
				'value'                => $exam_option['value'],
				'required'             => $exam_option['required']
			);
		}

		return $exam_option_data;
	}

	public function getTotalExamsByExamId($parent_id = 0) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "exam e LEFT JOIN " . DB_PREFIX . "exam_to_store e2s ON (e.exam_id = e2s.exam_id) WHERE e.parent_id = '" . (int)$parent_id . "' AND e2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND e.status = '1'");

		return $query->row['total'];
	}
}