<?php

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
					'quantity'                => $exam_option_value['quantity'],
					'subtract'                => $exam_option_value['subtract'],
					'price'                   => $exam_option_value['price'],
					'price_prefix'            => $exam_option_value['price_prefix'],
					'weight'                  => $exam_option_value['weight'],
					'weight_prefix'           => $exam_option_value['weight_prefix']
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