<?php

use \googleshopping\exception\Connection as ConnectionException;
use \googleshopping\Googleshopping;

class ModelExtensionAdvertiseGoogle extends Model {
    private $events = array(
        'admin/view/common/column_left/before' => array(
            'extension/advertise/google/admin_link',
        ),
        'admin/model/catalog/assessment/addAssessment/after' => array(
            'extension/advertise/google/addAssessment',
        ),
        'admin/model/catalog/assessment/copyAssessment/after' => array(
            'extension/advertise/google/copyAssessment',
        ),
        'admin/model/catalog/assessment/deleteAssessment/after' => array(
            'extension/advertise/google/deleteAssessment',
        ),
        'catalog/controller/checkout/success/before' => array(
            'extension/advertise/google/before_checkout_success'
        ),
        'catalog/view/common/header/after' => array(
            'extension/advertise/google/google_global_site_tag'
        ),
        'catalog/view/common/success/after' => array(
            'extension/advertise/google/google_dynamic_remarketing_purchase'
        ),
        'catalog/view/assessment/assessment/after' => array(
            'extension/advertise/google/google_dynamic_remarketing_assessment'
        ),
        'catalog/view/assessment/search/after' => array(
            'extension/advertise/google/google_dynamic_remarketing_searchresults'
        ),
        'catalog/view/assessment/exam/after' => array(
            'extension/advertise/google/google_dynamic_remarketing_exam'
        ),
        'catalog/view/common/home/after' => array(
            'extension/advertise/google/google_dynamic_remarketing_home'
        ),
        'catalog/view/checkout/cart/after' => array(
            'extension/advertise/google/google_dynamic_remarketing_cart'
        )
    );

    private $rename_tables = array(
        'advertise_google_target' => 'googleshopping_target',
        'exam_to_google_assessment_exam' => 'googleshopping_exam',
        'assessment_advertise_google_status' => 'googleshopping_assessment_status',
        'assessment_advertise_google_target' => 'googleshopping_assessment_target',
        'assessment_advertise_google' => 'googleshopping_assessment'
    );

    private $table_columns = array(
        'googleshopping_target' => array(
            'advertise_google_target_id',
            'store_id',
            'campaign_name',
            'country',
            'budget',
            'feeds',
            'status'
        ),
        'googleshopping_exam' => array(
            'google_assessment_exam',
            'store_id',
            'exam_id'
        ),
        'googleshopping_assessment_status' => array(
            'assessment_id',
            'store_id',
            'assessment_variation_id',
            'destination_statuses',
            'data_quality_issues',
            'item_level_issues',
            'google_expiration_date'
        ),
        'googleshopping_assessment_target' => array(
            'assessment_id',
            'store_id',
            'advertise_google_target_id'
        ),
        'googleshopping_assessment' => array(
            'assessment_advertise_google_id',
            'assessment_id',
            'store_id',
            'has_issues',
            'destination_status',
            'impressions',
            'clicks',
            'conversions',
            'cost',
            'conversion_value',
            'google_assessment_exam',
            'condition',
            'adult',
            'multipack',
            'is_bundle',
            'age_group',
            'color',
            'gender',
            'size_type',
            'size_system',
            'size',
            'is_modified'
        )
    );

    public function isAppIdUsed($app_id, $store_id) {
        $sql = "SELECT `store_id` FROM `" . DB_PREFIX . "setting` WHERE `key`='advertise_google_app_id' AND `value`='" . $this->db->escape($store_id) . "' AND `store_id`!=" . (int)$store_id . " LIMIT 1";

        $result = $this->db->query($sql);

        if ($result->num_rows > 0) {
            try {
                $googleshopping = new Googleshopping($this->registry, (int)$result->row['store_id']);

                return $googleshopping->isConnected();
            } catch (\RuntimeException $e) {
                return false;
            }
        }

        return false;
    }

    public function getFinalAssessmentId() {
        $sql = "SELECT assessment_id FROM `" . DB_PREFIX . "assessment` ORDER BY assessment_id DESC LIMIT 1";

        $result = $this->db->query($sql);

        if ($result->num_rows > 0) {
            return (int)$result->row['assessment_id'];
        }

        return null;
    }

    public function isAnyAssessmentExamModified($store_id) {
        $sql = "SELECT pag.is_modified FROM `" . DB_PREFIX . "googleshopping_assessment` pag WHERE pag.google_assessment_exam IS NOT NULL AND pag.store_id=" . (int)$store_id . " LIMIT 0,1";

        return $this->db->query($sql)->num_rows > 0;
    }

    public function getAdvertisedCount($store_id) {
        $result = $this->db->query("SELECT COUNT(assessment_id) as total FROM `" . DB_PREFIX . "googleshopping_assessment_target` WHERE store_id=" . (int)$store_id . " GROUP BY `assessment_id`");

        return $result->num_rows > 0 ? (int)$result->row['total'] : 0;
    }

    public function getMapping($store_id) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "googleshopping_exam` WHERE store_id=" . (int)$store_id;

        return $this->db->query($sql)->rows;
    }

    public function setExamMapping($google_assessment_exam, $store_id, $exam_id) {
        $sql = "INSERT INTO `" . DB_PREFIX . "googleshopping_exam` SET `google_assessment_exam`='" . $this->db->escape($google_assessment_exam) . "', `store_id`=" . (int)$store_id . ", `exam_id`=" . (int)$exam_id . " ON DUPLICATE KEY UPDATE `exam_id`=" . (int)$exam_id;

        $this->db->query($sql);
    }

    public function getMappedExam($google_assessment_exam, $store_id) {
        $sql = "SELECT GROUP_CONCAT(e.name ORDER BY ep.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, ep.exam_id FROM " . DB_PREFIX . "exam_path ep LEFT JOIN " . DB_PREFIX . "exam e ON (ep.path_id = e.exam_id) LEFT JOIN `" . DB_PREFIX . "googleshopping_exam` c2gpc ON (c2gpc.exam_id = ep.exam_id) WHERE c2gpc.google_assessment_exam='" . $this->db->escape($google_assessment_exam) . "' AND c2gpc.store_id=" . (int)$store_id;

        $result = $this->db->query($sql);

        if ($result->num_rows > 0) {
            return $result->row;
        }

        return null;
    }

    public function getAssessmentByAssessmentAdvertiseGoogleId($assessment_advertise_google_id) {
        $sql = "SELECT pag.assessment_id FROM `" . DB_PREFIX . "googleshopping_assessment` pag WHERE pag.assessment_advertise_google_id=" . (int)$assessment_advertise_google_id;

        $result = $this->db->query($sql);

        if ($result->num_rows) {
            $this->load->model('catalog/assessment');

            return $this->model_catalog_assessment->getAssessment($result->row['assessment_id']);
        }
    }

    public function getAssessmentAdvertiseGoogle($assessment_advertise_google_id) {
        $sql = "SELECT pag.* FROM `" . DB_PREFIX . "googleshopping_assessment` pag WHERE pag.assessment_advertise_google_id=" . (int)$assessment_advertise_google_id;

        return $this->db->query($sql)->row;
    }

    public function hasActiveTarget($store_id) {
        $sql = "SELECT agt.advertise_google_target_id FROM `" . DB_PREFIX . "googleshopping_target` agt WHERE agt.store_id=" . (int)$store_id . " AND agt.status='active' LIMIT 1";

        return $this->db->query($sql)->num_rows > 0;
    }

    public function getRequiredFieldsByAssessmentIds($assessment_ids, $store_id) {
        $this->load->config('googleshopping/googleshopping');

        $result = array();
        $countries = $this->getTargetCountriesByAssessmentIds($assessment_ids, $store_id);

        foreach ($countries as $country) {
            foreach ($this->config->get('advertise_google_country_required_fields') as $field => $requirements) {
                if (
                    (!empty($requirements['countries']) && in_array($country, $requirements['countries']))
                        ||
                    (is_array($requirements['countries']) && empty($requirements['countries']))
                ) {
                    $result[$field] = $requirements;
                }
            }
        }

        return $result;
    }

    public function getRequiredFieldsByFilter($data, $store_id) {
        $this->load->config('googleshopping/googleshopping');

        $result = array();
        $countries = $this->getTargetCountriesByFilter($data, $store_id);

        foreach ($countries as $country) {
            foreach ($this->config->get('advertise_google_country_required_fields') as $field => $requirements) {
                if (
                    (!empty($requirements['countries']) && in_array($country, $requirements['countries']))
                        ||
                    (is_array($requirements['countries']) && empty($requirements['countries']))
                ) {
                    $result[$field] = $requirements;
                }
            }
        }

        return $result;
    }

    public function getTargetCountriesByAssessmentIds($assessment_ids, $store_id) {
        $sql = "SELECT DISTINCT agt.country FROM `" . DB_PREFIX . "googleshopping_assessment_target` pagt LEFT JOIN `" . DB_PREFIX . "googleshopping_target` agt ON (agt.advertise_google_target_id = pagt.advertise_google_target_id AND agt.store_id = pagt.store_id) WHERE pagt.assessment_id IN (" . $this->googleshopping->assessmentIdsToIntegerExpression($assessment_ids) . ") AND pagt.store_id=" . (int)$store_id;

        return array_map(array($this, 'country'), $this->db->query($sql)->rows);
    }

    public function getTargetCountriesByFilter($data, $store_id) {
        $sql = "SELECT DISTINCT agt.country FROM `" . DB_PREFIX . "googleshopping_assessment_target` pagt LEFT JOIN `" . DB_PREFIX . "googleshopping_target` agt ON (agt.advertise_google_target_id = pagt.advertise_google_target_id AND agt.store_id = pagt.store_id) LEFT JOIN `" . DB_PREFIX . "assessment` p ON (pagt.assessment_id = p.assessment_id) LEFT JOIN `" . DB_PREFIX . "assessment_description` ad ON (ad.assessment_id = pagt.assessment_id) WHERE pagt.store_id=" . (int)$store_id . " AND ad.language_id=" . (int)$this->config->get('config_language_id');

        $this->googleshopping->applyFilter($sql, $data);

        return array_map(array($this, 'country'), $this->db->query($sql)->rows);
    }

    public function getAssessmentOptionsByAssessmentIds($assessment_ids) {
        $sql = "SELECT po.option_id, od.name FROM `" . DB_PREFIX . "assessment_option` po LEFT JOIN `" . DB_PREFIX . "option_description` od ON (od.option_id=po.option_id AND od.language_id=" . (int)$this->config->get('config_language_id') . ") LEFT JOIN `" . DB_PREFIX . "option` o ON (o.option_id = po.option_id) WHERE o.type IN ('select', 'radio') AND po.assessment_id IN (" . $this->googleshopping->assessmentIdsToIntegerExpression($assessment_ids) . ")";

        return $this->db->query($sql)->rows;
    }

    public function getAssessmentOptionsByFilter($data) {
        $sql = "SELECT DISTINCT po.option_id, od.name FROM `" . DB_PREFIX . "assessment_option` po LEFT JOIN `" . DB_PREFIX . "option_description` od ON (od.option_id=po.option_id AND od.language_id=" . (int)$this->config->get('config_language_id') . ") LEFT JOIN `" . DB_PREFIX . "option` o ON (o.option_id = po.option_id) LEFT JOIN `" . DB_PREFIX . "assessment` p ON (po.assessment_id = p.assessment_id) LEFT JOIN `" . DB_PREFIX . "assessment_description` ad ON (ad.assessment_id = po.assessment_id) WHERE o.type IN ('select', 'radio') AND ad.language_id=" . (int)$this->config->get('config_language_id');

        $this->googleshopping->applyFilter($sql, $data);

        return $this->db->query($sql)->rows;
    }

    public function addTarget($target, $store_id) {
        $sql = "INSERT INTO `" . DB_PREFIX . "googleshopping_target` SET `store_id`=" . (int)$store_id . ", `campaign_name`='" . $this->db->escape($target['campaign_name']) . "', `country`='" . $this->db->escape($target['country']) . "', `budget`='" . (float)$target['budget'] . "', `feeds`='" . $this->db->escape(json_encode($target['feeds'])) . "', `date_added`=NOW(), `roas`=" . (int)$target['roas'] . " , `status`='" . $this->db->escape($target['status']) . "'";

        $this->db->query($sql);

        return $this->db->getLastId();
    }

    public function deleteAssessments($assessment_ids) {
        $sql = "DELETE FROM `" . DB_PREFIX . "googleshopping_assessment` WHERE `assessment_id` IN (" . $this->googleshopping->assessmentIdsToIntegerExpression($assessment_ids) . ")";

        $this->db->query($sql);

        $sql = "DELETE FROM `" . DB_PREFIX . "googleshopping_assessment_target` WHERE `assessment_id` IN (" . $this->googleshopping->assessmentIdsToIntegerExpression($assessment_ids) . ")";

        $this->db->query($sql);

        $sql = "DELETE FROM `" . DB_PREFIX . "googleshopping_assessment_status` WHERE `assessment_id` IN (" . $this->googleshopping->assessmentIdsToIntegerExpression($assessment_ids) . ")";

        $this->db->query($sql);

        return true;
    }

    public function setAdvertisingBySelect($post_assessment_ids, $post_target_ids, $store_id) {
        if (!empty($post_assessment_ids)) {
            $assessment_ids = array_map(array($this->googleshopping, 'integer'), $post_assessment_ids);

            $assessment_ids_expression = implode(',', $assessment_ids);

            $this->db->query("DELETE FROM `" . DB_PREFIX . "googleshopping_assessment_target` WHERE assessment_id IN (" . $assessment_ids_expression . ") AND store_id=" . (int)$store_id);

            if (!empty($post_target_ids)) {
                $target_ids = array_map(array($this->googleshopping, 'integer'), $post_target_ids);

                $values = array();

                foreach ($assessment_ids as $assessment_id) {
                    foreach ($target_ids as $target_id) {
                        $values[] = '(' . $assessment_id . ',' . $store_id . ',' . $target_id . ')';
                    }
                }

                $sql = "INSERT INTO `" . DB_PREFIX . "googleshopping_assessment_target` (`assessment_id`, `store_id`, `advertise_google_target_id`) VALUES " . implode(',', $values);

                $this->db->query($sql);
            }
        }
    }

    public function setAdvertisingByFilter($data, $post_target_ids, $store_id) {
        $sql = "DELETE pagt FROM `" . DB_PREFIX . "googleshopping_assessment_target` pagt LEFT JOIN `" . DB_PREFIX . "assessment` p ON (pagt.assessment_id = p.assessment_id) LEFT JOIN `" . DB_PREFIX . "assessment_description` ad ON (ad.assessment_id = p.assessment_id) WHERE ad.language_id=" . (int)$this->config->get('config_language_id');

        $this->googleshopping->applyFilter($sql, $data);

        $this->db->query($sql);

        if (!empty($post_target_ids)) {
            $target_ids = array_map(array($this->googleshopping, 'integer'), $post_target_ids);

            $insert_sql = "SELECT p.assessment_id, " . (int)$store_id . " as store_id, '{TARGET_ID}' as advertise_google_target_id FROM `" . DB_PREFIX . "assessment` p LEFT JOIN `" . DB_PREFIX . "assessment_description` ad ON (ad.assessment_id = p.assessment_id) WHERE ad.language_id=" . (int)$this->config->get('config_language_id');

            $this->googleshopping->applyFilter($insert_sql, $data);

            foreach ($target_ids as $target_id) {
                $sql = "INSERT INTO `" . DB_PREFIX . "googleshopping_assessment_target` (`assessment_id`, `store_id`, `advertise_google_target_id`) " . str_replace('{TARGET_ID}', (string)$target_id, $insert_sql);

                $this->db->query($sql);
            }
        }
    }

    public function insertNewAssessments($assessment_ids = array(), $store_id) {
        $sql = "INSERT INTO `" . DB_PREFIX . "googleshopping_assessment` (`assessment_id`, `store_id`, `google_assessment_exam`) SELECT p.assessment_id, p2s.store_id, (SELECT c2gpc.google_assessment_exam FROM `" . DB_PREFIX . "assessment_to_exam` p2c LEFT JOIN `" . DB_PREFIX . "exam_path` ep ON (p2c.exam_id = ep.exam_id) LEFT JOIN `" . DB_PREFIX . "googleshopping_exam` c2gpc ON (c2gpc.exam_id = ep.path_id AND c2gpc.store_id = " . (int)$store_id . ") WHERE p2c.assessment_id = p.assessment_id AND c2gpc.google_assessment_exam IS NOT NULL ORDER BY ep.level DESC LIMIT 0,1) as `google_assessment_exam` FROM `" . DB_PREFIX . "assessment` p LEFT JOIN `" . DB_PREFIX . "assessment_to_store` p2s ON (p2s.assessment_id = p.assessment_id AND p2s.store_id = " . (int)$store_id . ") LEFT JOIN `" . DB_PREFIX . "googleshopping_assessment` pag ON (pag.assessment_id = p.assessment_id AND pag.store_id=p2s.store_id) WHERE pag.assessment_id IS NULL AND p2s.store_id IS NOT NULL";

        if (!empty($assessment_ids)) {
            $sql .= " AND p.assessment_id IN (" . $this->googleshopping->assessmentIdsToIntegerExpression($assessment_ids) . ")";
        }

        $this->db->query($sql);
    }

    public function updateGoogleAssessmentExamMapping($store_id) {
        $sql = "INSERT INTO `" . DB_PREFIX . "googleshopping_assessment` (`assessment_id`, `store_id`, `google_assessment_exam`) SELECT p.assessment_id, " . (int)$store_id . " as store_id, (SELECT c2gpc.google_assessment_exam FROM `" . DB_PREFIX . "assessment_to_exam` p2c LEFT JOIN `" . DB_PREFIX . "exam_path` cp ON (p2c.exam_id = cp.exam_id) LEFT JOIN `" . DB_PREFIX . "googleshopping_exam` c2gpc ON (c2gpc.exam_id = cp.path_id AND c2gpc.store_id = " . (int)$store_id . ") WHERE p2c.assessment_id = p.assessment_id AND c2gpc.google_assessment_exam IS NOT NULL ORDER BY cp.level DESC LIMIT 0,1) as `google_assessment_exam` FROM `" . DB_PREFIX . "assessment` p LEFT JOIN `" . DB_PREFIX . "googleshopping_assessment` pag ON (pag.assessment_id = p.assessment_id) WHERE pag.assessment_id IS NOT NULL ON DUPLICATE KEY UPDATE `google_assessment_exam`=VALUES(`google_assessment_exam`)";

        $this->db->query($sql);
    }

    public function updateSingleAssessmentFields($data) {
        $values = array();

        $entry = array();
        $entry['assessment_id'] = (int)$data['assessment_id'];
        $entry = array_merge($entry, $this->makeInsertData($data));

        $values[] = "(" . implode(",", $entry) . ")";

        $sql = "INSERT INTO `" . DB_PREFIX . "googleshopping_assessment` (`assessment_id`, `store_id`, `google_assessment_exam`, `condition`, `adult`, `multipack`, `is_bundle`, `age_group`, `color`, `gender`, `size_type`, `size_system`, `size`, `is_modified`) VALUES " . implode(',', $values) . " ON DUPLICATE KEY UPDATE " . $this->makeOnDuplicateKeyData();

        $this->db->query($sql);
    }

    public function updateMultipleAssessmentFields($filter_data, $data) {
        $insert_sql = "SELECT p.assessment_id, {INSERT_DATA} FROM `" . DB_PREFIX . "assessment` p LEFT JOIN `" . DB_PREFIX . "assessment_description` ad ON (ad.assessment_id = p.assessment_id) WHERE ad.language_id=" . (int)$this->config->get('config_language_id');

        $this->googleshopping->applyFilter($insert_sql, $filter_data);

        $insert_data = array();
        $keys[] = "`assessment_id`";

        foreach ($this->makeInsertData($data) as $key => $value) {
            $insert_data[] = $value . " as `" . $key . "`";
            $keys[] = "`" . $key . "`";
        }

        $sql = "INSERT INTO `" . DB_PREFIX . "googleshopping_assessment` (" . implode(", ", $keys) . ") " . str_replace('{INSERT_DATA}', implode(", ", $insert_data), $insert_sql) . " ON DUPLICATE KEY UPDATE " . $this->makeOnDuplicateKeyData();

        $this->db->query($sql);
    }

    protected function makeInsertData($data) {
        $insert_data = array();

        $insert_data['store_id'] = (int)$data['store_id'];
        $insert_data['google_assessment_exam'] = "'" . $this->db->escape($data['google_assessment_exam']) . "'";
        $insert_data['condition'] = "'" . $this->db->escape($data['condition']) . "'";
        $insert_data['adult'] = (int)$data['adult'];
        $insert_data['multipack'] = (int)$data['multipack'];
        $insert_data['is_bundle'] = (int)$data['is_bundle'];
        $insert_data['age_group'] = "'" . $this->db->escape($data['age_group']) . "'";
        $insert_data['color'] = (int)$data['color'];
        $insert_data['gender'] = "'" . $this->db->escape($data['gender']) . "'";
        $insert_data['size_type'] = "'" . $this->db->escape($data['size_type']) . "'";
        $insert_data['size_system'] = "'" . $this->db->escape($data['size_system']) . "'";
        $insert_data['size'] = (int)$data['size'];
        $insert_data['is_modified'] = 1;

        return $insert_data;
    }

    protected function makeOnDuplicateKeyData() {
        return "`google_assessment_exam`=VALUES(`google_assessment_exam`), `condition`=VALUES(`condition`), `adult`=VALUES(`adult`), `multipack`=VALUES(`multipack`), `is_bundle`=VALUES(`is_bundle`), `age_group`=VALUES(`age_group`), `color`=VALUES(`color`), `gender`=VALUES(`gender`), `size_type`=VALUES(`size_type`), `size_system`=VALUES(`size_system`), `size`=VALUES(`size`), `is_modified`=VALUES(`is_modified`)";
    }

    public function getExams($data = array(), $store_id) {
        $sql = "SELECT cp.exam_id AS exam_id, GROUP_CONCAT(e1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "exam_path cp LEFT JOIN `" . DB_PREFIX . "exam_to_store` c2s ON (c2s.exam_id = cp.exam_id AND c2s.store_id=" . (int)$store_id . ") LEFT JOIN " . DB_PREFIX . "exam c1 ON (cp.exam_id = c1.exam_id) LEFT JOIN " . DB_PREFIX . "exam c2 ON (cp.path_id = c2.exam_id) LEFT JOIN " . DB_PREFIX . "exam e1 ON (cp.path_id = e1.exam_id) LEFT JOIN " . DB_PREFIX . "exam e2 ON (cp.exam_id = e2.exam_id) WHERE c2s.store_id IS NOT NULL";

        if (!empty($data['filter_name'])) {
            $sql .= " AND e2.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sql .= " GROUP BY cp.exam_id";

        $sort_data = array(
            'name',
            'sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY sort_order";
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

    public function getAssessmentCampaigns($assessment_id, $store_id) {
        $sql = "SELECT agt.advertise_google_target_id, agt.campaign_name FROM `" . DB_PREFIX . "googleshopping_assessment_target` pagt LEFT JOIN `" . DB_PREFIX . "googleshopping_target` agt ON (pagt.advertise_google_target_id = agt.advertise_google_target_id) WHERE pagt.assessment_id=" . (int)$assessment_id . " AND pagt.store_id=" . (int)$store_id;

        return $this->db->query($sql)->rows;
    }

    public function getAssessmentIssues($assessment_id, $store_id) {
        $this->load->model('localisation/language');

        $sql = "SELECT pag.color, pag.size, p.name, p.model FROM `" . DB_PREFIX . "googleshopping_assessment` pag LEFT JOIN `" . DB_PREFIX . "assessment` p ON (p.assessment_id = pag.assessment_id) LEFT JOIN `" . DB_PREFIX . "assessment_description` ad ON (ad.assessment_id = pag.assessment_id AND ad.language_id=" . (int)$this->config->get('config_language_id') . ") WHERE pag.assessment_id=" . (int)$assessment_id . " AND pag.store_id=" . (int)$store_id;

        $assessment_info = $this->db->query($sql)->row;

        if (!empty($assessment_info)) {
            $result = array();
            $result['name'] = $assessment_info['name'];
            $result['model'] = $assessment_info['model'];
            $result['entries'] = array();

            foreach ($this->model_localisation_language->getLanguages() as $language) {
                $language_id = $language['language_id'];
                $groups = $this->googleshopping->getGroups($assessment_id, $language_id, $assessment_info['color'], $assessment_info['size']);

                $result['entries'][$language_id] = array(
                    'language_name' => $language['name'],
                    'issues' => array()
                );

                foreach ($groups as $id => $group) {
                    $issues = $this->db->query("SELECT * FROM `" . DB_PREFIX . "googleshopping_assessment_status` WHERE assessment_id=" . (int)$assessment_id . " AND store_id=" . (int)$store_id . " AND assessment_variation_id='" . $this->db->escape($id) . "'")->row;

                    $destination_statuses = !empty($issues['destination_statuses']) ? json_decode($issues['destination_statuses'], true) : array();
                    $data_quality_issues = !empty($issues['data_quality_issues']) ? json_decode($issues['data_quality_issues'], true) : array();
                    $item_level_issues = !empty($issues['item_level_issues']) ? json_decode($issues['item_level_issues'], true) : array();
                    $google_expiration_date = !empty($issues['google_expiration_date']) ? date($this->language->get('datetime_format'), $issues['google_expiration_date']) : $this->language->get('text_na');

                    $result['entries'][$language_id]['issues'][] = array(
                        'color' => $group['color'] != "" ? $group['color'] : $this->language->get('text_na'),
                        'size' => $group['size'] != "" ? $group['size'] : $this->language->get('text_na'),
                        'destination_statuses' => $destination_statuses,
                        'data_quality_issues' => $data_quality_issues,
                        'item_level_issues' => $item_level_issues,
                        'google_expiration_date' => $google_expiration_date
                    );
                }
            }

            return $result;
        }

        return null;
    }

    /*
     * Shortly after releasing the extension, 
     * we learned that the table names are actually 
     * clashing with third-party extensions. 
     * Hence, this renaming script was created.
     */
    public function renameTables() {
        foreach ($this->rename_tables as $old_table => $new_table) {
            $new_table_name = DB_PREFIX . $new_table;
            $old_table_name = DB_PREFIX . $old_table;

            if ($this->tableExists($old_table_name) && !$this->tableExists($new_table_name) && $this->tableColumnsMatch($old_table_name, $this->table_columns[$new_table])) {
                $this->db->query("RENAME TABLE `" . $old_table_name . "` TO `" . $new_table_name . "`");
            }
        }
    }

    private function tableExists($table) {
        return $this->db->query("SHOW TABLES LIKE '" . $table . "'")->num_rows > 0;
    }

    private function tableColumnsMatch($table, $columns) {
        $num_columns = $this->db->query("SHOW COLUMNS FROM `" . $table . "` WHERE Field IN (" . implode(',', $this->wrap($columns, '"')) . ")")->num_rows;

        return $num_columns == count($columns);
    }

    private function wrap($text, $char) {
        if (is_array($text)) {
            foreach ($text as &$string) {
                $string = $char . $string . $char;
            }

            return $text;
        } else {
            return $char . $text . $char;
        }
    }

    public function createTables() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "googleshopping_assessment` (
            `assessment_advertise_google_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `assessment_id` INT(11),
            `store_id` INT(11) NOT NULL DEFAULT '0',
            `has_issues` TINYINT(1),
            `destination_status` ENUM('pending','approved','disapproved') NOT NULL DEFAULT 'pending',
            `impressions` INT(11) NOT NULL DEFAULT '0',
            `clicks` INT(11) NOT NULL DEFAULT '0',
            `conversions` INT(11) NOT NULL DEFAULT '0.0000',
            `cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
            `conversion_value` decimal(15,4) NOT NULL DEFAULT '0.0000',
            `google_assessment_exam` VARCHAR(10),
            `condition` ENUM('new','refurbished','used'),
            `adult` TINYINT(1),
            `multipack` INT(11),
            `is_bundle` TINYINT(1),
            `age_group` ENUM('newborn','infant','toddler','kids','adult'),
            `color` INT(11),
            `gender` ENUM('male','female','unisex'),
            `size_type` ENUM('regular','petite','plus','big and tall','maternity'),
            `size_system` ENUM('AU','BR','CN','DE','EU','FR','IT','JP','MEX','UK','US'),
            `size` INT(11),
            `is_modified` TINYINT(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`assessment_advertise_google_id`),
            UNIQUE `assessment_id_store_id` (`assessment_id`, `store_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "googleshopping_assessment_status` (
            `assessment_id` INT(11),
            `store_id` INT(11) NOT NULL DEFAULT '0',
            `assessment_variation_id` varchar(64),
            `destination_statuses` TEXT NOT NULL,
            `data_quality_issues` TEXT NOT NULL,
            `item_level_issues` TEXT NOT NULL,
            `google_expiration_date` INT(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`assessment_id`, `store_id`, `assessment_variation_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "googleshopping_assessment_target` (
            `assessment_id` INT(11) NOT NULL,
            `store_id` INT(11) NOT NULL DEFAULT '0',
            `advertise_google_target_id` INT(11) UNSIGNED NOT NULL,
            PRIMARY KEY (`assessment_id`, `advertise_google_target_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "googleshopping_exam` (
            `google_assessment_exam` VARCHAR(10) NOT NULL,
            `store_id` INT(11) NOT NULL DEFAULT '0',
            `exam_id` INT(11) NOT NULL,
            INDEX `exam_id_store_id` (`exam_id`, `store_id`),
            PRIMARY KEY (`google_assessment_exam`, `store_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "googleshopping_target` (
            `advertise_google_target_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `store_id` INT(11) NOT NULL DEFAULT '0',
            `campaign_name` varchar(255) NOT NULL DEFAULT '',
            `country` varchar(2) NOT NULL DEFAULT '',
            `budget` decimal(15,4) NOT NULL DEFAULT '0.0000',
            `feeds` text NOT NULL,
            `date_added` DATE,
            `roas` INT(11) NOT NULL DEFAULT '0',
            `status` ENUM('paused','active') NOT NULL DEFAULT 'paused',
            INDEX `store_id` (`store_id`),
            PRIMARY KEY (`advertise_google_target_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
    }

    public function fixColumns() {
        $has_auto_increment = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "googleshopping_assessment` WHERE Field='assessment_advertise_google_id' AND Extra LIKE '%auto_increment%'")->num_rows > 0;

        if (!$has_auto_increment) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "googleshopping_assessment MODIFY COLUMN assessment_advertise_google_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT");
        }

        $has_unique_key = $this->db->query("SHOW INDEX FROM `" . DB_PREFIX . "googleshopping_assessment` WHERE Key_name='assessment_id_store_id' AND Non_unique=0")->num_rows == 2;

        if (!$has_unique_key) {
            $index_exists = $this->db->query("SHOW INDEX FROM `" . DB_PREFIX . "googleshopping_assessment` WHERE Key_name='assessment_id_store_id'")->num_rows > 0;

            if ($index_exists) {
                $this->db->query("ALTER TABLE `" . DB_PREFIX . "googleshopping_assessment` DROP INDEX assessment_id_store_id;");
            }

            $this->db->query("CREATE UNIQUE INDEX assessment_id_store_id ON `" . DB_PREFIX . "googleshopping_assessment` (assessment_id, store_id)");
        }

        $has_date_added_column = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "googleshopping_target` WHERE Field='date_added'")->num_rows > 0;

        if (!$has_date_added_column) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "googleshopping_target ADD COLUMN date_added DATE");

            $this->db->query("UPDATE " . DB_PREFIX . "googleshopping_target SET date_added = NOW() WHERE date_added IS NULL");
        }

        $has_roas_column = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "googleshopping_target` WHERE Field='roas'")->num_rows > 0;

        if (!$has_roas_column) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "googleshopping_target ADD COLUMN roas INT(11) NOT NULL DEFAULT '0'");
        }
    }

    public function dropTables() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "googleshopping_target`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "googleshopping_exam`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "googleshopping_assessment_status`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "googleshopping_assessment_target`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "googleshopping_assessment`");
    }

    public function deleteEvents() {
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('advertise_google');
    }

    public function createEvents() {
        $this->load->model('setting/event');

        foreach ($this->events as $trigger => $actions) {
            foreach ($actions as $action) {
                $this->model_setting_event->addEvent('advertise_google', $trigger, $action, 1, 0);
            }
        }
    }

    public function getAllowedTargets() {
        $this->load->config('googleshopping/googleshopping');

        $result = array();

        foreach ($this->config->get('advertise_google_targets') as $target) {
            $result[] = array(
                'country' => array(
                    'code' => $target['country'],
                    'name' => $this->googleshopping->getCountryName($target['country'])
                ),
                'languages' => $this->googleshopping->getLanguages($target['languages']),
                'currencies' => $this->googleshopping->getCurrencies($target['currencies'])
            );
        }

        return $result;
    }

    protected function country($row) {
        return $row['country'];
    }
}
