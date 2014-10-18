<?php

/*
 * Json data schema required property
 *		id: integer
 *		title: string						// A data should have a name(title).
 *		category: string				// A data should be able to classify.
 *		tag: string							// A data should be able to classify.
 *		status: string					// A data should have a status.
 *		updated_at: string
 *		created_at: string
 */
class JsonFile {
	public $datas;
	public $last_id;
	private $filepath;

	public function __construct($filepath) {
		$this->filepath = $filepath;
		if (file_exists($this->filepath)) {
			$this->datas = json_decode(file_get_contents($this->filepath), true);
			$this->last_id = 0;
			foreach ($this->datas as $data){
				if ($this->last_id < $data['id']) {
					$this->last_id = $data['id'];
				}
			}
		} else {
			$this->datas = array();
			$this->last_id = 0;
		}
	}

	public function property_counts($callback = null) {
		$property_counts = array(
			'category' => array(),
			'tag' => array(),
			'status' => array(),
		);
		foreach ($this->datas as $data){
			if (isset($callback) && !$callback($data)) {
				continue;
			}
			if (isset($data['category'])) {
				$this->property_countup($property_counts['category'], $data['category']);
			}
			if (isset($data['tag'])) {
				$this->property_countup($property_counts['tag'], $data['tag']);
			}
			if (isset($data['status'])) {
				$this->property_countup($property_counts['status'], $data['status']);
			}
		}
		foreach ($property_counts as &$property_count) {
			ksort($property_count);
		}
		return $property_counts;
	}

	private function property_countup(&$prop_counter, $value) {
		if (empty($value)) {
			return;
		}
		if (!isset($prop_counter[$value])) {
			$prop_counter[$value] = 0;
		}
		$prop_counter[$value]++;
	}

	public function reverse() {
		$this->datas = array_reverse($this->datas);
	}

	public function rsort($property) {
		usort($this->datas, function($a, $b) use ($property) {
			if ($a[$property] == $b[$property]) {
				return 0;
			}
			return ($a[$property] < $b[$property]) ? 1 : -1;
		});
	}

	public function add(&$data) {
		$this->last_id++;
		$data['id'] = $this->last_id;
		$data['created_at'] = $data['updated_at'] = date(DATE_ATOM);
		array_push($this->datas, $data);
	}

	public function update($new_data) {
		foreach ($this->datas as &$data){
			if ($data['id'] == $new_data['id']) {
				$data = $new_data;
				$data['updated_at'] = date(DATE_ATOM);
				return;
			}
		}
	}

	public function delete($ids) {
		$this->datas = array_filter($this->datas, function ($data) use ($ids) {
			return !in_array($data['id'], $ids);
		});
	}

	public function save() {
		return file_put_contents($this->filepath, json_encode($this->datas, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
	}
	
	public function find($id) {
		foreach ($this->datas as $data){
			if ($data['id'] == $id) {
				return $data;
			}
		}
		return NULL;
	}
	
	public function find_by_filter($callback) {
		return array_filter($this->datas, $callback);
	}

	public function getPagination($page, $limit, $callback = null) {
		$posts = $this->datas;
		if (isset($callback)) {
			$posts = $this->find_by_filter($callback);
		}
		$count = count($posts);
		if (ceil($count / $limit) < $page) {
			$page = ceil($count / $limit);
		}
		$offset = abs($page - 1) * $limit;
		$limited_posts = array_slice($posts, $offset, $limit);

		$pagination = new \Knp\Component\Pager\Pagination\SlidingPagination();
		$pagination->setCurrentPageNumber($page);
		$pagination->setItemNumberPerPage($limit);
		$pagination->setTotalItemCount($count);
		$pagination->setItems($limited_posts);
		$pagination->setCustomParameters(array());
		$pagination->setPaginatorOptions(array());

		return $pagination;
	}
}
