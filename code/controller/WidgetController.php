<?php

/**
 * Optional controller for every widget which has its own logic, e.g. in forms. 
 *
 * It always handles a single widget, usually passed in as a database 
 * identifier through the controller URL. Needs to be constructed as a nested 
 * controller within a {@link ContentController}.
 * 
 * ## Forms
 * You can add forms like in any other SilverStripe controller. If you need 
 * access to the widget from within a form, you can use 
 * `$this->controller->getWidget()` inside the form logic.
 *
 * Note: Widget controllers currently only work on {@link Page} objects,
 * because the logic is implemented in {@link ContentController->handleWidget()}.
 * Copy this logic and the URL rules to enable it for other controllers.
 * 
 * @package widgets
 */
class WidgetController extends Controller {
	
	/**
	 * @var Widget
	 */
	protected $widget;

	/**
	 * @var array
	 */
	private static $allowed_actions = array(
		'editablesegment'
	);

	/**
	 * @param Widget $widget
	 */
	public function __construct($widget = null) {
		if($widget) {
			$this->widget = $widget;
			$this->failover = $widget;
		}
		
		parent::__construct();
	}

	/**
	 * @param string $action
	 * @return string
	 */
	public function Link($action = null) {
		$id = ($this->widget) ? $this->widget->ID : null;
		$segment = Controller::join_links('widget', $id, $action);
		
		if($page = Director::get_current_page()) {
			return $page->Link($segment);
		} 
		
		return Controller::curr()->Link($segment);
	}
	
	/**
	 * @return Widget
	 */
	public function getWidget() {
		return $this->widget;
	}
	
	/**
	 * Overloaded from {@link Widget->Content()} to allow for controller / form 
	 * linking.
	 * 
	 * @return string HTML
	 */
	public function Content() {
		return $this->renderWith(array_reverse(ClassInfo::ancestry($this->widget->class)));
	}
	
	/**
	 * Overloaded from {@link Widget->WidgetHolder()} to allow for controller/ 
	 * form linking.
	 * 
	 * @return string HTML
	 */
	public function WidgetHolder() {
		return $this->renderWith("WidgetHolder");
	}
	
	/**
	 * Uses the `WidgetEditor.ss` template and {@link Widget->editablesegment()} 
	 * to render a administrator-view of the widget. It is assumed that this
	 * view contains form elements which are submitted and saved through 
	 * {@link WidgetAreaEditor} within the CMS interface.
	 * 
	 * @return string HTML
	 */
	public function editablesegment() {
		$className = $this->urlParams['ID'];
		if (class_exists('Translatable') && Member::currentUserID()) {
			// set current locale based on logged in user's locale
			$locale = Member::currentUser()->Locale;
			i18n::set_locale($locale);
		}
		if(class_exists($className) && is_subclass_of($className, 'Widget')) {
			$obj = new $className();
			return $obj->EditableSegment();
		} else {
			user_error("Bad widget class: $className", E_USER_WARNING);
			return "Bad widget class name given";
		}
	}	
}

/**
 * @deprecated Use WidgetController
 * @package widgets
 */
class Widget_Controller extends WidgetController {

}
