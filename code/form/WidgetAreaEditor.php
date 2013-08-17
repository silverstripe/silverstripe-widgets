<?php

/**
 * Special field type for selecting and configuring widgets on a page.
 *
 * @package widgets
 */
class WidgetAreaEditor extends FormField {

	/**
	 * @param string $name
	 * @param array $widgetClasses
	 * @param int $maxWidgets
	 */
	public function __construct($name, $widgetClasses = array('Widget'), $maxWidgets = 0) {
		$this->MaxWidgets = $maxWidgets;
		$this->widgetClasses = $widgetClasses;
		
		parent::__construct($name);
	}

	/**
	 * @param array $properties
	 *
	 * @return string - HTML
	 */
	public function FieldHolder($properties = array()) {
		Requirements::css('widgets/css/WidgetAreaEditor.css');
		Requirements::javascript('widgets/javascript/WidgetAreaEditor.js');

		return $this->renderWith("WidgetAreaEditor");
	}

	/**
	 *
	 * @return ArrayList
	 */
	public function AvailableWidgets() {
		$widgets= new ArrayList();

		foreach($this->widgetClasses as $widgetClass) {
			$classes = ClassInfo::subclassesFor($widgetClass);

			if (isset($classes['Widget'])) { 
				unset($classes['Widget']); 
			} 
			else if (isset($classes[0]) && $classes[0] == 'Widget') { 
				unset($classes[0]); 
			}
			
			foreach($classes as $class) {
				
				$available = Config::inst()->get($class, 'only_available_in');
				
				if (!empty($available) && is_array($available)) {
					if(in_array($this->Name, $available)) {
						$widgets->push(singleton($class));
					}
				}else {
					$widgets->push(singleton($class));
				}
			}
		}
		
		return $widgets;
	}

	/**
	 * @return HasManyList
	 */
	public function UsedWidgets() {
		// Call class_exists() to load Widget.php earlier and avoid a segfault
		class_exists('Widget');
		
		$relationName = $this->name;
		$widgets = $this->form->getRecord()->getComponent($relationName)->Items();

		return $widgets;
	}

	/**
	 * @return string
	 */
	public function IdxField() {
		return $this->id() . 'ID';
	}

	/**
	 *
	 * @return int
	 */
	public function Value() {
		$relationName = $this->name;

		return $this->form->getRecord()->getComponent($relationName)->ID;
	}

	/**
	 * @param DataObjectInterface $record
	 */
	public function saveInto(DataObjectInterface $record) {
		$name = $this->name;
		$idName = $name . "ID";

		$widgetarea = $record->getComponent($name);
		$widgetarea->write();
		
		$record->$idName = $widgetarea->ID;
	
		$widgets = $widgetarea->Items();
	
		// store the field IDs and delete the missing fields
		// alternatively, we could delete all the fields and re add them
		$missingWidgets = array();
		
		if($widgets) {
			foreach($widgets as $existingWidget) {
				$missingWidgets[$existingWidget->ID] = $existingWidget;
			}
		}
		
		if(isset($_REQUEST['Widget'])) {
			foreach(array_keys($_REQUEST['Widget']) as $widgetAreaName) {
				if ($widgetAreaName !== $this->name) {
					continue;
				}

				foreach(array_keys($_REQUEST['Widget'][$widgetAreaName]) as $newWidgetID) {
					$newWidgetData = $_REQUEST['Widget'][$widgetAreaName][$newWidgetID];

					// Sometimes the id is "new-1" or similar, ensure this doesn't get into the query
					if(!is_numeric($newWidgetID)) {
						$newWidgetID = 0;
					}
				
					// \"ParentID\" = '0' is for the new page
					$widget = DataObject::get_one(
						'Widget',
						"(\"ParentID\" = '{$record->$name()->ID}' OR ".
						"\"ParentID\" = '0') AND \"Widget\".\"ID\" = '$newWidgetID'"
					);

					// check if we are updating an existing widget
					if($widget && isset($missingWidgets[$widget->ID])) {
						unset($missingWidgets[$widget->ID]);
					}
					
					// create a new object
					if(!$widget && !empty($newWidgetData['Type']) && class_exists($newWidgetData['Type'])) {
						$widget = new $newWidgetData['Type']();
						$widget->ID = 0;
						$widget->ParentID = $record->$name()->ID;

						if(!is_subclass_of($widget, 'Widget')) {
							$widget = null;
						}
					}

					if($widget) {
						if($widget->ParentID == 0) {
							$widget->ParentID = $record->$name()->ID;
						}

						$widget->populateFromPostData($newWidgetData);
					}
				}
			}
		}
		
		// remove the fields not saved
		if($missingWidgets) {
			foreach($missingWidgets as $removedWidget) {
				if(isset($removedWidget) && is_numeric($removedWidget->ID)) {
					$removedWidget->delete();
				}
			}
		}
	}
}
