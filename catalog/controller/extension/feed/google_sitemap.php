<?php
class ControllerExtensionFeedGoogleSitemap extends Controller {
	public function index() {
		if ($this->config->get('feed_google_sitemap_status')) {
			$output  = '<?xml version="1.0" encoding="UTF-8"?>';
			$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

			$this->load->model('catalog/assessment');
			$this->load->model('tool/image');

			$assessments = $this->model_catalog_assessment->getAssessments();

			foreach ($assessments as $assessment) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->url->link('assessment/assessment', 'assessment_id=' . $assessment['assessment_id']) . '</loc>';
				$output .= '  <changefreq>weekly</changefreq>';
				$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($assessment['date_modified'])) . '</lastmod>';
				$output .= '  <priority>1.0</priority>';

				if ($assessment['image']) {
					$output .= '  <image:image>';
					$output .= '  <image:loc>' . $this->model_tool_image->resize($assessment['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')) . '</image:loc>';
					$output .= '  <image:caption>' . $assessment['name'] . '</image:caption>';
					$output .= '  <image:title>' . $assessment['name'] . '</image:title>';
					$output .= '  </image:image>';
				}

				$output .= '</url>';
			}

			$this->load->model('catalog/exam');

			$output .= $this->getExams(0);

			$this->load->model('catalog/manufacturer');

			$manufacturers = $this->model_catalog_manufacturer->getManufacturers();

			foreach ($manufacturers as $manufacturer) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->url->link('assessment/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']) . '</loc>';
				$output .= '  <changefreq>weekly</changefreq>';
				$output .= '  <priority>0.7</priority>';
				$output .= '</url>';
			}

			$this->load->model('catalog/information');

			$informations = $this->model_catalog_information->getInformations();

			foreach ($informations as $information) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->url->link('information/information', 'information_id=' . $information['information_id']) . '</loc>';
				$output .= '  <changefreq>weekly</changefreq>';
				$output .= '  <priority>0.5</priority>';
				$output .= '</url>';
			}

			$output .= '</urlset>';

			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($output);
		}
	}

	protected function getExams($parent_id) {
		$output = '';

		$results = $this->model_catalog_exam->getExams($parent_id);

		foreach ($results as $result) {
			$output .= '<url>';
			$output .= '  <loc>' . $this->url->link('assessment/exam', 'path=' . $result['exam_id']) . '</loc>';
			$output .= '  <changefreq>weekly</changefreq>';
			$output .= '  <priority>0.7</priority>';
			$output .= '</url>';

			$output .= $this->getExams($result['exam_id']);
		}

		return $output;
	}
}
