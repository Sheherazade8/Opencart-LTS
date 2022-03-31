<?php

// GetForm 

// Options
$this->load->model('catalog/option');

if (isset($this->request->post['exam_option'])) {
    $exam_options = $this->request->post['exam_option'];
} elseif (isset($this->request->get['exam_id'])) {
    $exam_options = $this->model_catalog_exam->getExamOptions($this->request->get['exam_id']);
} else {
    $exam_options = array();
}

$data['exam_options'] = array();

foreach ($exam_options as $exam_option) {
    $exam_option_value_data = array();

    if (isset($exam_option['exam_option_value'])) {
        foreach ($exam_option['exam_option_value'] as $exam_option_value) {
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
    }

    $data['exam_options'][] = array(
        'exam_option_id'    => $exam_option['exam_option_id'],
        'exam_option_value' => $exam_option_value_data,
        'option_id'            => $exam_option['option_id'],
        'name'                 => $exam_option['name'],
        'type'                 => $exam_option['type'],
        'value'                => isset($exam_option['value']) ? $exam_option['value'] : '',
        'required'             => $exam_option['required']
    );
}

$data['option_values'] = array();

foreach ($data['exam_options'] as $exam_option) {
    if ($exam_option['type'] == 'select' || $exam_option['type'] == 'radio' || $exam_option['type'] == 'checkbox' || $exam_option['type'] == 'image') {
        if (!isset($data['option_values'][$exam_option['option_id']])) {
            $data['option_values'][$exam_option['option_id']] = $this->model_catalog_option->getOptionValues($exam_option['option_id']);
        }
    }
}


// Autocomplete

    $this->load->model('catalog/option');


    $results = $this->model_catalog_exam->getExams($filter_data);

    foreach ($results as $result) {
        $option_data = array();

        $exam_options = $this->model_catalog_exam->getExamOptions($result['exam_id']);

        foreach ($exam_options as $exam_option) {
            $option_info = $this->model_catalog_option->getOption($exam_option['option_id']);

            if ($option_info) {
                $exam_option_value_data = array();

                foreach ($exam_option['exam_option_value'] as $exam_option_value) {
                    $option_value_info = $this->model_catalog_option->getOptionValue($exam_option_value['option_value_id']);

                    if ($option_value_info) {
                        $exam_option_value_data[] = array(
                            'exam_option_value_id' => $exam_option_value['exam_option_value_id'],
                            'option_value_id'         => $exam_option_value['option_value_id'],
                            'name'                    => $option_value_info['name'],
                            'price'                   => (float)$exam_option_value['price'] ? $this->currency->format($exam_option_value['price'], $this->config->get('config_currency')) : false,
                            'price_prefix'            => $exam_option_value['price_prefix']
                        );
                    }
                }

                $option_data[] = array(
                    'exam_option_id'    => $exam_option['exam_option_id'],
                    'exam_option_value' => $exam_option_value_data,
                    'option_id'            => $exam_option['option_id'],
                    'name'                 => $option_info['name'],
                    'type'                 => $option_info['type'],
                    'value'                => $exam_option['value'],
                    'required'             => $exam_option['required']
                );
            }
        }

        $json[] = array(
            'exam_id' => $result['exam_id'],
            // Nouveau code pour autocomplete le filtre exam
            'exam' => strip_tags(html_entity_decode($result['exam_name'], ENT_QUOTES, 'UTF-8')),
            'name'       => strip_tags(html_entity_decode($result['exam_name'], ENT_QUOTES, 'UTF-8')),
            'model'      => $result['model'],
            'option'     => $option_data,
            'price'      => $result['price']
        );
    }
}

$this->response->addHeader('Content-Type: application/json');
$this->response->setOutput(json_encode($json));

// Option.php

$this->load->model('catalog/exam');

		foreach ($this->request->post['selected'] as $option_id) {
			$exam_total = $this->model_catalog_exam->getTotalExamsByOptionId($option_id);

			if ($exam_total) {
				$this->error['warning'] = sprintf($this->language->get('error_exam'), $exam_total);
			}
		}