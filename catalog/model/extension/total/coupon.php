<?php
class ModelExtensionTotalCoupon extends Model {
	public function getCoupon($code) {
		$status = true;

		$coupon_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon` WHERE code = '" . $this->db->escape($code) . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) AND status = '1'");

		if ($coupon_query->num_rows) {
			if ($coupon_query->row['total'] > $this->cart->getSubTotal()) {
				$status = false;
			}

			$coupon_total = $this->getTotalCouponHistoriesByCoupon($code);

			if ($coupon_query->row['uses_total'] > 0 && ($coupon_total >= $coupon_query->row['uses_total'])) {
				$status = false;
			}

			if ($coupon_query->row['logged'] && !$this->customer->getId()) {
				$status = false;
			}

			if ($this->customer->getId()) {
				$customer_total = $this->getTotalCouponHistoriesByCustomerId($code, $this->customer->getId());
				
				if ($coupon_query->row['uses_customer'] > 0 && ($customer_total >= $coupon_query->row['uses_customer'])) {
					$status = false;
				}
			}

			// Assessments
			$coupon_assessment_data = array();

			$coupon_assessment_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon_assessment` WHERE coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");

			foreach ($coupon_assessment_query->rows as $assessment) {
				$coupon_assessment_data[] = $assessment['assessment_id'];
			}

			// Exams
			$coupon_exam_data = array();

			$coupon_exam_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon_exam` cc LEFT JOIN `" . DB_PREFIX . "exam_path` cp ON (cc.exam_id = cp.path_id) WHERE cc.coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");

			foreach ($coupon_exam_query->rows as $exam) {
				$coupon_exam_data[] = $exam['exam_id'];
			}

			$assessment_data = array();

			if ($coupon_assessment_data || $coupon_exam_data) {
				foreach ($this->cart->getAssessments() as $assessment) {
					if (in_array($assessment['assessment_id'], $coupon_assessment_data)) {
						$assessment_data[] = $assessment['assessment_id'];

						continue;
					}

					foreach ($coupon_exam_data as $exam_id) {
						$coupon_exam_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "assessment_to_exam` WHERE `assessment_id` = '" . (int)$assessment['assessment_id'] . "' AND exam_id = '" . (int)$exam_id . "'");

						if ($coupon_exam_query->row['total']) {
							$assessment_data[] = $assessment['assessment_id'];

							continue;
						}
					}
				}

				if (!$assessment_data) {
					$status = false;
				}
			}
		} else {
			$status = false;
		}

		if ($status) {
			return array(
				'coupon_id'     => $coupon_query->row['coupon_id'],
				'code'          => $coupon_query->row['code'],
				'name'          => $coupon_query->row['name'],
				'type'          => $coupon_query->row['type'],
				'discount'      => $coupon_query->row['discount'],
				'shipping'      => $coupon_query->row['shipping'],
				'total'         => $coupon_query->row['total'],
				'assessment'       => $assessment_data,
				'date_start'    => $coupon_query->row['date_start'],
				'date_end'      => $coupon_query->row['date_end'],
				'uses_total'    => $coupon_query->row['uses_total'],
				'uses_customer' => $coupon_query->row['uses_customer'],
				'status'        => $coupon_query->row['status'],
				'date_added'    => $coupon_query->row['date_added']
			);
		}
	}

	public function getTotal($total) {
		if (isset($this->session->data['coupon'])) {
			$this->load->language('extension/total/coupon', 'coupon');

			$coupon_info = $this->getCoupon($this->session->data['coupon']);

			if ($coupon_info) {
				$discount_total = 0;

				if (!$coupon_info['assessment']) {
					$sub_total = $this->cart->getSubTotal();
				} else {
					$sub_total = 0;

					foreach ($this->cart->getAssessments() as $assessment) {
						if (in_array($assessment['assessment_id'], $coupon_info['assessment'])) {
							$sub_total += $assessment['total'];
						}
					}
				}

				if ($coupon_info['type'] == 'F') {
					$coupon_info['discount'] = min($coupon_info['discount'], $sub_total);
				}

				foreach ($this->cart->getAssessments() as $assessment) {
					$discount = 0;

					if (!$coupon_info['assessment']) {
						$status = true;
					} else {
						$status = in_array($assessment['assessment_id'], $coupon_info['assessment']);
					}

					if ($status) {
						if ($coupon_info['type'] == 'F') {
							$discount = $coupon_info['discount'] * ($assessment['total'] / $sub_total);
						} elseif ($coupon_info['type'] == 'P') {
							$discount = $assessment['total'] / 100 * $coupon_info['discount'];
						}

						if ($assessment['tax_class_id']) {
							$tax_rates = $this->tax->getRates($assessment['total'] - ($assessment['total'] - $discount), $assessment['tax_class_id']);

							foreach ($tax_rates as $tax_rate) {
								if ($tax_rate['type'] == 'P') {
									$total['taxes'][$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
								}
							}
						}
					}

					$discount_total += $discount;
				}

				if ($coupon_info['shipping'] && isset($this->session->data['shipping_method'])) {
					if (!empty($this->session->data['shipping_method']['tax_class_id'])) {
						$tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);

						foreach ($tax_rates as $tax_rate) {
							if ($tax_rate['type'] == 'P') {
								$total['taxes'][$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
							}
						}
					}

					$discount_total += $this->session->data['shipping_method']['cost'];
				}

				// If discount greater than total
				if ($discount_total > $total['total']) {
					$discount_total = $total['total'];
				}

				if ($discount_total > 0) {
					$total['totals'][] = array(
						'code'       => 'coupon',
						'title'      => sprintf($this->language->get('coupon')->get('text_coupon'), $this->session->data['coupon']),
						'value'      => -$discount_total,
						'sort_order' => $this->config->get('total_coupon_sort_order')
					);

					$total['total'] -= $discount_total;
				}
			}
		}
	}

	public function confirm($order_info, $order_total) {
		$code = '';

		$start = strpos($order_total['title'], '(') + 1;
		$end = strrpos($order_total['title'], ')');

		if ($start && $end) {
			$code = substr($order_total['title'], $start, $end - $start);
		}

		if ($code) {
			$status = true;
			
			$coupon_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon` WHERE code = '" . $this->db->escape($code) . "' AND status = '1'");

			if ($coupon_query->num_rows) {
				$coupon_total = $this->getTotalCouponHistoriesByCoupon($code);
	
				if ($coupon_query->row['uses_total'] > 0 && ($coupon_total >= $coupon_query->row['uses_total'])) {
					$status = false;
				}
				
				if ($order_info['customer_id']) {
					$customer_total = $this->getTotalCouponHistoriesByCustomerId($code, $order_info['customer_id']);
					
					if ($coupon_query->row['uses_customer'] > 0 && ($customer_total >= $coupon_query->row['uses_customer'])) {
						$status = false;
					}
				}
			} else {
				$status = false;	
			}

			if ($status) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "coupon_history` SET coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', customer_id = '" . (int)$order_info['customer_id'] . "', amount = '" . (float)$order_total['value'] . "', date_added = NOW()");
			} else {
				return $this->config->get('config_fraud_status_id');
			}
		}
	}

	public function unconfirm($order_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "coupon_history` WHERE order_id = '" . (int)$order_id . "'");
	}
	
	public function getTotalCouponHistoriesByCoupon($coupon) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "coupon_history` ch LEFT JOIN `" . DB_PREFIX . "coupon` c ON (ch.coupon_id = c.coupon_id) WHERE c.code = '" . $this->db->escape($coupon) . "'");	
		
		return $query->row['total'];
	}
	
	public function getTotalCouponHistoriesByCustomerId($coupon, $customer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "coupon_history` ch LEFT JOIN `" . DB_PREFIX . "coupon` c ON (ch.coupon_id = c.coupon_id) WHERE c.code = '" . $this->db->escape($coupon) . "' AND ch.customer_id = '" . (int)$customer_id . "'");
		
		return $query->row['total'];
	}
}
