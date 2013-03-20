<?php
/**
 * Widgets let CMS authors drag and drop small pieces of functionality into 
 * defined areas of their websites.
 * 
 * ## Forms
 * You can use forms in widgets by implementing a {@link Widget_Controller}.
 * See {@link Widget_Controller} for more information.
 * 
 * @package cms
 * @subpackage widgets
 */
class Widget extends DataObject {

	/**
	 *
	 * @var array
	 */
	public static $db = array(
		"Sort" => "Int",
		"Enabled" => "Boolean"
	);

	/**
	 *
	 * @var array
	 */
	public static $defaults = array(
		'Enabled' => true
	);

	/**
	 *
	 * @var array
	 */
	public static $has_one = array(
		"Parent" => "WidgetArea",
	);

	/**
	 *
	 * @var array
	 */
	public static $has_many = array();

	/**
	 *
	 * @var array
	 */
	public static $many_many = array();

	/**
	 *
	 * @var array
	 */
	public static $belongs_many_many = array();

	/**
	 *
	 * @var string
	 */
	public static $default_sort = "\"Sort\"";

	/**
	 *
	 * @var string
	 */
	public static $title = "Widget Title";

	/**
	 *
	 * @var string
	 */
	public static $cmsTitle = "Name of this widget";

	/**
	 *
	 * @var string
	 */
	public static $description = "Description of what this widget does.";

	/**
	 *
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = new FieldList();
		$this->extend('updateCMSFields', $fields);
		return $fields;
	}
	
	/**
	 * Note: Overloaded in {@link Widget_Controller}.
	 * 
	 * @return string HTML
	 */
	public function WidgetHolder() {
		return $this->renderWith("WidgetHolder");
	}
	
	/**
	 * Renders the widget content in a custom template with the same name as the current class.
	 * This should be the main point of output customization.
	 * 
	 * Invoked from within WidgetHolder.ss, which contains
	 * the "framing" around the custom content, like a title.
	 * 
	 * Note: Overloaded in {@link Widget_Controller}.
	 * 
	 * @return string HTML
	 */
	public function Content() {
		return $this->renderWith(array_reverse(ClassInfo::ancestry($this->class)));
	}

	/**
	 *
	 * @return string
	 */
	public function Title() {
		return _t($this->class.'.TITLE', $this->config()->title);
	}

	/**
	 *
	 * @return string
	 */
	public function CMSTitle() {
		return _t($this->class.'.CMSTITLE', $this->config()->cmsTitle);
	}

	/**
	 *
	 * @return string
	 */
	public function Description() {
		return _t($this->class.'.DESCRIPTION', $this->config()->description);
	}

	/**
	 *
	 * @return string - HTML
	 */
	public function DescriptionSegment() {
		return $this->renderWith('WidgetDescription'); 
	}
	
	/**
	 * @see Widget_Controller->editablesegment()
	 * @return string - HTML
	 */
	public function EditableSegment() {
		return $this->renderWith('WidgetEditor'); 
	}

	/**
	 *
	 * @return FieldList
	 */
	public function CMSEditor() {
		$fields = $this->getCMSFields();
		$outputFields = new FieldList();
		foreach($fields as $field) {
			$name = $field->getName();
			$value = $this->getField($name);
			if ($value) {
				$field->setValue($value);
			}
			$name = preg_replace("/([A-Za-z0-9\-_]+)/", "Widget[" . $this->ID . "][\\1]", $name);
			$field->setName($name);
			$outputFields->push($field);
		}
		return $outputFields;
	}

	/**
	 *
	 * @return string
	 */
	public function ClassName() {
		return $this->class;
	}

	/**
	 *
	 * @return string
	 */
	public function Name() {
		return "Widget[".$this->ID."]";
	}

	/**
	 *
	 * @param array $data
	 */
	public function populateFromPostData($data) {
		$fields = $this->getCMSFields();
		foreach($data as $name => $value) {
			if($name != "Type") {
				if ($field = $fields->dataFieldByName($name)) {
					$field->setValue($value);
					$field->saveInto($this);
				}
				else {
					$this->setField($name, $value);
				}
			}
		}
		
		$this->write();
		
		// The field must be written to ensure a unique ID.
		$this->Name = $this->class.$this->ID;
		$this->write();
	}
	
}

/**
 * Optional controller for every widget which has its own logic,
 * e.g. in forms. It always handles a single widget, usually passed
 * in as a database identifier through the controller URL.
 * Needs to be constructed as a nested controller
 * within a {@link ContentController}.
 * 
 * ## Forms
 * You can add forms like in any other SilverStripe controller.
 * If you need access to the widget from within a form,
 * you can use `$this->controller->getWidget()` inside the form logic.
 * Note: Widget controllers currently only work on {@link Page} objects,
 * because the logic is implemented in {@link ContentController->handleWidget()}.
 * Copy this logic and the URL rules to enable it for other controllers.
 * 
 * @package cms
 * @subpackage widgets
 */
class Widget_Controller extends Controller {
	
	/**
	 * @var Widget
	 */
	protected $widget;

	/**
	 *
	 * @var array
	 */
	public static $allowed_actions = array(
		'editablesegment'
	);

	/**
	 *
	 * @param Widget $widget
	 */
	public function __construct($widget = null) {
		// TODO This shouldn't be optional, is only necessary for editablesegment()
		if($widget) {
			$this->widget = $widget;
			$this->failover = $widget;
		}
		
		parent::__construct();
	}

	/**
	 *
	 * @param string $action
	 * @return string
	 */
	public function Link($action = null) {
		$segment = Controller::join_links('widget', ($this->widget ? $this->widget->ID : null), $action);
		
		if(Director::get_current_page()) {
			return Director::get_current_page()->Link($segment);
		} else {
			return Controller::curr()->Link($segment);
		}
	}
	
	/**
	 * @return Widget
	 */
	public function getWidget() {
		return $this->widget;
	}
	
	/**
	 * Overloaded from {@link Widget->Content()}
	 * to allow for controller/form linking.
	 * 
	 * @return string HTML
	 */
	public function Content() {
		return $this->renderWith(array_reverse(ClassInfo::ancestry($this->widget->class)));
	}
	
	/**
	 * Overloaded from {@link Widget->WidgetHolder()}
	 * to allow for controller/form linking.
	 * 
	 * @return string HTML
	 */
	public function WidgetHolder() {
		return $this->renderWith("WidgetHolder");
	}
	
	/**
	 * Uses the `WidgetEditor.ss` template and {@link Widget->editablesegment()}
	 * to render a administrator-view of the widget. It is assumed that this
	 * view contains form elements which are submitted and saved through {@link WidgetAreaEditor}
	 * within the CMS interface.
	 * 
	 * @return string HTML
	 */
	public function editablesegment() {
		$className = $this->urlParams['ID'];
		if (class_exists('Translatable') && Member::currentUserID()) {
			// set current locale based on logged in user's locale
			$locale = Member::currentUser()->Locale;
			Translatable::set_current_locale($locale);
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
 * @package cms
 * @subpackage widgets
 */
class Widget_TreeDropdownField extends TreeDropdownField {

	public function FieldHolder($properties = array()) {}
	public function Field($properties = array()) {}
}

