<?php

$query = $this->db->query
("SELECT DISTINCT 
*, pd.name AS name, p.image, m.name AS manufacturer,

(SELECT ed.name FROM " . DB_PREFIX . "assessment_to_exam ate LEFT JOIN " . DB_PREFIX . "exam_description ed ON (ate.exam_id = ed.exam_id) WHERE ate.assessment_id = '" . (int)$assessment_id . "' AND ed.language_id = '" . (int)$this->config->get('config_language_id') . "' ) AS exam,

(SELECT price FROM 
" . DB_PREFIX . "assessment_discount pd2 WHERE 
pd2.assessment_id = p.assessment_id ORDER BY 
pd2.priority ASC, pd2.price ASC LIMIT 1
) AS discount, 

(SELECT price FROM " . DB_PREFIX . "assessment_special ps WHERE 
ps.assessment_id = p.assessment_id ORDER BY 
ps.priority ASC, ps.price ASC LIMIT 1
) AS special, 

(SELECT points FROM " . DB_PREFIX . "assessment_reward pr WHERE 
pr.assessment_id = p.assessment_id 
) AS reward, 

(SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE 
ss.stock_status_id = p.stock_status_id 
) AS stock_status, 

(SELECT AVG(rating) AS total FROM 
" . DB_PREFIX . "review r1 WHERE 
r1.assessment_id = p.assessment_id GROUP BY 
r1.assessment_id
) AS rating, 

(SELECT COUNT(*) AS total FROM 
" . DB_PREFIX . "review r2 WHERE 
r2.assessment_id = p.assessment_id 
GROUP BY r2.assessment_id
) AS reviews, 

p.sort_order 

FROM 

" . DB_PREFIX . "assessment p LEFT JOIN 
" . DB_PREFIX . "assessment_description pd ON 
(p.assessment_id = pd.assessment_id) LEFT JOIN 
" . DB_PREFIX . "assessment_to_store p2s ON 
(p.assessment_id = p2s.assessment_id) LEFT JOIN 
" . DB_PREFIX . "manufacturer m ON 
(p.manufacturer_id = m.manufacturer_id) WHERE 
p.assessment_id = '" . (int)$assessment_id . "'


");

AND 
pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND 
p.status = '1' AND p.date_available <= NOW() AND 
p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
