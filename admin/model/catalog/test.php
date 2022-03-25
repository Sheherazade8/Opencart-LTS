
		$sql = "SELECT cp.exam_id AS exam_id, GROUP_CONCAT(
            cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS 
            name, c1.parent_id, c1.sort_order, cd1.price AS price FROM 
            oc_exam_path cp LEFT JOIN 
            oc_exam c1 ON 
            (cp.exam_id = c1.exam_id) LEFT JOIN 
            oc_exam c2 ON 
            (cp.path_id = c2.exam_id) LEFT JOIN 
            oc_exam_description cd1 ON 
            (cp.path_id = cd1.exam_id) LEFT JOIN 
            oc_exam_description cd2 ON 
            (cp.exam_id = cd2.exam_id) WHERE 
            cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND 
            cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

            $sql = "SELECT * FROM oc_assessment p LEFT JOIN 
            oc_assessment_description pd ON 
            (p.assessment_id = pd.assessment_id) WHERE 
            pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

            $query = $this->db->query(
                  "SELECT * FROM oc_assessment p LEFT JOIN
                  oc_assessment_description pd ON 
                  (p.assessment_id = pd.assessment_id) LEFT JOIN 
                  oc_assessment_to_exam p2c ON 
                  (p.assessment_id = p2c.assessment_id) WHERE 
                  pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND 
                  p2c.exam_id = '" . (int)$exam_id . "' *
                  ORDER BY pd.name ASC");


