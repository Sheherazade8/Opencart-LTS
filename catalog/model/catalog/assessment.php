<?php
class ModelCatalogAssessment extends Model {
	public function updateViewed($assessment_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "assessment SET viewed = (viewed + 1) WHERE assessment_id = '" . (int)$assessment_id . "'");
	}

	public function getAssessment($assessment_id) {
		// Nouveau code pour obtenir exam
		$query = $this->db->query("SELECT DISTINCT *, a.name AS name, a.image, m.name AS manufacturer, (SELECT e.name FROM " . DB_PREFIX . "assessment_to_exam a2e LEFT JOIN " . DB_PREFIX . "exam e ON (a2e.exam_id = e.exam_id) WHERE a2e.assessment_id = '" . (int)$assessment_id . "' ) AS exam, (SELECT price FROM " . DB_PREFIX . "assessment_discount ad2 WHERE ad2.assessment_id = a.assessment_id AND ad2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ad2.quantity = '1' AND ((ad2.date_start = '0000-00-00' OR ad2.date_start < NOW()) AND (ad2.date_end = '0000-00-00' OR ad2.date_end > NOW())) ORDER BY ad2.priority ASC, ad2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "assessment_special asp WHERE asp.assessment_id = a.assessment_id AND asp.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((asp.date_start = '0000-00-00' OR asp.date_start < NOW()) AND (asp.date_end = '0000-00-00' OR asp.date_end > NOW())) ORDER BY asp.priority ASC, asp.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "assessment_reward ar WHERE ar.assessment_id = a.assessment_id AND ar.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = a.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.assessment_id = a.assessment_id AND r1.status = '1' GROUP BY r1.assessment_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.assessment_id = a.assessment_id AND r2.status = '1' GROUP BY r2.assessment_id) AS reviews, a.sort_order FROM " . DB_PREFIX . "assessment a LEFT JOIN " . DB_PREFIX . "assessment_description ad ON (a.assessment_id = ad.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_to_store a2s ON (a.assessment_id = a2s.assessment_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (a.manufacturer_id = m.manufacturer_id) WHERE a.assessment_id = '" . (int)$assessment_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND a.status = '1' AND a.date_available <= NOW() AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
		
		if ($query->num_rows) {
			return array(
				'assessment_id'       => $query->row['assessment_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'meta_title'       => $query->row['meta_title'],
				// Nouveau code
				'date' => $query->row['date'],
				'exam' => $query->row['exam'],

				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'model'            => $query->row['model'],
				'location'         => $query->row['location'],
				'quantity'         => $query->row['quantity'],
				'stock_status'     => $query->row['stock_status'],
				'image'            => $query->row['image'],
				'manufacturer_id'  => $query->row['manufacturer_id'],
				'manufacturer'     => $query->row['manufacturer'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],
				'reward'           => $query->row['reward'],
				'points'           => $query->row['points'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'date_available'   => $query->row['date_available'],
				'subtract'         => $query->row['subtract'],
				'rating'           => round($query->row['rating']),
				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
				'minimum'          => $query->row['minimum'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed']
			);
		} else {
			return false;
		}
	}

	public function getAssessments($data = array()) {
		$sql = "SELECT a.assessment_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.assessment_id = a.assessment_id AND r1.status = '1' GROUP BY r1.assessment_id) AS rating, (SELECT price FROM " . DB_PREFIX . "assessment_discount ad2 WHERE ad2.assessment_id = a.assessment_id AND ad2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ad2.quantity = '1' AND ((ad2.date_start = '0000-00-00' OR ad2.date_start < NOW()) AND (ad2.date_end = '0000-00-00' OR ad2.date_end > NOW())) ORDER BY ad2.priority ASC, ad2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "assessment_special asp WHERE asp.assessment_id = a.assessment_id AND asp.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((asp.date_start = '0000-00-00' OR asp.date_start < NOW()) AND (asp.date_end = '0000-00-00' OR asp.date_end > NOW())) ORDER BY asp.priority ASC, asp.price ASC LIMIT 1) AS special";

		if (!empty($data['filter_exam_id'])) {
			if (!empty($data['filter_sub_exam'])) {
				$sql .= " FROM " . DB_PREFIX . "exam_path ep LEFT JOIN " . DB_PREFIX . "assessment_to_exam a2e ON (ep.exam_id = a2e.exam_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "assessment_to_exam a2e";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "assessment_filter af ON (a2e.assessment_id = af.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment a ON (af.assessment_id = a.assessment_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "assessment a ON (a2e.assessment_id = a.assessment_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "assessment a";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "assessment_description ad ON (a.assessment_id = ad.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_to_store a2s ON (a.assessment_id = a2s.assessment_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND a.status = '1' AND a.date_available <= NOW() AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_exam_id'])) {
			if (!empty($data['filter_sub_exam'])) {
				$sql .= " AND ap.path_id = '" . (int)$data['filter_exam_id'] . "'";
			} else {
				$sql .= " AND a2e.exam_id = '" . (int)$data['filter_exam_id'] . "'";
			}

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND af.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (!empty($data['filter_city']) ) {
			$sql .= " AND a.model LIKE '%" . $data['filter_city'] . "%'";
		}

		if (!empty($data['filter_month']) ) {
			$sql .= " AND MONTH(a.date)= '" . (int)$data['filter_month'] . "'";

		}


		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "a.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR ad.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

				foreach ($words as $word) {
					$implode[] = "a.tag LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(a.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}

		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND a.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		$sql .= " GROUP BY a.assessment_id";

		$sort_data = array(
			'a.name',
			'a.model',
			'a.quantity',
			'a.date',
			'rating',
			'a.sort_order',
			'a.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'a.name' || $data['sort'] == 'a.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} elseif ($data['sort'] == 'a.date') {
				$sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE a.date END)";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY a.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(a.name) DESC";
		} else {
			$sql .= " ASC, LCASE(a.name) ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$assessment_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$assessment_data[$result['assessment_id']] = $this->getAssessment($result['assessment_id']);
		}

		return $assessment_data;
	}

	public function getcitiesByExamId($exam_id) {
		$query = $this->db->query("SELECT DISTINCT a.model FROM " . DB_PREFIX . "assessment a LEFT JOIN " . DB_PREFIX . "assessment_to_exam a2e ON (a.assessment_id = a2e.assessment_id) WHERE a2e.exam_id = '" . (int)$exam_id . "' ORDER BY a.model ASC");

		return $query->rows;
	}

	public function getdatesByExamId($exam_id) {
		$query = $this->db->query("SELECT DISTINCT a.date FROM " . DB_PREFIX . "assessment a LEFT JOIN " . DB_PREFIX . "assessment_to_exam a2e ON (a.assessment_id = a2e.assessment_id) WHERE a2e.exam_id = '" . (int)$exam_id . "' ORDER BY a.date ASC");

		return $query->rows;
	}

	public function getAssessmentSpecials($data = array()) {
		$sql = "SELECT DISTINCT asp.assessment_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.assessment_id = asp.assessment_id AND r1.status = '1' GROUP BY r1.assessment_id) AS rating FROM " . DB_PREFIX . "assessment_special asp LEFT JOIN " . DB_PREFIX . "assessment a ON (asp.assessment_id = a.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_description ad ON (a.assessment_id = ad.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_to_store a2s ON (a.assessment_id = a2s.assessment_id) WHERE a.status = '1' AND a.date_available <= NOW() AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND asp.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((asp.date_start = '0000-00-00' OR asp.date_start < NOW()) AND (asp.date_end = '0000-00-00' OR asp.date_end > NOW())) GROUP BY asp.assessment_id";

		$sort_data = array(
			'a.name',
			'a.model',
			'a.date',
			'rating',
			'a.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'a.name' || $data['sort'] == 'a.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY a.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(a.name) DESC";
		} else {
			$sql .= " ASC, LCASE(a.name) ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$assessment_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$assessment_data[$result['assessment_id']] = $this->getAssessment($result['assessment_id']);
		}

		return $assessment_data;
	}

	public function getLatestAssessments($limit) {
		$assessment_data = $this->cache->get('assessment.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);

		if (!$assessment_data) {
			$query = $this->db->query("SELECT a.assessment_id FROM " . DB_PREFIX . "assessment a LEFT JOIN " . DB_PREFIX . "assessment_to_store a2s ON (a.assessment_id = a2s.assessment_id) WHERE a.status = '1' AND a.date_available <= NOW() AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY a.date_added DESC LIMIT " . (int)$limit);

			foreach ($query->rows as $result) {
				$assessment_data[$result['assessment_id']] = $this->getAssessment($result['assessment_id']);
			}

			$this->cache->set('assessment.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $assessment_data);
		}

		return $assessment_data;
	}

	public function getPopularAssessments($limit) {
		$assessment_data = $this->cache->get('assessment.popular.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);
	
		if (!$assessment_data) {
			$query = $this->db->query("SELECT a.assessment_id FROM " . DB_PREFIX . "assessment a LEFT JOIN " . DB_PREFIX . "assessment_to_store a2s ON (a.assessment_id = a2s.assessment_id) WHERE a.status = '1' AND a.date_available <= NOW() AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY a.viewed DESC, a.date_added DESC LIMIT " . (int)$limit);
	
			foreach ($query->rows as $result) {
				$assessment_data[$result['assessment_id']] = $this->getAssessment($result['assessment_id']);
			}
			
			$this->cache->set('assessment.popular.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $assessment_data);
		}
		
		return $assessment_data;
	}

	public function getBestSellerAssessments($limit) {
		$assessment_data = $this->cache->get('assessment.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);

		if (!$assessment_data) {
			$assessment_data = array();

			$query = $this->db->query("SELECT op.assessment_id, SUM(op.quantity) AS total FROM " . DB_PREFIX . "order_assessment op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "assessment` a ON (op.assessment_id = a.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_to_store a2s ON (a.assessment_id = a2s.assessment_id) WHERE o.order_status_id > '0' AND a.status = '1' AND a.date_available <= NOW() AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY op.assessment_id ORDER BY total DESC LIMIT " . (int)$limit);

			foreach ($query->rows as $result) {
				$assessment_data[$result['assessment_id']] = $this->getAssessment($result['assessment_id']);
			}

			$this->cache->set('assessment.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $assessment_data);
		}

		return $assessment_data;
	}

	public function getAssessmentAttributes($assessment_id) {
		$assessment_attribute_group_data = array();

		$assessment_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "assessment_attribute aa LEFT JOIN " . DB_PREFIX . "attribute a ON (aa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE aa.assessment_id = '" . (int)$assessment_id . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

		foreach ($assessment_attribute_group_query->rows as $assessment_attribute_group) {
			$assessment_attribute_data = array();

			$assessment_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, aa.text FROM " . DB_PREFIX . "assessment_attribute aa LEFT JOIN " . DB_PREFIX . "attribute a ON (aa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE aa.assessment_id = '" . (int)$assessment_id . "' AND a.attribute_group_id = '" . (int)$assessment_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND aa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");

			foreach ($assessment_attribute_query->rows as $assessment_attribute) {
				$assessment_attribute_data[] = array(
					'attribute_id' => $assessment_attribute['attribute_id'],
					'name'         => $assessment_attribute['name'],
					'text'         => $assessment_attribute['text']
				);
			}

			$assessment_attribute_group_data[] = array(
				'attribute_group_id' => $assessment_attribute_group['attribute_group_id'],
				'name'               => $assessment_attribute_group['name'],
				'attribute'          => $assessment_attribute_data
			);
		}

		return $assessment_attribute_group_data;
	}

	public function getAssessmentOptions($assessment_id) {
		// Nouveau code pour obtenir les options correspondant à l'exam

		$assessment_option_data = array();

		$exams = $this->getExams($assessment_id);
		foreach ($exams as $exam) { 
			$assessment_exam_id = $exam['exam_id'];
		}

		$assessment_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "exam_option eo LEFT JOIN `" . DB_PREFIX . "option` o ON (eo.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE eo.exam_id = '" . (int)$assessment_exam_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

		foreach ($assessment_option_query->rows as $assessment_option) {
			$assessment_option_value_data = array();

			$assessment_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "exam_option_value eov LEFT JOIN " . DB_PREFIX . "option_value ov ON (eov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE eov.exam_id = '" . (int)$assessment_exam_id . "' AND eov.exam_option_id = '" . (int)$assessment_option['exam_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

			foreach ($assessment_option_value_query->rows as $assessment_option_value) {				
				$assessment_option_value_data[] = array(
					'assessment_option_value_id' => $assessment_option_value['exam_option_value_id'],
					'option_value_id'         => $assessment_option_value['option_value_id'],
					'name'                    => $assessment_option_value['name'],
					'image'                   => $assessment_option_value['image'],
					'subtract'                => $assessment_option_value['subtract'],
					'price'                   => $assessment_option_value['price'],
					'price_prefix'            => $assessment_option_value['price_prefix']
				);
			}

			$assessment_option_data[] = array(
				'assessment_option_id'    => $assessment_option['exam_option_id'],
				'assessment_option_value' => $assessment_option_value_data,
				'option_id'            => $assessment_option['option_id'],
				'name'                 => $assessment_option['name'],
				'type'                 => $assessment_option['type'],
				'value'                => $assessment_option['value'],
				'required'             => $assessment_option['required']
			);
		}

		return $assessment_option_data;
	}

	public function getAssessmentDiscounts($assessment_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_discount WHERE assessment_id = '" . (int)$assessment_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC, priority ASC, price ASC");

		return $query->rows;
	}

	public function getAssessmentImages($assessment_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_image WHERE assessment_id = '" . (int)$assessment_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getAssessmentRelated($assessment_id) {
		$assessment_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_related af LEFT JOIN " . DB_PREFIX . "assessment a ON (af.related_id = a.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_to_store a2s ON (a.assessment_id = a2s.assessment_id) WHERE af.assessment_id = '" . (int)$assessment_id . "' AND a.status = '1' AND a.date_available <= NOW() AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$assessment_data[$result['related_id']] = $this->getAssessment($result['related_id']);
		}

		return $assessment_data;
	}

	public function getAssessmentLayoutId($assessment_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_to_layout WHERE assessment_id = '" . (int)$assessment_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}

	public function getExams($assessment_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_to_exam WHERE assessment_id = '" . (int)$assessment_id . "'");

		return $query->rows;
	}

	public function getTotalAssessments($data = array()) {
		$sql = "SELECT COUNT(DISTINCT a.assessment_id) AS total";

		if (!empty($data['filter_exam_id'])) {
			if (!empty($data['filter_sub_exam'])) {
				$sql .= " FROM " . DB_PREFIX . "exam_path ep LEFT JOIN " . DB_PREFIX . "assessment_to_exam a2e ON (ep.exam_id = a2e.exam_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "assessment_to_exam a2e";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "assessment_filter af ON (a2e.assessment_id = af.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment a ON (af.assessment_id = a.assessment_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "assessment a ON (a2e.assessment_id = a.assessment_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "assessment a";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "assessment_description ad ON (a.assessment_id = ad.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_to_store a2s ON (a.assessment_id = a2s.assessment_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND a.status = '1' AND a.date_available <= NOW() AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_exam_id'])) {
			if (!empty($data['filter_sub_exam'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_exam_id'] . "'";
			} else {
				$sql .= " AND a2e.exam_id = '" . (int)$data['filter_exam_id'] . "'";
			}

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND af.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "a.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR ad.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

				foreach ($words as $word) {
					$implode[] = "a.tag LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(a.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}

		if (!empty($data['filter_city']) ) {
			$sql .= " AND a.model LIKE '%" . $data['filter_city'] . "%'";
		}

		if (!empty($data['filter_month']) ) {
			$sql .= " AND MONTH(a.date)= '" . (int)$data['filter_month'] . "'";

		}
		
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND a.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getProfile($assessment_id, $recurring_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recurring r JOIN " . DB_PREFIX . "assessment_recurring af ON (af.recurring_id = r.recurring_id AND af.assessment_id = '" . (int)$assessment_id . "') WHERE af.recurring_id = '" . (int)$recurring_id . "' AND status = '1' AND af.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");

		return $query->row;
	}

	public function getProfiles($assessment_id) {
		$query = $this->db->query("SELECT rd.* FROM " . DB_PREFIX . "assessment_recurring af JOIN " . DB_PREFIX . "recurring_description rd ON (rd.language_id = " . (int)$this->config->get('config_language_id') . " AND rd.recurring_id = af.recurring_id) JOIN " . DB_PREFIX . "recurring r ON r.recurring_id = rd.recurring_id WHERE af.assessment_id = " . (int)$assessment_id . " AND status = '1' AND af.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getTotalAssessmentSpecials() {
		$query = $this->db->query("SELECT COUNT(DISTINCT asp.assessment_id) AS total FROM " . DB_PREFIX . "assessment_special asp LEFT JOIN " . DB_PREFIX . "assessment a ON (asp.assessment_id = a.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_to_store a2s ON (a.assessment_id = a2s.assessment_id) WHERE a.status = '1' AND a.date_available <= NOW() AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND asp.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((asp.date_start = '0000-00-00' OR asp.date_start < NOW()) AND (asp.date_end = '0000-00-00' OR asp.date_end > NOW()))");

		if (isset($query->row['total'])) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}

	public function checkAssessmentExam($assessment_id, $exam_ids) {
		
		$implode = array();

		foreach ($exam_ids as $exam_id) {
			$implode[] = (int)$exam_id;
		}
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_to_exam WHERE assessment_id = '" . (int)$assessment_id . "' AND exam_id IN(" . implode(',', $implode) . ")");
  	    return $query->row;
	}
}
