<?php
defined('_JEXEC') or die('Restricted access');

class ProximityController {

	private $_handlers;
	private $_viewPath;
	private $_modelPath;

	public function __construct($modelPath, $viewPath) {
		$this->_handlers = array();
		$this->_viewPath = $viewPath;
		$this->_modelPath = $modelPath;
	}
	
	private function defaultTask() {}
	
	public function setViewPath($viewPath) {
		$this->_viewPath = $viewPath;
	}

	public function setModelPath($modelPath) {
		$this->_modelPath = $modelPath;
	}

	public final function handle($task) {

		if (isset($this->_handlers[$task])) {
			$handler = $this->_handlers[$task];
			$this->$handler();
		} else if(is_callable(array($this, $task), false)) {
			$this->$task();
		} else {
			JError::raiseNotice(100, "Error handling task [$task]");
			$this->defaultTask();
		}
	}

	public function registerHandler($task, $method) {

		$this->_handlers[$task] = $method;
	}

	public function unregisterHandler($task) {

		unset($this->_handlers[$task]);
	}

	protected function getView($view, $suffix = "") {

		if (class_exists($view, true)) {

			return new $view;
		} else {

			return $this->generateObject($view, $this->_viewPath, $suffix);
		}
	}

	private function generateObject($className, $classFilePath, $suffix) {

		require_once($classFilePath."/".$className.$suffix.".class.php");

		$class = new ReflectionClass($className);

		$object = null;

		if ($class != null) {
			$object = $class->newInstance();
		}

		return $object;
	}

	protected function getModel($model, $suffix = "") {

		if (class_exists($model, true)) {

			return new $model;
		} else {
			return $this->generateObject($model, $this->_modelPath, $suffix);
		}
	}
}

?>