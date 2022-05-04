<?php

class ModelExtensionAdvertiseGoogle extends Model {
    public function getHumanReadableExam($assessment_id, $store_id) {
        $this->load->config('googleshopping/googleshopping');

        $google_exam_result = $this->db->query("SELECT google_assessment_exam FROM `" . DB_PREFIX . "googleshopping_assessment` pag WHERE pag.assessment_id = " . (int)$assessment_id . " AND pag.store_id = " . (int)$store_id);

        if ($google_exam_result->num_rows > 0) {
            $google_exam_id = $google_exam_result->row['google_assessment_exam'];
            $google_exams = $this->config->get('advertise_google_google_assessment_exams');

            if (!empty($google_exam_id) && isset($google_exams[$google_exam_id])) {
                return $google_exams[$google_exam_id];
            }
        }

        $oc_exam_result = $this->db->query("SELECT c.exam_id FROM `" . DB_PREFIX . "assessment_to_exam` p2c LEFT JOIN `" . DB_PREFIX . "exam` c ON (c.exam_id = p2c.exam_id) WHERE p2c.assessment_id=" . (int)$assessment_id . " LIMIT 0,1");

        if ($oc_exam_result->num_rows > 0) {
            return $this->getHumanReadableOpenCartExam((int)$oc_exam_result->row['exam_id']);
        }

        return "";
    }

    public function getHumanReadableOpenCartExam($exam_id) {
        $sql = "SELECT GROUP_CONCAT(e.name ORDER BY cp.level SEPARATOR ' &gt; ') AS path FROM " . DB_PREFIX . "exam_path cp LEFT JOIN " . DB_PREFIX . "exam e ON (cp.path_id = e.exam_id) WHERE cp.exam_id=" . (int)$exam_id;

        $result = $this->db->query($sql);

        if ($result->num_rows > 0) {
            return $result->row['path'];
        }

        return "";
    }

    public function getSizeAndColorOptionMap($assessment_id, $store_id) {
        $color_id = $this->getOptionId($assessment_id, $store_id, 'color');
        $size_id = $this->getOptionId($assessment_id, $store_id, 'size');

        $groups = $this->googleshopping->getGroups($assessment_id, $this->config->get('config_language_id'), $color_id, $size_id);

        $colors = $this->googleshopping->getAssessmentOptionValueNames($assessment_id, $this->config->get('config_language_id'), $color_id);
        $sizes = $this->googleshopping->getAssessmentOptionValueNames($assessment_id, $this->config->get('config_language_id'), $size_id);

        $map = array(
            'groups' => $groups,
            'colors' => count($colors) > 1 ? $colors : null,
            'sizes' => count($sizes) > 1 ? $sizes : null,
        );

        return $map;
    }

    public function getCoupon($order_id) {
        $sql = "SELECT c.code FROM `" . DB_PREFIX . "coupon_history` ch LEFT JOIN `" . DB_PREFIX . "coupon` c ON (c.coupon_id = ch.coupon_id) WHERE ch.order_id=" . (int)$order_id;

        $result = $this->db->query($sql);

        if ($result->num_rows > 0) {
            return $result->row['code'];
        }

        return null;
    }

    public function getRemarketingAssessmentIds($assessments, $store_id) {
        $ecomm_prodid = array();

        foreach ($assessments as $assessment) {
            if (null !== $id = $this->getRemarketingAssessmentId($assessment, $store_id)) {
                $ecomm_prodid[] = $id;
            }
        }

        return $ecomm_prodid;
    }

    public function getRemarketingItems($assessments, $store_id) {
        $items = array();

        foreach ($assessments as $assessment) {
            if (null !== $id = $this->getRemarketingAssessmentId($assessment, $store_id)) {
                $items[] = array(
                    'google_business_vertical' => 'retail',
                    'id' => (string)$id,
                    'name' => (string)$assessment['name'],
                    'quantity' => (int)$assessment['quantity']
                );
            }
        }

        return $items;
    }

    protected function getRemarketingAssessmentId($assessment, $store_id) {
        $option_map = $this->getSizeAndColorOptionMap($assessment['assessment_id'], $store_id);
        $found_color = "";
        $found_size = "";

        foreach ($assessment['option'] as $option) {
            if (is_array($option_map['colors'])) {
                foreach ($option_map['colors'] as $assessment_option_value_id => $color) {
                    if ($option['assessment_option_value_id'] == $assessment_option_value_id) {
                        $found_color = $color;
                    }
                }
            }

            if (is_array($option_map['sizes'])) {
                foreach ($option_map['sizes'] as $assessment_option_value_id => $size) {
                    if ($option['assessment_option_value_id'] == $assessment_option_value_id) {
                        $found_size = $size;
                    }
                }
            }
        }

        foreach ($option_map['groups'] as $id => $group) {
            if ($group['color'] === $found_color && $group['size'] === $found_size) {
                return $id;
            }
        }

        return null;
    }

    protected function getOptionId($assessment_id, $store_id, $type) {
        $sql = "SELECT pag." . $type . " FROM `" . DB_PREFIX . "googleshopping_assessment` pag WHERE assessment_id=" . (int)$assessment_id . " AND store_id=" . (int)$store_id;

        $result = $this->db->query($sql);

        if ($result->num_rows > 0) {
            return (int)$result->row[$type];
        }

        return 0;
    }
}