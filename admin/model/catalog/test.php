<?php

// Nouveau code pour ajouter options à Exam

// AddExam

if (isset($data['exam_option'])) {
      foreach ($data['exam_option'] as $exam_option) {
            if ($exam_option['type'] == 'select' || $exam_option['type'] == 'radio' || $exam_option['type'] == 'checkbox' || $exam_option['type'] == 'image') {
                  if (isset($exam_option['exam_option_value'])) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "exam_option SET exam_id = '" . (int)$exam_id . "', option_id = '" . (int)$exam_option['option_id'] . "', required = '" . (int)$exam_option['required'] . "'");

                        $exam_option_id = $this->db->getLastId();

                        foreach ($exam_option['exam_option_value'] as $exam_option_value) {
                              $this->db->query("INSERT INTO " . DB_PREFIX . "exam_option_value SET exam_option_id = '" . (int)$exam_option_id . "', exam_id = '" . (int)$exam_id . "', option_id = '" . (int)$exam_option['option_id'] . "', option_value_id = '" . (int)$exam_option_value['option_value_id'] . "', quantity = '" . (int)$exam_option_value['quantity'] . "', subtract = '" . (int)$exam_option_value['subtract'] . "', price = '" . (float)$exam_option_value['price'] . "', price_prefix = '" . $this->db->escape($exam_option_value['price_prefix']) . "', points = '" . (int)$exam_option_value['points'] . "', points_prefix = '" . $this->db->escape($exam_option_value['points_prefix']) . "', weight = '" . (float)$exam_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($exam_option_value['weight_prefix']) . "'");
                        }
                  }
            } else {
                  $this->db->query("INSERT INTO " . DB_PREFIX . "exam_option SET exam_id = '" . (int)$exam_id . "', option_id = '" . (int)$exam_option['option_id'] . "', value = '" . $this->db->escape($exam_option['value']) . "', required = '" . (int)$exam_option['required'] . "'");
            }
      }
}


// editExam

$this->db->query("DELETE FROM " . DB_PREFIX . "exam_option WHERE exam_id = '" . (int)$exam_id . "'");
$this->db->query("DELETE FROM " . DB_PREFIX . "exam_option_value WHERE exam_id = '" . (int)$exam_id . "'");

if (isset($data['exam_option'])) {
      foreach ($data['exam_option'] as $exam_option) {
            if ($exam_option['type'] == 'select' || $exam_option['type'] == 'radio' || $exam_option['type'] == 'checkbox' || $exam_option['type'] == 'image') {
                  if (isset($exam_option['exam_option_value'])) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "exam_option SET exam_option_id = '" . (int)$exam_option['exam_option_id'] . "', exam_id = '" . (int)$exam_id . "', option_id = '" . (int)$exam_option['option_id'] . "', required = '" . (int)$exam_option['required'] . "'");

                        $exam_option_id = $this->db->getLastId();

                        foreach ($exam_option['exam_option_value'] as $exam_option_value) {
                              $this->db->query("INSERT INTO " . DB_PREFIX . "exam_option_value SET exam_option_value_id = '" . (int)$exam_option_value['exam_option_value_id'] . "', exam_option_id = '" . (int)$exam_option_id . "', exam_id = '" . (int)$exam_id . "', option_id = '" . (int)$exam_option['option_id'] . "', option_value_id = '" . (int)$exam_option_value['option_value_id'] . "', quantity = '" . (int)$exam_option_value['quantity'] . "', subtract = '" . (int)$exam_option_value['subtract'] . "', price = '" . (float)$exam_option_value['price'] . "', price_prefix = '" . $this->db->escape($exam_option_value['price_prefix']) . "', points = '" . (int)$exam_option_value['points'] . "', points_prefix = '" . $this->db->escape($exam_option_value['points_prefix']) . "', weight = '" . (float)$exam_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($exam_option_value['weight_prefix']) . "'");
                        }
                  }
            } else {
                  $this->db->query("INSERT INTO " . DB_PREFIX . "exam_option SET exam_option_id = '" . (int)$exam_option['exam_option_id'] . "', exam_id = '" . (int)$exam_id . "', option_id = '" . (int)$exam_option['option_id'] . "', value = '" . $this->db->escape($exam_option['value']) . "', required = '" . (int)$exam_option['required'] . "'");
            }
      }
}

// copyExam

$data['exam_option'] = $this->getExamOptions($exam_id);

// deleteExam

$this->db->query("DELETE FROM " . DB_PREFIX . "exam_option WHERE exam_id = '" . (int)$exam_id . "'");
$this->db->query("DELETE FROM " . DB_PREFIX . "exam_option_value WHERE exam_id = '" . (int)$exam_id . "'");

// Functions

public function getExamOptions($exam_id) {
      $exam_option_data = array();

      $exam_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "exam_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.exam_id = '" . (int)$exam_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order ASC");

      foreach ($exam_option_query->rows as $exam_option) {
            $exam_option_value_data = array();

            $exam_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "exam_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON(pov.option_value_id = ov.option_value_id) WHERE pov.exam_option_id = '" . (int)$exam_option['exam_option_id'] . "' ORDER BY ov.sort_order ASC");

            foreach ($exam_option_value_query->rows as $exam_option_value) {
                  $exam_option_value_data[] = array(
                        'exam_option_value_id' => $exam_option_value['exam_option_value_id'],
                        'option_value_id'         => $exam_option_value['option_value_id'],
                        'quantity'                => $exam_option_value['quantity'],
                        'subtract'                => $exam_option_value['subtract'],
                        'price'                   => $exam_option_value['price'],
                        'price_prefix'            => $exam_option_value['price_prefix'],
                        'points'                  => $exam_option_value['points'],
                        'points_prefix'           => $exam_option_value['points_prefix'],
                        'weight'                  => $exam_option_value['weight'],
                        'weight_prefix'           => $exam_option_value['weight_prefix']
                  );
            }

            $exam_option_data[] = array(
                  'exam_option_id'    => $exam_option['exam_option_id'],
                  'exam_option_value' => $exam_option_value_data,
                  'option_id'            => $exam_option['option_id'],
                  'name'                 => $exam_option['name'],
                  'type'                 => $exam_option['type'],
                  'value'                => $exam_option['value'],
                  'required'             => $exam_option['required']
            );
      }

      return $exam_option_data;
}

public function getExamOptionValue($exam_id, $exam_option_value_id) {
      $query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "exam_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.exam_id = '" . (int)$exam_id . "' AND pov.exam_option_value_id = '" . (int)$exam_option_value_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

      return $query->row;
}

public function getTotalExamsByOptionId($option_id) {
      $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "exam_option WHERE option_id = '" . (int)$option_id . "'");

      return $query->row['total'];
}

"UPDATE ( SELECT * FROM " . DB_PREFIX . "assessment a LEFT JOIN " . DB_PREFIX . "assessment_description ad ON (a.assessment_id = ad.assessment_id) WHERE ad.center_id = '" . (int)$center_id . "' AND ad.language_id = '" . (int)$language_id . "') SET ad.name = '" . $data['name'] . "', ad.description = '" . $data['description'] . "', a.model = '" . $data['city'] . "', a.location = '" . $data['location'] . "', a.quantity = '" . $data['capacity'] . "', a.date_modified = NOW()" ;

$sql = "SELECT ad.center_id AS center_id, ad.name AS name, ad.description AS description, 
a.model AS city, a.location AS location, a.quantity AS quantity, a.date_modified AS date_modified FROM "

"SELECT * FROM
" . DB_PREFIX . "assessment a LEFT JOIN 
" . DB_PREFIX . "assessment_description ad ON 
(a.assessment_id = ad.assessment_id) WHERE 
center_id = '" . (int)$center_id . "' AND
ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

