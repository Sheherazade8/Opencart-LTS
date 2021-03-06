<?php
class ModelExtensionModuleCenter extends Model {
	public function addCenter($data) {

		foreach ($data['center_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "center SET center_id = '" . (int)$this->db->getLastId() . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($data['name']) . "', description = '" . $this->db->escape($value['description']) . "', city = '" . $this->db->escape($data['city']) . "', location = '" . $this->db->escape($data['location']) . "', capacity = '" . $this->db->escape($data['capacity']) . "', image = '" . $this->db->escape($data['image']) . "', sort_order = '" . (int)$data['sort_order'] . "'");
		}

		// $center_id = $this->db->getLastId();


        // if (isset($data['city'])) {
		// 	$this->db->query("UPDATE " . DB_PREFIX . "center SET city = '" . $this->db->escape($data['city']) . "' WHERE center_id = '" . (int)$center_id . "'");
		// }

        // if (isset($data['location'])) {
		// 	$this->db->query("UPDATE " . DB_PREFIX . "center SET location = '" . $this->db->escape($data['location']) . "' WHERE center_id = '" . (int)$center_id . "'");
		// }

        // if (isset($data['capacity'])) {
		// 	$this->db->query("UPDATE " . DB_PREFIX . "center SET capacity = '" . $this->db->escape($data['capacity']) . "' WHERE center_id = '" . (int)$center_id . "'");
		// }

		// if (isset($data['image'])) {
		// 	$this->db->query("UPDATE " . DB_PREFIX . "center SET image = '" . $this->db->escape($data['image']) . "' WHERE center_id = '" . (int)$center_id . "'");
		// }

				
		// SEO URL
		if (isset($data['center_seo_url'])) {
			foreach ($data['center_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'center_id=" . (int)$center_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		$this->cache->delete('center');

		return $center_id;
	}

	public function editCenter($center_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "center SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE center_id = '" . (int)$center_id . "'");

		// Nouveau code
        if (isset($data['city'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "center SET city = '" . $this->db->escape($data['city']) . "' WHERE center_id = '" . (int)$center_id . "'");
		}

        if (isset($data['location'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "center SET location = '" . $this->db->escape($data['location']) . "' WHERE center_id = '" . (int)$center_id . "'");
		}

        if (isset($data['capacity'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "center SET capacity = '" . $this->db->escape($data['capacity']) . "' WHERE center_id = '" . (int)$center_id . "'");
		}

        if (isset($data['center_description'])) {
			foreach ($data['center_description'] as $language_id => $value) {
				$this->db->query("UPDATE " . DB_PREFIX . "center SET description = '" . $this->db->escape($value['description']) . "' WHERE center_id = '" . (int)$center_id . "' AND language_id = '" . (int)$language_id . "'");
			}
		}

        if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "center SET image = '" . $this->db->escape($data['image']) . "' WHERE center_id = '" . (int)$center_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "center_to_store WHERE center_id = '" . (int)$center_id . "'");

		if (isset($data['center_store'])) {
			foreach ($data['center_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "center_to_store SET center_id = '" . (int)$center_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'center_id=" . (int)$center_id . "'");

		if (isset($data['center_seo_url'])) {
			foreach ($data['center_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'center_id=" . (int)$center_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('center');
	}

	public function deleteCenter($center_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "center` WHERE center_id = '" . (int)$center_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "center_to_store` WHERE center_id = '" . (int)$center_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'center_id=" . (int)$center_id . "'");

		$this->cache->delete('center');
	}

	public function getCenter($center_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "center WHERE center_id = '" . (int)$center_id . "'");

		return $query->row;
	}

	public function getCenters($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "center";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%' AND language_id = '" . (int)$this->config->get('config_language_id') . "'";
		} else {
			$sql .= " WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'";
		}


		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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

	public function getCentersDescriptions() {
		$sql = "SELECT * FROM " . DB_PREFIX . "center";


		$query = $this->db->query($sql);

		return $query->rows;
	}


	public function getCenterDescription($center_id) {
		$center_description = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "center WHERE center_id = '" . (int)$center_id . "'");

		foreach ($query->rows as $result) {
			$center_description[$result['language_id']] = array('description' => $result['description']);
		}

		return $center_description;
	}


	public function getCenterStores($center_id) {
		$center_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "center_to_store WHERE center_id = '" . (int)$center_id . "'");

		foreach ($query->rows as $result) {
			$center_store_data[] = $result['store_id'];
		}

		return $center_store_data;
	}
	
	public function getCenterSeoUrls($center_id) {
		$center_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'center_id=" . (int)$center_id . "'");

		foreach ($query->rows as $result) {
			$center_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $center_seo_url_data;
	}
	
	public function getTotalCenters() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "center");

		return $query->row['total'];
	}	
}
