<?php
class ModelCatalogReview extends Model {
	public function addReview($assessment_id, $data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$this->customer->getId() . "', assessment_id = '" . (int)$assessment_id . "', text = '" . $this->db->escape($data['text']) . "', rating = '" . (int)$data['rating'] . "', date_added = NOW()");

		$review_id = $this->db->getLastId();

		if (in_array('review', (array)$this->config->get('config_mail_alert'))) {
			$this->load->language('mail/review');
			$this->load->model('catalog/assessment');
			
			$assessment_info = $this->model_catalog_assessment->getAssessment($assessment_id);

			$subject = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

			$message  = $this->language->get('text_waiting') . "\n";
			$message .= sprintf($this->language->get('text_assessment'), html_entity_decode($assessment_info['name'], ENT_QUOTES, 'UTF-8')) . "\n";
			$message .= sprintf($this->language->get('text_reviewer'), html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8')) . "\n";
			$message .= sprintf($this->language->get('text_rating'), $data['rating']) . "\n";
			$message .= $this->language->get('text_review') . "\n";
			$message .= html_entity_decode($data['text'], ENT_QUOTES, 'UTF-8') . "\n\n";

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setText($message);
			$mail->send();

			// Send to additional alert emails
			$emails = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails as $email) {
				if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}
	}

	public function getReviewsByAssessmentId($assessment_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->query("SELECT r.review_id, r.author, r.rating, r.text, a.assessment_id, a.name, a.date, a.image, r.date_added FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "assessment a ON (r.assessment_id = a.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_description ad ON (a.assessment_id = ad.assessment_id) WHERE a.assessment_id = '" . (int)$assessment_id . "' AND a.date_available <= NOW() AND a.status = '1' AND r.status = '1' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalReviewsByAssessmentId($assessment_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "assessment a ON (r.assessment_id = a.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_description ad ON (a.assessment_id = ad.assessment_id) WHERE a.assessment_id = '" . (int)$assessment_id . "' AND a.date_available <= NOW() AND a.status = '1' AND r.status = '1' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}
}


// class ModelCatalogReview extends Model {
// 	public function addReview($assessment_id, $data) {
// 		$this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$this->customer->getId() . "', assessment_id = '" . (int)$assessment_id . "', text = '" . $this->db->escape($data['text']) . "', rating = '" . (int)$data['rating'] . "', date_added = NOW()");

// 		$review_id = $this->db->getLastId();

// 		if (in_array('review', (array)$this->config->get('config_mail_alert'))) {
// 			$this->load->language('mail/review');
// 			$this->load->model('catalog/assessment');
			
// 			$assessment_info = $this->model_catalog_assessment->getAssessment($assessment_id);

// 			$subject = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

// 			$message  = $this->language->get('text_waiting') . "\n";
// 			$message .= sprintf($this->language->get('text_assessment'), html_entity_decode($assessment_info['name'], ENT_QUOTES, 'UTF-8')) . "\n";
// 			$message .= sprintf($this->language->get('text_reviewer'), html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8')) . "\n";
// 			$message .= sprintf($this->language->get('text_rating'), $data['rating']) . "\n";
// 			$message .= $this->language->get('text_review') . "\n";
// 			$message .= html_entity_decode($data['text'], ENT_QUOTES, 'UTF-8') . "\n\n";

// 			$mail = new Mail($this->config->get('config_mail_engine'));
// 			$mail->parameter = $this->config->get('config_mail_parameter');
// 			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
// 			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
// 			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
// 			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
// 			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

// 			$mail->setTo($this->config->get('config_email'));
// 			$mail->setFrom($this->config->get('config_email'));
// 			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
// 			$mail->setSubject($subject);
// 			$mail->setText($message);
// 			$mail->send();

// 			// Send to additional alert emails
// 			$emails = explode(',', $this->config->get('config_mail_alert_email'));

// 			foreach ($emails as $email) {
// 				if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
// 					$mail->setTo($email);
// 					$mail->send();
// 				}
// 			}
// 		}
// 	}

// 	public function getReviewsByAssessmentId($assessment_id, $start = 0, $limit = 20) {
// 		if ($start < 0) {
// 			$start = 0;
// 		}

// 		if ($limit < 1) {
// 			$limit = 20;
// 		}

// 		$query = $this->db->query("SELECT r.review_id, r.author, r.rating, r.text, p.assessment_id, pd.name, p.price, p.image, r.date_added FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "assessment p ON (r.assessment_id = p.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_description pd ON (p.assessment_id = pd.assessment_id) WHERE p.assessment_id = '" . (int)$assessment_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

// 		return $query->rows;
// 	}

// 	public function getTotalReviewsByAssessmentId($assessment_id) {
// 		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "assessment p ON (r.assessment_id = p.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_description pd ON (p.assessment_id = pd.assessment_id) WHERE p.assessment_id = '" . (int)$assessment_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

// 		return $query->row['total'];
// 	}
// }