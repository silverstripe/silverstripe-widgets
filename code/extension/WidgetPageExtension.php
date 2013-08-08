<?php
/**
 * Adds a single {@link WidgetArea} called "SideBar" to {@link Page} classes.
 * Adjust your templates to render the resulting
 * {@link WidgetArea} as required, through the $SideBarView placeholder.
 *
 * This extension is just an example on how to use the widgets functionality,
 * feel free to create your own relationships, naming conventions, etc.
 * without using this class.
 */
class WidgetPageExtension extends DataExtension {

	private static $db = array(
		'InheritSideBar' => 'Boolean',
	);

	private static $defaults = array(
		'InheritSideBar' => true
	);

	private static $has_one = array(
		'SideBar' => 'WidgetArea'
	);

	public function updateCMSFields(FieldList $fields) {
		$fields->addFieldToTab(
			"Root.Widgets", 
			new CheckboxField("InheritSideBar", 'Inherit Sidebar From Parent')
		);
		$fields->addFieldToTab(
			"Root.Widgets", 
			new WidgetAreaEditor("SideBar")
		);
	}

	/**
	 * @return WidgetArea
	 */
	public function SideBarView() {
		if(
			$this->owner->InheritSideBar 
			&& $this->owner->getParent() 
			&& $this->owner->getParent()->hasMethod('SideBar')
		) {
			return $this->owner->getParent()->SideBar();
		} elseif($this->owner->SideBar()->exists()){
			return $this->owner->SideBar();
		}
	}

	/**
	 * Support Translatable so that we don't link WidgetAreas across translations
	 */
	public function onTranslatableCreate() {
		//reset the sidebar ID
		$this->owner->SideBarID = 0;
	}

}
