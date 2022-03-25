<?php
class ModelExtensionFeedGoogleBase extends Model {
    public function getExams() {
		$query = $this->db->query("SELECT google_base_exam_id, (SELECT name FROM `" . DB_PREFIX . "google_base_exam` gbc WHERE gbc.google_base_exam_id = gbc2c.google_base_exam_id) AS google_base_exam, exam_id, (SELECT name FROM `" . DB_PREFIX . "exam_description` cd WHERE cd.exam_id = gbc2c.exam_id AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS exam FROM `" . DB_PREFIX . "google_base_exam_to_exam` gbc2c ORDER BY google_base_exam ASC");

		return $query->rows;
    }

	public function getTotalExams() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "google_base_exam_to_exam`");

		return $query->row['total'];
    }
}
