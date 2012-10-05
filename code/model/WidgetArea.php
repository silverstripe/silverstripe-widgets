<?php
/**
 * Represents a set of widgets shown on a page.
 * @package cms
 * @subpackage widgets
 */
class WidgetArea extends DataObject {
	
	public static $db = array();
	
	public static $has_one = array();
	
	public static $has_many = array(
		"Widgets" => "Widget"
	);
	
	public static $many_many = array();
	
	public static $belongs_many_many = array();
	
	public $template = __CLASS__;
	
	/**
	 * Used in template instead of {@link Widgets()}
	 * to wrap each widget in its controller, making
	 * it easier to access and process form logic
	 * and actions stored in {@link Widget_Controller}.
	 * 
	 * @return SS_List Collection of {@link Widget_Controller}
	 */
	public function WidgetControllers() {
		$controllers = new ArrayList();

		foreach($this->ItemsToRender() as $widget) {
			// find controller
			$controllerClass = '';
			foreach(array_reverse(ClassInfo::ancestry($widget->class)) as $widgetClass) {
				$controllerClass = "{$widgetClass}_Controller";
				if(class_exists($controllerClass)) break;
			}
			$controller = new $controllerClass($widget);
			$controller->init();
			$controllers->push($controller);
		}

		return $controllers;
	}
	
	public function Items() {
		return $this->getComponents('Widgets');
	}
	
	public function ItemsToRender() {
		return $this->getComponents('Widgets', "\"Widget\".\"Enabled\" = 1");
	}
	
	public function forTemplate() {
		return $this->renderWith($this->template); 
	}
	
	public function setTemplate($template) {
		$this->template = $template;
	}
	
	public function onBeforeDelete() {
		parent::onBeforeDelete();
		foreach($this->Widgets() as $widget) {
			$widget->delete();
		}
	}
}

