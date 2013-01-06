<?php
/**
 * Add this to ContentController to enable widgets
 */
class WidgetContentControllerExtension extends Extension {

	/**
	 *
	 * @var array
	 */
	public static $allowed_actions = array(
		'handleWidget'
	);
	
	/**
	 * Handles widgets attached to a page through one or more {@link WidgetArea} elements.
	 * Iterated through each $has_one relation with a {@link WidgetArea}
	 * and looks for connected widgets by their database identifier.
	 * Assumes URLs in the following format: <URLSegment>/widget/<Widget-ID>.
	 * 
	 * @return RequestHandler
	 */
	public function handleWidget() {
		$SQL_id = $this->owner->getRequest()->param('ID');
		if(!$SQL_id) return false;
		
		// find WidgetArea relations
		$widgetAreaRelations = array();
		$hasOnes = $this->owner->data()->has_one();
		if(!$hasOnes) return false;
		foreach($hasOnes as $hasOneName => $hasOneClass) {
			if($hasOneClass == 'WidgetArea' || is_subclass_of($hasOneClass, 'WidgetArea')) {
				$widgetAreaRelations[] = $hasOneName;
			}
		}

		// find widget
		$widget = null;
		foreach($widgetAreaRelations as $widgetAreaRelation) {
			if($widget) break;
			$widget = $this->owner->data()->$widgetAreaRelation()->Widgets(
				sprintf('"Widget"."ID" = %d', $SQL_id)
			)->First();
		}
		if(!$widget) user_error('No widget found', E_USER_ERROR);
		
		// find controller
		$controllerClass = '';
		foreach(array_reverse(ClassInfo::ancestry($widget->class)) as $widgetClass) {
			$controllerClass = "{$widgetClass}_Controller";
			if(class_exists($controllerClass)) break;
		}
		if(!$controllerClass) user_error(
			sprintf('No controller available for %s', $widget->class),
			E_USER_ERROR
		);

		return new $controllerClass($widget);
	}

}