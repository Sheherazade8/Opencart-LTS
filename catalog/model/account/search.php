<?php
class ModelAccountSearch extends Model {
	public function addSearch($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_search` SET `store_id` = '" . (int)$this->config->get('config_store_id') . "', `language_id` = '" . (int)$this->config->get('config_language_id') . "', `customer_id` = '" . (int)$data['customer_id'] . "', `keyword` = '" . $this->db->escape($data['keyword']) . "', `exam_id` = '" . (int)$data['exam_id'] . "', `sub_exam` = '" . (int)$data['sub_exam'] . "', `description` = '" . (int)$data['description'] . "', `assessments` = '" . (int)$data['assessments'] . "', `ip` = '" . $this->db->escape($data['ip']) . "', `date_added` = NOW()");
	}
}
