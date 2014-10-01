<?php

class ImageDir {
	public $files = array();
	private $ctimes = array();
	private $dirpath;

	public function __construct($dirpath) {
		$this->dirpath = $dirpath;
		$dirfiles = glob("${dirpath}/*");
		foreach ($dirfiles as $dirfile) {
			if (is_file($dirfile)) {
				$this->files[] = basename($dirfile);
				$this->ctimes[] = filectime($dirfile);
			}
		}
		array_multisort($this->ctimes, SORT_DESC, $this->files);
	}

	public function getPagination($page, $limit) {
		$files = $this->files;
		$count = count($files);
		if (ceil($count / $limit) < $page) {
			$page = ceil($count / $limit);
		}
		$offset = abs($page - 1) * $limit;
		$limited_files = array_slice($files, $offset, $limit);

		$pagination = new \Knp\Component\Pager\Pagination\SlidingPagination();
		$pagination->setCurrentPageNumber($page);
		$pagination->setItemNumberPerPage($limit);
		$pagination->setTotalItemCount($count);
		$pagination->setItems($limited_files);
		$pagination->setCustomParameters(array());
		$pagination->setPaginatorOptions(array());

		return $pagination;
	}
}
