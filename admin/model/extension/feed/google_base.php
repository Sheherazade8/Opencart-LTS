<?php
class ModelExtensionFeedGoogleBase extends Model {
	public function install() {
		$this->db->query("
			CREATE TABLE `" . DB_PREFIX . "google_base_exam` (
				`google_base_exam_id` INT(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(255) NOT NULL,
				PRIMARY KEY (`google_base_exam_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

		$this->db->query("
			CREATE TABLE `" . DB_PREFIX . "google_base_exam_to_exam` (
				`google_base_exam_id` INT(11) NOT NULL,
				`exam_id` INT(11) NOT NULL,
				PRIMARY KEY (`google_base_exam_id`, `exam_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");
	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "google_base_exam`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "google_base_exam_to_exam`");
	}

    public function import($string) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "google_base_exam");

        $lines = explode("\n", $string);

        foreach ($lines as $line) {
			if (substr($line, 0, 1) != '#') {
	            $part = explode(' - ', $line, 2);

	            if (isset($part[1])) {
	                $this->db->query("INSERT INTO " . DB_PREFIX . "google_base_exam SET google_base_exam_id = '" . (int)$part[0] . "', name = '" . $this->db->escape($part[1]) . "'");
	            }
			}
        }
    }

    public function getGoogleBaseExams($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "google_base_exam` WHERE name LIKE '%" . $this->db->escape($data['filter_name']) . "%' ORDER BY name ASC";

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

	public function addExam($data) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "google_base_exam_to_exam WHERE exam_id = '" . (int)$data['exam_id'] . "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "google_base_exam_to_exam SET google_base_exam_id = '" . (int)$data['google_base_exam_id'] . "', exam_id = '" . (int)$data['exam_id'] . "'");
	}

	public function deleteExam($exam_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "google_base_exam_to_exam WHERE exam_id = '" . (int)$exam_id . "'");
	}

    public function getExams($data = array()) {
        $sql = "SELECT google_base_exam_id, (SELECT name FROM `" . DB_PREFIX . "google_base_exam` gbc WHERE gbc.google_base_exam_id = gbc2c.google_base_exam_id) AS google_base_exam, exam_id, (SELECT name FROM `" . DB_PREFIX . "exam_description` cd WHERE cd.exam_id = gbc2c.exam_id AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS exam FROM `" . DB_PREFIX . "google_base_exam_to_exam` gbc2c ORDER BY google_base_exam ASC";

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

	public function getTotalExams() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "google_base_exam_to_exam`");

		return $query->row['total'];
    }
}
