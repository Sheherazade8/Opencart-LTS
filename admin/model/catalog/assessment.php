<?php
class ModelCatalogAssessment extends Model {

	// Nouveau code pour remplacer data['assessment_exam'] par data['exam'] dans addAssessment et editAssessment

	public function addAssessment($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "assessment SET model = '" . $this->db->escape($data['model']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()");

		$assessment_id = $this->db->getLastId();

		// Nouveau code pour obtenir le prix de exam dans assessment

		$this->load->model('catalog/exam');

		if ( isset($data['exam']) ) {
			// foreach ($data['exam'] as $exam_id) {				
				$exam_info = $this->model_catalog_exam->getExam($data['exam']);
				if ( $exam_info['price'] > 0 ) {
					$data['price'] = $exam_info['price'];
				}
			// }
			$this->db->query("UPDATE " . DB_PREFIX . "assessment SET price = '" . $data['price'] . "' WHERE assessment_id = '" . (int)$assessment_id . "'");
		}
		// Fin nouveau code

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "assessment SET image = '" . $this->db->escape($data['image']) . "' WHERE assessment_id = '" . (int)$assessment_id . "'");
		}

		foreach ($data['assessment_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_description SET assessment_id = '" . (int)$assessment_id . "', language_id = '" . (int)$language_id . "', center_id = '" . (int)$this->db->escape($value['center_id']) . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', date = '" . $this->db->escape($value['date']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['assessment_store'])) {
			foreach ($data['assessment_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_to_store SET assessment_id = '" . (int)$assessment_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['assessment_attribute'])) {
			foreach ($data['assessment_attribute'] as $assessment_attribute) {
				if ($assessment_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_attribute WHERE assessment_id = '" . (int)$assessment_id . "' AND attribute_id = '" . (int)$assessment_attribute['attribute_id'] . "'");

					foreach ($assessment_attribute['assessment_attribute_description'] as $language_id => $assessment_attribute_description) {
						$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_attribute WHERE assessment_id = '" . (int)$assessment_id . "' AND attribute_id = '" . (int)$assessment_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

						$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_attribute SET assessment_id = '" . (int)$assessment_id . "', attribute_id = '" . (int)$assessment_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($assessment_attribute_description['text']) . "'");
					}
				}
			}
		}

		if (isset($data['assessment_option'])) {
			foreach ($data['assessment_option'] as $assessment_option) {
				if ($assessment_option['type'] == 'select' || $assessment_option['type'] == 'radio' || $assessment_option['type'] == 'checkbox' || $assessment_option['type'] == 'image') {
					if (isset($assessment_option['assessment_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_option SET assessment_id = '" . (int)$assessment_id . "', option_id = '" . (int)$assessment_option['option_id'] . "', required = '" . (int)$assessment_option['required'] . "'");

						$assessment_option_id = $this->db->getLastId();

						foreach ($assessment_option['assessment_option_value'] as $assessment_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_option_value SET assessment_option_id = '" . (int)$assessment_option_id . "', assessment_id = '" . (int)$assessment_id . "', option_id = '" . (int)$assessment_option['option_id'] . "', option_value_id = '" . (int)$assessment_option_value['option_value_id'] . "', subtract = '" . (int)$assessment_option_value['subtract'] . "', price = '" . (float)$assessment_option_value['price'] . "', price_prefix = '" . $this->db->escape($assessment_option_value['price_prefix']) . "', points = '" . (int)$assessment_option_value['points'] . "', points_prefix = '" . $this->db->escape($assessment_option_value['points_prefix']) . "'");
						}
					}
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_option SET assessment_id = '" . (int)$assessment_id . "', option_id = '" . (int)$assessment_option['option_id'] . "', value = '" . $this->db->escape($assessment_option['value']) . "', required = '" . (int)$assessment_option['required'] . "'");
				}
			}
		}

		if (isset($data['assessment_recurring'])) {
			foreach ($data['assessment_recurring'] as $recurring) {

				$query = $this->db->query("SELECT `assessment_id` FROM `" . DB_PREFIX . "assessment_recurring` WHERE `assessment_id` = '" . (int)$assessment_id . "' AND `customer_group_id = '" . (int)$recurring['customer_group_id'] . "' AND `recurring_id` = '" . (int)$recurring['recurring_id'] . "'");

				if (!$query->num_rows) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "assessment_recurring` SET `assessment_id` = '" . (int)$assessment_id . "', customer_group_id = '" . (int)$recurring['customer_group_id'] . "', `recurring_id` = '" . (int)$recurring['recurring_id'] . "'");
				}
			}
		}
		
		if (isset($data['assessment_discount'])) {
			foreach ($data['assessment_discount'] as $assessment_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_discount SET assessment_id = '" . (int)$assessment_id . "', customer_group_id = '" . (int)$assessment_discount['customer_group_id'] . "', quantity = '" . (int)$assessment_discount['quantity'] . "', priority = '" . (int)$assessment_discount['priority'] . "', price = '" . (float)$assessment_discount['price'] . "', date_start = '" . $this->db->escape($assessment_discount['date_start']) . "', date_end = '" . $this->db->escape($assessment_discount['date_end']) . "'");
			}
		}

		if (isset($data['assessment_special'])) {
			foreach ($data['assessment_special'] as $assessment_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_special SET assessment_id = '" . (int)$assessment_id . "', customer_group_id = '" . (int)$assessment_special['customer_group_id'] . "', priority = '" . (int)$assessment_special['priority'] . "', price = '" . (float)$assessment_special['price'] . "', date_start = '" . $this->db->escape($assessment_special['date_start']) . "', date_end = '" . $this->db->escape($assessment_special['date_end']) . "'");
			}
		}

		if (isset($data['assessment_image'])) {
			foreach ($data['assessment_image'] as $assessment_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_image SET assessment_id = '" . (int)$assessment_id . "', image = '" . $this->db->escape($assessment_image['image']) . "', sort_order = '" . (int)$assessment_image['sort_order'] . "'");
			}
		}

		if (isset($data['assessment_download'])) {
			foreach ($data['assessment_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_to_download SET assessment_id = '" . (int)$assessment_id . "', download_id = '" . (int)$download_id . "'");
			}
		}

		$this->load->model('catalog/exam');

		if (isset($data['exam'])) {
			// foreach ($data['exam'] as $exam_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_to_exam SET assessment_id = '" . (int)$assessment_id . "', exam_id = '" . (int)$data['exam'] . "'");
			// }
		}

		if (isset($data['assessment_filter'])) {
			foreach ($data['assessment_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_filter SET assessment_id = '" . (int)$assessment_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		if (isset($data['assessment_related'])) {
			foreach ($data['assessment_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_related WHERE assessment_id = '" . (int)$assessment_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_related SET assessment_id = '" . (int)$assessment_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_related WHERE assessment_id = '" . (int)$related_id . "' AND related_id = '" . (int)$assessment_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_related SET assessment_id = '" . (int)$related_id . "', related_id = '" . (int)$assessment_id . "'");
			}
		}

		if (isset($data['assessment_reward'])) {
			foreach ($data['assessment_reward'] as $customer_group_id => $assessment_reward) {
				if ((int)$assessment_reward['points'] > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_reward SET assessment_id = '" . (int)$assessment_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$assessment_reward['points'] . "'");
				}
			}
		}
		
		// SEO URL
		if (isset($data['assessment_seo_url'])) {
			foreach ($data['assessment_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'assessment_id=" . (int)$assessment_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		if (isset($data['assessment_layout'])) {
			foreach ($data['assessment_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_to_layout SET assessment_id = '" . (int)$assessment_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		
		$this->cache->delete('assessment');

		return $assessment_id;
	}

	public function editAssessment($assessment_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "assessment SET model = '" . $this->db->escape($data['model']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE assessment_id = '" . (int)$assessment_id . "'");

		// Nouveau code pour obtenir le prix de exam dans assessment

		$this->load->model('catalog/exam');

		if ( isset($data['exam']) ) {
			// foreach ($data['exam'] as $exam_id) {				
				$exam_info = $this->model_catalog_exam->getExam($data['exam']);
				if ( $exam_info['price'] > 0 ) {
					$data['price'] = $exam_info['price'];
				// }
			}
			$this->db->query("UPDATE " . DB_PREFIX . "assessment SET price = '" . $data['price'] . "' WHERE assessment_id = '" . (int)$assessment_id . "'");
		}
		// Fin nouveau code

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "assessment SET image = '" . $this->db->escape($data['image']) . "' WHERE assessment_id = '" . (int)$assessment_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_description WHERE assessment_id = '" . (int)$assessment_id . "'");

		foreach ($data['assessment_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_description SET assessment_id = '" . (int)$assessment_id . "', language_id = '" . (int)$language_id . "', center_id = '" . (int)$this->db->escape($value['center_id']) . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', date = '" . $this->db->escape($value['date']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_to_store WHERE assessment_id = '" . (int)$assessment_id . "'");

		if (isset($data['assessment_store'])) {
			foreach ($data['assessment_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_to_store SET assessment_id = '" . (int)$assessment_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_attribute WHERE assessment_id = '" . (int)$assessment_id . "'");

		if (!empty($data['assessment_attribute'])) {
			foreach ($data['assessment_attribute'] as $assessment_attribute) {
				if ($assessment_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_attribute WHERE assessment_id = '" . (int)$assessment_id . "' AND attribute_id = '" . (int)$assessment_attribute['attribute_id'] . "'");

					foreach ($assessment_attribute['assessment_attribute_description'] as $language_id => $assessment_attribute_description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_attribute SET assessment_id = '" . (int)$assessment_id . "', attribute_id = '" . (int)$assessment_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($assessment_attribute_description['text']) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_option WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_option_value WHERE assessment_id = '" . (int)$assessment_id . "'");

		if (isset($data['assessment_option'])) {
			foreach ($data['assessment_option'] as $assessment_option) {
				if ($assessment_option['type'] == 'select' || $assessment_option['type'] == 'radio' || $assessment_option['type'] == 'checkbox' || $assessment_option['type'] == 'image') {
					if (isset($assessment_option['assessment_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_option SET assessment_option_id = '" . (int)$assessment_option['assessment_option_id'] . "', assessment_id = '" . (int)$assessment_id . "', option_id = '" . (int)$assessment_option['option_id'] . "', required = '" . (int)$assessment_option['required'] . "'");

						$assessment_option_id = $this->db->getLastId();

						foreach ($assessment_option['assessment_option_value'] as $assessment_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_option_value SET assessment_option_value_id = '" . (int)$assessment_option_value['assessment_option_value_id'] . "', assessment_option_id = '" . (int)$assessment_option_id . "', assessment_id = '" . (int)$assessment_id . "', option_id = '" . (int)$assessment_option['option_id'] . "', option_value_id = '" . (int)$assessment_option_value['option_value_id'] . "', subtract = '" . (int)$assessment_option_value['subtract'] . "', price = '" . (float)$assessment_option_value['price'] . "', price_prefix = '" . $this->db->escape($assessment_option_value['price_prefix']) . "', points = '" . (int)$assessment_option_value['points'] . "', points_prefix = '" . $this->db->escape($assessment_option_value['points_prefix']) . "'");
						}
					}
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_option SET assessment_option_id = '" . (int)$assessment_option['assessment_option_id'] . "', assessment_id = '" . (int)$assessment_id . "', option_id = '" . (int)$assessment_option['option_id'] . "', value = '" . $this->db->escape($assessment_option['value']) . "', required = '" . (int)$assessment_option['required'] . "'");
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "assessment_recurring` WHERE assessment_id = " . (int)$assessment_id);

		if (isset($data['assessment_recurring'])) {
			foreach ($data['assessment_recurring'] as $assessment_recurring) {
				$query = $this->db->query("SELECT `assessment_id` FROM `" . DB_PREFIX . "assessment_recurring` WHERE `assessment_id` = '" . (int)$assessment_id . "' AND `customer_group_id` = '" . (int)$assessment_recurring['customer_group_id'] . "' AND `recurring_id` = '" . (int)$assessment_recurring['recurring_id'] . "'");

				if (!$query->num_rows) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "assessment_recurring` SET `assessment_id` = '" . (int)$assessment_id . "', `customer_group_id` = '" . (int)$assessment_recurring['customer_group_id'] . "', `recurring_id` = '" . (int)$assessment_recurring['recurring_id'] . "'");
				}				
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_discount WHERE assessment_id = '" . (int)$assessment_id . "'");

		if (isset($data['assessment_discount'])) {
			foreach ($data['assessment_discount'] as $assessment_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_discount SET assessment_id = '" . (int)$assessment_id . "', customer_group_id = '" . (int)$assessment_discount['customer_group_id'] . "', quantity = '" . (int)$assessment_discount['quantity'] . "', priority = '" . (int)$assessment_discount['priority'] . "', price = '" . (float)$assessment_discount['price'] . "', date_start = '" . $this->db->escape($assessment_discount['date_start']) . "', date_end = '" . $this->db->escape($assessment_discount['date_end']) . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_special WHERE assessment_id = '" . (int)$assessment_id . "'");

		if (isset($data['assessment_special'])) {
			foreach ($data['assessment_special'] as $assessment_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_special SET assessment_id = '" . (int)$assessment_id . "', customer_group_id = '" . (int)$assessment_special['customer_group_id'] . "', priority = '" . (int)$assessment_special['priority'] . "', price = '" . (float)$assessment_special['price'] . "', date_start = '" . $this->db->escape($assessment_special['date_start']) . "', date_end = '" . $this->db->escape($assessment_special['date_end']) . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_image WHERE assessment_id = '" . (int)$assessment_id . "'");

		if (isset($data['assessment_image'])) {
			foreach ($data['assessment_image'] as $assessment_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_image SET assessment_id = '" . (int)$assessment_id . "', image = '" . $this->db->escape($assessment_image['image']) . "', sort_order = '" . (int)$assessment_image['sort_order'] . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_to_download WHERE assessment_id = '" . (int)$assessment_id . "'");

		if (isset($data['assessment_download'])) {
			foreach ($data['assessment_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_to_download SET assessment_id = '" . (int)$assessment_id . "', download_id = '" . (int)$download_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_to_exam WHERE assessment_id = '" . (int)$assessment_id . "'");

		if (isset($data['exam'])) {
			// foreach ($data['exam'] as $exam_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_to_exam SET assessment_id = '" . (int)$assessment_id . "', exam_id = '" . (int)$data['exam'] . "'");
			// }
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_filter WHERE assessment_id = '" . (int)$assessment_id . "'");

		if (isset($data['assessment_filter'])) {
			foreach ($data['assessment_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_filter SET assessment_id = '" . (int)$assessment_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_related WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_related WHERE related_id = '" . (int)$assessment_id . "'");

		if (isset($data['assessment_related'])) {
			foreach ($data['assessment_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_related WHERE assessment_id = '" . (int)$assessment_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_related SET assessment_id = '" . (int)$assessment_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_related WHERE assessment_id = '" . (int)$related_id . "' AND related_id = '" . (int)$assessment_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_related SET assessment_id = '" . (int)$related_id . "', related_id = '" . (int)$assessment_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_reward WHERE assessment_id = '" . (int)$assessment_id . "'");

		if (isset($data['assessment_reward'])) {
			foreach ($data['assessment_reward'] as $customer_group_id => $value) {
				if ((int)$value['points'] > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_reward SET assessment_id = '" . (int)$assessment_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
				}
			}
		}
		
		// SEO URL
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'assessment_id=" . (int)$assessment_id . "'");
		
		if (isset($data['assessment_seo_url'])) {
			foreach ($data['assessment_seo_url']as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'assessment_id=" . (int)$assessment_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_to_layout WHERE assessment_id = '" . (int)$assessment_id . "'");

		if (isset($data['assessment_layout'])) {
			foreach ($data['assessment_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "assessment_to_layout SET assessment_id = '" . (int)$assessment_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('assessment');
	}

	// Nouveau code pour mettre à jour le prix des assessments lorqu'on met à jour le prix de exam
	
	Public function updatePrices($exam_id, $data) {
		
		$results = $this->getAssessmentsByExamId($exam_id);
		foreach ($results as $result) {
			foreach ($data['exam_description'] as $language_id => $value) {
				$this->db->query("UPDATE " . DB_PREFIX . "assessment SET price = '" . $this->db->escape($value['price']) . "', date_modified = NOW() WHERE assessment_id = '" . (int)$result['assessment_id'] . "'");
			}
		}
	}

	Public function updateCenterInfos($center_id, $data) {
		
		// foreach ($data['language_id'] as $language_id) {
			// $this->db->query("UPDATE ( SELECT * FROM " . DB_PREFIX . "assessment a LEFT JOIN " . DB_PREFIX . "assessment_description ad ON (a.assessment_id = ad.assessment_id) WHERE ad.center_id = '" . (int)$center_id . "' AND ad.language_id = '" . (int)$language_id . "') SET ad.name = '" . $this->db->escape($data['name']) . "', ad.description = '" . $data['description'] . "', a.model = '" . $data['city'] . "', a.location = '" . $data['location'] . "', a.quantity = '" . $data['capacity'] . "', a.date_modified = NOW()" );
			
		$results = $this->getAssessmentsByCenterId($center_id);
		foreach ($results as $result) {
	
			$this->db->query("UPDATE " . DB_PREFIX . "assessment_description SET name = '" . $this->db->escape($data['name']) . "', meta_title = '" . $this->db->escape($data['name']) . "' WHERE assessment_id = '" . (int)$result['assessment_id'] . "'");
			$this->db->query("UPDATE " . DB_PREFIX . "assessment SET date_modified = NOW() WHERE assessment_id = '" . (int)$result['assessment_id'] . "'");

			if (isset($data['city'])) {
				$this->db->query("UPDATE " . DB_PREFIX . "assessment SET model = '" . $this->db->escape($data['city']) . "' WHERE assessment_id = '" . (int)$result['assessment_id'] . "'");
			}
		
			if (isset($data['location'])) {
				$this->db->query("UPDATE " . DB_PREFIX . "assessment SET location = '" . $this->db->escape($data['location']) . "' WHERE assessment_id = '" . (int)$result['assessment_id'] . "'");
			}
		
			if (isset($data['capacity'])) {
				$this->db->query("UPDATE " . DB_PREFIX . "assessment SET quantity = '" . $this->db->escape($data['capacity']) . "' WHERE assessment_id = '" . (int)$result['assessment_id'] . "'");
			}
		
			if (isset($data['center_description'])) {
				foreach ($data['center_description'] as $language_id => $value) {
					$this->db->query("UPDATE " . DB_PREFIX . "assessment_description SET description = '" . $this->db->escape($value['description']) . "', language_id = '" . (int)$language_id . "' WHERE assessment_id = '" . (int)$result['assessment_id'] . "'");
				}
			}
		
			if (isset($data['image'])) {
				$this->db->query("UPDATE " . DB_PREFIX . "assessment_image SET image = '" . $this->db->escape($data['image']) . "' WHERE assessment_id = '" . (int)$result['assessment_id'] . "'");
			}
		}
	
	}

	public function copyAssessment($assessment_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "assessment p WHERE p.assessment_id = '" . (int)$assessment_id . "'");

		if ($query->num_rows) {
			$data = $query->row;

			// $data['sku'] = '';
			// $data['upc'] = '';
			$data['viewed'] = '0';
			$data['keyword'] = '';
			$data['status'] = '0';

			$data['assessment_attribute'] = $this->getAssessmentAttributes($assessment_id);
			$data['assessment_description'] = $this->getAssessmentDescriptions($assessment_id);
			$data['assessment_discount'] = $this->getAssessmentDiscounts($assessment_id);
			$data['assessment_filter'] = $this->getAssessmentFilters($assessment_id);
			$data['assessment_image'] = $this->getAssessmentImages($assessment_id);
			$data['assessment_option'] = $this->getAssessmentOptions($assessment_id);
			$data['assessment_related'] = $this->getAssessmentRelated($assessment_id);
			$data['assessment_reward'] = $this->getAssessmentRewards($assessment_id);
			$data['assessment_special'] = $this->getAssessmentSpecials($assessment_id);
			$data['assessment_exam'] = $this->getAssessmentExam($assessment_id);
			$data['assessment_download'] = $this->getAssessmentDownloads($assessment_id);
			$data['assessment_layout'] = $this->getAssessmentLayouts($assessment_id);
			$data['assessment_store'] = $this->getAssessmentStores($assessment_id);
			$data['assessment_recurrings'] = $this->getRecurrings($assessment_id);

			$this->addAssessment($data);
		}
	}

	public function deleteAssessment($assessment_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_attribute WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_description WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_discount WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_filter WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_image WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_option WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_option_value WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_related WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_related WHERE related_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_reward WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_special WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_to_exam WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_to_download WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_to_layout WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_to_store WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "assessment_recurring WHERE assessment_id = " . (int)$assessment_id);
		$this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE assessment_id = '" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'assessment_id=" . (int)$assessment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_assessment WHERE assessment_id = '" . (int)$assessment_id . "'");

		$this->cache->delete('assessment');
	}

	public function getAssessment($assessment_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "assessment p LEFT JOIN " . DB_PREFIX . "assessment_description pd ON (p.assessment_id = pd.assessment_id) WHERE p.assessment_id = '" . (int)$assessment_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getAssessments($data = array()) {
		// Attention : Avec les modifications apportees name designe a la fois assessment_name et exam_name
		// Nouveau code
		$sql = "SELECT *, pd.name AS assessment_name, cd.name AS exam_name FROM " . DB_PREFIX . "assessment p LEFT JOIN " . DB_PREFIX . "assessment_description pd ON (p.assessment_id = pd.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_to_exam p2c ON (p.assessment_id = p2c.assessment_id) LEFT JOIN " . DB_PREFIX . "exam_description cd ON (p2c.exam_id = cd.exam_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		// Nouveau code pour ajouter un filtre exam : name LIKE "%...%"
		if (!empty($data['filter_exam'])) {
			$sql .= " AND cd.name LIKE '%" . $this->db->escape($data['filter_exam']) . "%'";
		}
		// Fin nouveau code

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (!empty($data['filter_date'])) {
			$sql .= " AND pd.date LIKE '" . $this->db->escape($data['filter_date']) . "%'";
		}

		if (isset($data['filter_quantity']) && $data['filter_quantity'] !== '') {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		$sql .= " GROUP BY p.assessment_id";

		$sort_data = array(
			'cd.name',
			'pd.name',
			'p.model',
			'pd.date',
			'p.quantity',
			'p.status',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getAssessmentsByExamId($exam_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment p LEFT JOIN " . DB_PREFIX . "assessment_description pd ON (p.assessment_id = pd.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_to_exam p2c ON (p.assessment_id = p2c.assessment_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.exam_id = '" . (int)$exam_id . "' ORDER BY pd.name ASC");

		return $query->rows;
	}

	public function getAssessmentsByCenterId($center_id) {
		$query = $this->db->query("SELECT assessment_id FROM " . DB_PREFIX . "assessment_description  WHERE center_id = '" . (int)$center_id . "'");

		return $query->rows;
	}

	public function getAssessmentDescriptions($assessment_id) {
		$assessment_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_description WHERE assessment_id = '" . (int)$assessment_id . "'");

		foreach ($query->rows as $result) {
			$assessment_description_data[$result['language_id']] = array(
				'center_id'        => $result['center_id'],
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'date'             => $result['date'],
				'meta_keyword'     => $result['meta_keyword'],
				'tag'              => $result['tag']
			);
		}

		return $assessment_description_data;
	}

	public function getAssessmentExam($assessment_id) {
		$assessment_exam_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_to_exam WHERE assessment_id = '" . (int)$assessment_id . "'");

		foreach ($query->rows as $result) {
			$assessment_exam_data[] = $result['exam_id'];
		}

		return $assessment_exam_data;
	}

	public function getAssessmentFilters($assessment_id) {
		$assessment_filter_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_filter WHERE assessment_id = '" . (int)$assessment_id . "'");

		foreach ($query->rows as $result) {
			$assessment_filter_data[] = $result['filter_id'];
		}

		return $assessment_filter_data;
	}

	public function getAssessmentAttributes($assessment_id) {
		$assessment_attribute_data = array();

		$assessment_attribute_query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "assessment_attribute WHERE assessment_id = '" . (int)$assessment_id . "' GROUP BY attribute_id");

		foreach ($assessment_attribute_query->rows as $assessment_attribute) {
			$assessment_attribute_description_data = array();

			$assessment_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_attribute WHERE assessment_id = '" . (int)$assessment_id . "' AND attribute_id = '" . (int)$assessment_attribute['attribute_id'] . "'");

			foreach ($assessment_attribute_description_query->rows as $assessment_attribute_description) {
				$assessment_attribute_description_data[$assessment_attribute_description['language_id']] = array('text' => $assessment_attribute_description['text']);
			}

			$assessment_attribute_data[] = array(
				'attribute_id'                  => $assessment_attribute['attribute_id'],
				'assessment_attribute_description' => $assessment_attribute_description_data
			);
		}

		return $assessment_attribute_data;
	}

	public function getAssessmentOptions($assessment_id) {
		$assessment_option_data = array();

		$assessment_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "assessment_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.assessment_id = '" . (int)$assessment_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order ASC");

		foreach ($assessment_option_query->rows as $assessment_option) {
			$assessment_option_value_data = array();

			$assessment_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON(pov.option_value_id = ov.option_value_id) WHERE pov.assessment_option_id = '" . (int)$assessment_option['assessment_option_id'] . "' ORDER BY ov.sort_order ASC");

			foreach ($assessment_option_value_query->rows as $assessment_option_value) {
				$assessment_option_value_data[] = array(
					'assessment_option_value_id' => $assessment_option_value['assessment_option_value_id'],
					'option_value_id'         => $assessment_option_value['option_value_id'],
					'subtract'                => $assessment_option_value['subtract'],
					'price'                   => $assessment_option_value['price'],
					'price_prefix'            => $assessment_option_value['price_prefix'],
					'points'                  => $assessment_option_value['points'],
					'points_prefix'           => $assessment_option_value['points_prefix']
				);
			}

			$assessment_option_data[] = array(
				'assessment_option_id'    => $assessment_option['assessment_option_id'],
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

	public function getAssessmentOptionValue($assessment_id, $assessment_option_value_id) {
		$query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix FROM " . DB_PREFIX . "assessment_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.assessment_id = '" . (int)$assessment_id . "' AND pov.assessment_option_value_id = '" . (int)$assessment_option_value_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getAssessmentImages($assessment_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_image WHERE assessment_id = '" . (int)$assessment_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getAssessmentDiscounts($assessment_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_discount WHERE assessment_id = '" . (int)$assessment_id . "' ORDER BY quantity, priority, price");

		return $query->rows;
	}

	public function getAssessmentSpecials($assessment_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_special WHERE assessment_id = '" . (int)$assessment_id . "' ORDER BY priority, price");

		return $query->rows;
	}

	public function getAssessmentRewards($assessment_id) {
		$assessment_reward_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_reward WHERE assessment_id = '" . (int)$assessment_id . "'");

		foreach ($query->rows as $result) {
			$assessment_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
		}

		return $assessment_reward_data;
	}

	public function getAssessmentDownloads($assessment_id) {
		$assessment_download_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_to_download WHERE assessment_id = '" . (int)$assessment_id . "'");

		foreach ($query->rows as $result) {
			$assessment_download_data[] = $result['download_id'];
		}

		return $assessment_download_data;
	}

	public function getAssessmentStores($assessment_id) {
		$assessment_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_to_store WHERE assessment_id = '" . (int)$assessment_id . "'");

		foreach ($query->rows as $result) {
			$assessment_store_data[] = $result['store_id'];
		}

		return $assessment_store_data;
	}
	
	public function getAssessmentSeoUrls($assessment_id) {
		$assessment_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'assessment_id=" . (int)$assessment_id . "'");

		foreach ($query->rows as $result) {
			$assessment_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $assessment_seo_url_data;
	}
	
	public function getAssessmentLayouts($assessment_id) {
		$assessment_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_to_layout WHERE assessment_id = '" . (int)$assessment_id . "'");

		foreach ($query->rows as $result) {
			$assessment_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $assessment_layout_data;
	}

	public function getAssessmentRelated($assessment_id) {
		$assessment_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_related WHERE assessment_id = '" . (int)$assessment_id . "'");

		foreach ($query->rows as $result) {
			$assessment_related_data[] = $result['related_id'];
		}

		return $assessment_related_data;
	}

	public function getRecurrings($assessment_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "assessment_recurring` WHERE assessment_id = '" . (int)$assessment_id . "'");

		return $query->rows;
	}

	public function getTotalAssessments($data = array()) {
		// Nouveau code
		$sql = "SELECT COUNT(DISTINCT p.assessment_id) AS total FROM " . DB_PREFIX . "assessment p LEFT JOIN " . DB_PREFIX . "assessment_description pd ON (p.assessment_id = pd.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_to_exam p2c ON (p.assessment_id = p2c.assessment_id) LEFT JOIN " . DB_PREFIX . "exam_description cd ON (p2c.exam_id = cd.exam_id)";

		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		// Nouveau code pour ajouter le filtre
		if (!empty($data['filter_exam'])) {
			$sql .= " AND cd.name LIKE '" . $this->db->escape($data['filter_exam']) . "%'";
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_date']) && !is_null($data['filter_date'])) {
			$sql .= " AND pd.date LIKE '" . $this->db->escape($data['filter_date']) . "%'";
		}

		if (isset($data['filter_quantity']) && $data['filter_quantity'] !== '') {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalAssessmentsByTaxClassId($tax_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "assessment WHERE tax_class_id = '" . (int)$tax_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalAssessmentsByStockStatusId($stock_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "assessment WHERE stock_status_id = '" . (int)$stock_status_id . "'");

		return $query->row['total'];
	}

	public function getTotalAssessmentsByWeightClassId($weight_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "assessment WHERE weight_class_id = '" . (int)$weight_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalAssessmentsByLengthClassId($length_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "assessment WHERE length_class_id = '" . (int)$length_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalAssessmentsByDownloadId($download_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "assessment_to_download WHERE download_id = '" . (int)$download_id . "'");

		return $query->row['total'];
	}

	public function getTotalAssessmentsByManufacturerId($manufacturer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "assessment WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row['total'];
	}

	public function getTotalAssessmentsByAttributeId($attribute_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "assessment_attribute WHERE attribute_id = '" . (int)$attribute_id . "'");

		return $query->row['total'];
	}

	public function getTotalAssessmentsByOptionId($option_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "assessment_option WHERE option_id = '" . (int)$option_id . "'");

		return $query->row['total'];
	}

	public function getTotalAssessmentsByProfileId($recurring_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "assessment_recurring WHERE recurring_id = '" . (int)$recurring_id . "'");

		return $query->row['total'];
	}

	public function getTotalAssessmentsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "assessment_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}

	public function getTotalAssessmentsByCenterId($center_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "assessment_description WHERE center_id = '" . (int)$center_id . "'");

		return $query->row['total'];
	}

}
