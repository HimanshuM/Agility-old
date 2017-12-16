<?php

namespace Agility\HTTP\Render;

	class View {

		public $title;
		public $js;
		public $css;

		private $_viewPath;

		private $_layout;
		private $_view;

		private $_data;

		function __construct($viewPath) {

			if (!is_null($viewPath)) {
				$this->_viewPath = $viewPath;
			}

		}

		function setLayout($layout) {

			if (!is_null($layout)) {
				$this->_layout = $this->_viewPath."/".$layout.".php";
			}

		}

		function setView($view) {

			if (is_null($view)) {
				throw new Exception("view cannot be null", 1);
			}

			$this->_view = $this->_viewPath."/".$view.".php";

		}

		function render($data = null) {

			$this->prepareData($data);

			$data = $this->renderView();
			if (!is_null($this->_layout)) {
				$this->renderLayout($data);
			}

			return $this->_data;

		}

		private function prepareData($data) {

			if (!is_null($data) && $data !== false) {

				if (!is_array($data)) {
					$data = ["data" => $data];
				}

			}
			else {
				$data = null;
			}

			$this->_data = $data;

		}

		private function renderView() {

			ob_start();

			if (!is_null($this->_data)) {
				extract($this->_data);
			}
			extract(["view" => $this]);

			require_once $this->_view;

			$data = ob_get_contents();
			ob_end_clean();

			return $data;

		}

		private function renderLayout($data) {

			ob_start();

			extract(["data" => $data]);
			extract(["view" => $this]);

			require_once $this->_layout;

			$data = ob_get_contents();
			ob_end_clean();

			$this->_data = $data;

		}

	}

?>