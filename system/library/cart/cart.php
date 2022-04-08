<?php
namespace Cart;
class Cart {
	private $data = array();

	public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->customer = $registry->get('customer');
		$this->session = $registry->get('session');
		$this->db = $registry->get('db');
		$this->tax = $registry->get('tax');
		$this->weight = $registry->get('weight');

		// Remove all the expired carts with no customer ID
		$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE (api_id > '0' OR customer_id = '0') AND date_added < DATE_SUB(NOW(), INTERVAL 1 HOUR)");

		if ($this->customer->getId()) {
			// We want to change the session ID on all the old items in the customers cart
			$this->db->query("UPDATE " . DB_PREFIX . "cart SET session_id = '" . $this->db->escape($this->session->getId()) . "' WHERE api_id = '0' AND customer_id = '" . (int)$this->customer->getId() . "'");

			// Once the customer is logged in we want to update the customers cart
			$cart_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE api_id = '0' AND customer_id = '0' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

			foreach ($cart_query->rows as $cart) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE cart_id = '" . (int)$cart['cart_id'] . "'");

				// The advantage of using $this->add is that it will check if the assessments already exist and increaser the quantity if necessary.
				$this->add($cart['assessment_id'], $cart['quantity'], json_decode($cart['option']), $cart['recurring_id']);
			}
		}
	}

	public function getAssessments() {
		$assessment_data = array();

		$cart_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

		foreach ($cart_query->rows as $cart) {
			$stock = true;

			$assessment_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_to_store p2s LEFT JOIN " . DB_PREFIX . "assessment p ON (p2s.assessment_id = p.assessment_id) LEFT JOIN " . DB_PREFIX . "assessment_description pd ON (p.assessment_id = pd.assessment_id) WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2s.assessment_id = '" . (int)$cart['assessment_id'] . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.date_available <= NOW() AND p.status = '1'");

			if ($assessment_query->num_rows && ($cart['quantity'] > 0)) {

				// Nouveau code
				$exams = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_to_exam WHERE assessment_id = '" . (int)$cart['assessment_id'] . "'");
				foreach ($exams->rows as $exam ) {
					$exam_id = $exam['exam_id'];
				}

				$option_price = 0;
				$option_points = 0;
				// $option_weight = 0;

				$option_data = array();

				foreach (json_decode($cart['option']) as $option_id => $value) {
					// Nouveau code pour obtenir les options de Exam
				
					$option_query = $this->db->query("SELECT po.exam_option_id, po.option_id, od.name, o.type FROM " . DB_PREFIX . "exam_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.exam_option_id = '" . (int)$option_id . "' AND po.exam_id = '" . (int)$exam_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($option_query->num_rows) {
						if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio') {
							$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix FROM " . DB_PREFIX . "exam_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.exam_option_value_id = '" . (int)$value . "' AND pov.exam_option_id = '" . (int)$option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

							if ($option_value_query->num_rows) {
								if ($option_value_query->row['price_prefix'] == '+') {
									$option_price += $option_value_query->row['price'];
								} elseif ($option_value_query->row['price_prefix'] == '-') {
									$option_price -= $option_value_query->row['price'];
								}

								if ($option_value_query->row['points_prefix'] == '+') {
									$option_points += $option_value_query->row['points'];
								} elseif ($option_value_query->row['points_prefix'] == '-') {
									$option_points -= $option_value_query->row['points'];
								}

								// Nouveau code pour enlever quantity de option
								if (!$option_value_query->row['subtract']) {
									$stock = false;
								}

								$option_data[] = array(
									'assessment_option_id'       => $option_id,
									'assessment_option_value_id' => $value,
									'option_id'               => $option_query->row['option_id'],
									'option_value_id'         => $option_value_query->row['option_value_id'],
									'name'                    => $option_query->row['name'],
									'value'                   => $option_value_query->row['name'],
									'type'                    => $option_query->row['type'],
									'subtract'                => $option_value_query->row['subtract'],
									'price'                   => $option_value_query->row['price'],
									'price_prefix'            => $option_value_query->row['price_prefix'],
									'points'                  => $option_value_query->row['points'],
									'points_prefix'           => $option_value_query->row['points_prefix']
								);
							}
						} elseif ($option_query->row['type'] == 'checkbox' && is_array($value)) {
							foreach ($value as $option_value_id) {
								$option_value_query = $this->db->query("SELECT pov.option_value_id, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, ovd.name FROM " . DB_PREFIX . "exam_option_value pov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (pov.option_value_id = ovd.option_value_id) WHERE pov.exam_option_value_id = '" . (int)$option_value_id . "' AND pov.exam_option_id = '" . (int)$option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

								if ($option_value_query->num_rows) {
									if ($option_value_query->row['price_prefix'] == '+') {
										$option_price += $option_value_query->row['price'];
									} elseif ($option_value_query->row['price_prefix'] == '-') {
										$option_price -= $option_value_query->row['price'];
									}

									if ($option_value_query->row['points_prefix'] == '+') {
										$option_points += $option_value_query->row['points'];
									} elseif ($option_value_query->row['points_prefix'] == '-') {
										$option_points -= $option_value_query->row['points'];
									}

									// Nouveau code pour enlever quantity de option

									if (!$option_value_query->row['subtract']) {
										$stock = false;
									}

									$option_data[] = array(
										'assessment_option_id'       => $option_id,
										'assessment_option_value_id' => $option_value_id,
										'option_id'               => $option_query->row['option_id'],
										'option_value_id'         => $option_value_query->row['option_value_id'],
										'name'                    => $option_query->row['name'],
										'value'                   => $option_value_query->row['name'],
										'type'                    => $option_query->row['type'],
										'subtract'                => $option_value_query->row['subtract'],
										'price'                   => $option_value_query->row['price'],
										'price_prefix'            => $option_value_query->row['price_prefix'],
										'points'                  => $option_value_query->row['points'],
										'points_prefix'           => $option_value_query->row['points_prefix']
									);
								}
							}
						} elseif ($option_query->row['type'] == 'text' || $option_query->row['type'] == 'textarea' || $option_query->row['type'] == 'file' || $option_query->row['type'] == 'date' || $option_query->row['type'] == 'datetime' || $option_query->row['type'] == 'time') {
							$option_data[] = array(
								'assessment_option_id'       => $option_id,
								'assessment_option_value_id' => '',
								'option_id'               => $option_query->row['option_id'],
								'option_value_id'         => '',
								'name'                    => $option_query->row['name'],
								'value'                   => $value,
								'type'                    => $option_query->row['type'],
								'subtract'                => '',
								'price'                   => '',
								'price_prefix'            => '',
								'points'                  => '',
								'points_prefix'           => ''
							);
						}
					}
				}

				$price = $assessment_query->row['price'];

				// Assessment Discounts
				$discount_quantity = 0;

				foreach ($cart_query->rows as $cart_2) {
					if ($cart_2['assessment_id'] == $cart['assessment_id']) {
						$discount_quantity += $cart_2['quantity'];
					}
				}

				$assessment_discount_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "assessment_discount WHERE assessment_id = '" . (int)$cart['assessment_id'] . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND quantity <= '" . (int)$discount_quantity . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1");

				if ($assessment_discount_query->num_rows) {
					$price = $assessment_discount_query->row['price'];
				}

				// Assessment Specials
				$assessment_special_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "assessment_special WHERE assessment_id = '" . (int)$cart['assessment_id'] . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 1");

				if ($assessment_special_query->num_rows) {
					$price = $assessment_special_query->row['price'];
				}

				// Reward Points
				$assessment_reward_query = $this->db->query("SELECT points FROM " . DB_PREFIX . "assessment_reward WHERE assessment_id = '" . (int)$cart['assessment_id'] . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");

				if ($assessment_reward_query->num_rows) {
					$reward = $assessment_reward_query->row['points'];
				} else {
					$reward = 0;
				}

				// Downloads
				$download_data = array();

				$download_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assessment_to_download p2d LEFT JOIN " . DB_PREFIX . "download d ON (p2d.download_id = d.download_id) LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id) WHERE p2d.assessment_id = '" . (int)$cart['assessment_id'] . "' AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

				foreach ($download_query->rows as $download) {
					$download_data[] = array(
						'download_id' => $download['download_id'],
						'name'        => $download['name'],
						'filename'    => $download['filename'],
						'mask'        => $download['mask']
					);
				}

				// Stock
				if (!$assessment_query->row['quantity'] || ($assessment_query->row['quantity'] < $cart['quantity'])) {
					$stock = false;
				}

				$recurring_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recurring r LEFT JOIN " . DB_PREFIX . "assessment_recurring pr ON (r.recurring_id = pr.recurring_id) LEFT JOIN " . DB_PREFIX . "recurring_description rd ON (r.recurring_id = rd.recurring_id) WHERE r.recurring_id = '" . (int)$cart['recurring_id'] . "' AND pr.assessment_id = '" . (int)$cart['assessment_id'] . "' AND rd.language_id = " . (int)$this->config->get('config_language_id') . " AND r.status = 1 AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");

				if ($recurring_query->num_rows) {
					$recurring = array(
						'recurring_id'    => $cart['recurring_id'],
						'name'            => $recurring_query->row['name'],
						'frequency'       => $recurring_query->row['frequency'],
						'price'           => $recurring_query->row['price'],
						'cycle'           => $recurring_query->row['cycle'],
						'duration'        => $recurring_query->row['duration'],
						'trial'           => $recurring_query->row['trial_status'],
						'trial_frequency' => $recurring_query->row['trial_frequency'],
						'trial_price'     => $recurring_query->row['trial_price'],
						'trial_cycle'     => $recurring_query->row['trial_cycle'],
						'trial_duration'  => $recurring_query->row['trial_duration']
					);
				} else {
					$recurring = false;
				}

				// Nouveau code
				$exams = $this->db->query("SELECT * FROM " . DB_PREFIX . "exam_description WHERE exam_id = '" . (int)$exam_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
				foreach ($exams->rows as $exam) {
					$exam_name = $exam['name'];
				}

				$assessment_data[] = array(
					'cart_id'         => $cart['cart_id'],
					'assessment_id'      => $assessment_query->row['assessment_id'],
					'name'            => $assessment_query->row['name'],
					// Nouveau code
					'exam' => $exam_name,
					'date' => $assessment_query->row['date'],
					'model'           => $assessment_query->row['model'],
					'shipping'        => $assessment_query->row['shipping'],
					'image'           => $assessment_query->row['image'],
					'option'          => $option_data,
					'download'        => $download_data,
					'quantity'        => $cart['quantity'],
					'minimum'         => $assessment_query->row['minimum'],
					'subtract'        => $assessment_query->row['subtract'],
					'stock'           => $stock,
					'price'           => ($price + $option_price),
					'total'           => ($price + $option_price) * $cart['quantity'],
					'reward'          => $reward * $cart['quantity'],
					'points'          => ($assessment_query->row['points'] ? ($assessment_query->row['points'] + $option_points) * $cart['quantity'] : 0),
					'tax_class_id'    => $assessment_query->row['tax_class_id'],
					// 'weight'          => ($assessment_query->row['weight'] + $option_weight) * $cart['quantity'],
					// 'weight_class_id' => $assessment_query->row['weight_class_id'],
					// 'length'          => $assessment_query->row['length'],
					// 'width'           => $assessment_query->row['width'],
					// 'height'          => $assessment_query->row['height'],
					// 'length_class_id' => $assessment_query->row['length_class_id'],
					'recurring'       => $recurring
				);
			} else {
				$this->remove($cart['cart_id']);
			}
		}

		return $assessment_data;
	}

	public function add($assessment_id, $quantity = 1, $option = array(), $recurring_id = 0) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND assessment_id = '" . (int)$assessment_id . "' AND recurring_id = '" . (int)$recurring_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'");

		if (!$query->row['total']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "cart SET api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "', customer_id = '" . (int)$this->customer->getId() . "', session_id = '" . $this->db->escape($this->session->getId()) . "', assessment_id = '" . (int)$assessment_id . "', recurring_id = '" . (int)$recurring_id . "', `option` = '" . $this->db->escape(json_encode($option)) . "', quantity = '" . (int)$quantity . "', date_added = NOW()");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "cart SET quantity = (quantity + " . (int)$quantity . ") WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND assessment_id = '" . (int)$assessment_id . "' AND recurring_id = '" . (int)$recurring_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'");
		}
	}

	public function update($cart_id, $quantity) {
		$this->db->query("UPDATE " . DB_PREFIX . "cart SET quantity = '" . (int)$quantity . "' WHERE cart_id = '" . (int)$cart_id . "' AND api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");
	}

	public function remove($cart_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE cart_id = '" . (int)$cart_id . "' AND api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");
	}

	public function clear() {
		$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");
	}

	public function getRecurringAssessments() {
		$assessment_data = array();

		foreach ($this->getAssessments() as $value) {
			if ($value['recurring']) {
				$assessment_data[] = $value;
			}
		}

		return $assessment_data;
	}

	public function getWeight() {
		$weight = 0;

		// Nouveau code pour supprimer weight
		// foreach ($this->getAssessments() as $assessment) {
		// 	if ($assessment['shipping']) {
		// 		$weight += $this->weight->convert($assessment['weight'], $assessment['weight_class_id'], $this->config->get('config_weight_class_id'));
		// 	}
		// }

		return $weight;
	}

	public function getSubTotal() {
		$total = 0;

		foreach ($this->getAssessments() as $assessment) {
			$total += $assessment['total'];
		}

		return $total;
	}

	public function getTaxes() {
		$tax_data = array();

		foreach ($this->getAssessments() as $assessment) {
			if ($assessment['tax_class_id']) {
				$tax_rates = $this->tax->getRates($assessment['price'], $assessment['tax_class_id']);

				foreach ($tax_rates as $tax_rate) {
					if (!isset($tax_data[$tax_rate['tax_rate_id']])) {
						$tax_data[$tax_rate['tax_rate_id']] = ($tax_rate['amount'] * $assessment['quantity']);
					} else {
						$tax_data[$tax_rate['tax_rate_id']] += ($tax_rate['amount'] * $assessment['quantity']);
					}
				}
			}
		}

		return $tax_data;
	}

	public function getTotal() {
		$total = 0;

		foreach ($this->getAssessments() as $assessment) {
			$total += $this->tax->calculate($assessment['price'], $assessment['tax_class_id'], $this->config->get('config_tax')) * $assessment['quantity'];
		}

		return $total;
	}

	public function countAssessments() {
		$assessment_total = 0;

		$assessments = $this->getAssessments();

		foreach ($assessments as $assessment) {
			$assessment_total += $assessment['quantity'];
		}

		return $assessment_total;
	}

	public function hasAssessments() {
		return count($this->getAssessments());
	}

	public function hasRecurringAssessments() {
		return count($this->getRecurringAssessments());
	}

	public function hasStock() {
		foreach ($this->getAssessments() as $assessment) {
			if (!$assessment['stock']) {
				return false;
			}
		}

		return true;
	}

	public function hasShipping() {
		foreach ($this->getAssessments() as $assessment) {
			if ($assessment['shipping']) {
				return true;
			}
		}

		return false;
	}

	public function hasDownload() {
		foreach ($this->getAssessments() as $assessment) {
			if ($assessment['download']) {
				return true;
			}
		}

		return false;
	}
}
