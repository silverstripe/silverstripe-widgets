<?php
/**
 * @package cms
 * @subpackage tests
 */
class WidgetAreaEditorTest extends SapphireTest {
	/**
	 * This is the widget you want to use for your unit tests.
	 */
	protected $widgetToTest = 'WidgetAreaEditorTest_TestWidget';

	protected $extraDataObjects = array(
		'WidgetAreaEditorTest_FakePage',
		'WidgetAreaEditorTest_TestWidget',
	);
	
	protected $usesDatabase = true;
	
	function testFillingOneArea() {
		$data = array(
			'Widget' => array(
				'BottomBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidget',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				)
			)
		);
		$request = new SS_HTTPRequest('get', 'post', array(), $data);
		
		$editorSide = new WidgetAreaEditor('SideBar');
		$editorBott = new WidgetAreaEditor('BottomBar');
		$form = new Form(new ContentController(), 'Form', new FieldList($editorSide, $editorBott), new FieldList());
		$form->setRequest($request);
		
		$page = new WidgetAreaEditorTest_FakePage();

		$form->saveInto($page);
		$page->write();
		$page->flushCache();
		$page->BottomBar()->flushCache();
		$page->SideBar()->flushCache();

		$this->assertEquals($page->BottomBar()->Widgets()->Count(), 1);
		$this->assertEquals($page->SideBar()->Widgets()->Count(), 0);
	}

	function testFillingTwoAreas() {
		$data = array(
			'Widget' => array(
				'SideBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidgetSide',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				),
				'BottomBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidgetBottom',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				)
			)
		);
		$request = new SS_HTTPRequest('get', 'post', array(), $data);
		
		$editorSide = new WidgetAreaEditor('SideBar');
		$editorBott = new WidgetAreaEditor('BottomBar');
		$form = new Form(new ContentController(), 'Form', new FieldList($editorSide, $editorBott), new FieldList());
		$form->setRequest($request);
		$page = new WidgetAreaEditorTest_FakePage();

		$form->saveInto($page);
		$page->write();
		$page->flushCache();
		$page->BottomBar()->flushCache();
		$page->SideBar()->flushCache();
		
		// Make sure they both got saved
		$this->assertEquals($page->BottomBar()->Widgets()->Count(), 1);
		$this->assertEquals($page->SideBar()->Widgets()->Count(), 1);
		
		$sideWidgets = $page->SideBar()->Widgets()->toArray();
		$bottWidgets = $page->BottomBar()->Widgets()->toArray();
		$this->assertEquals($sideWidgets[0]->Title(), 'MyTestWidgetSide');
		$this->assertEquals($bottWidgets[0]->Title(), 'MyTestWidgetBottom');
	}
		
	function testDeletingOneWidgetFromOneArea() {
		// First get some widgets in there
		$data = array(
			'Widget' => array(
				'SideBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidgetSide',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				),
				'BottomBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidgetBottom',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				)
			)
		);
		$request = new SS_HTTPRequest('get', 'post', array(), $data);
		
		$editorSide = new WidgetAreaEditor('SideBar');
		$editorBott = new WidgetAreaEditor('BottomBar');
		$form = new Form(new ContentController(), 'Form', new FieldList($editorSide, $editorBott), new FieldList());
		$form->setRequest($request);
		$page = new WidgetAreaEditorTest_FakePage();

		$form->saveInto($page);
		$page->write();
		$page->flushCache();
		$page->BottomBar()->flushCache();
		$page->SideBar()->flushCache();
		$sideWidgets = $page->SideBar()->Widgets()->toArray();
		$bottWidgets = $page->BottomBar()->Widgets()->toArray();
		
		// Save again (after removing the SideBar's widget)
		$data = array(
			'Widget' => array(
				'SideBar' => array(
				),
				'BottomBar' => array(
					$bottWidgets[0]->ID => array(
						'Title' => 'MyTestWidgetBottom',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				)
			)
		);
		$request = new SS_HTTPRequest('get', 'post', array(), $data);
		$form->setRequest($request);
		$form->saveInto($page);

		$page->write();
		$page->flushCache();
		$page->BottomBar()->flushCache();
		$page->SideBar()->flushCache();
		$sideWidgets = $page->SideBar()->Widgets()->toArray();
		$bottWidgets = $page->BottomBar()->Widgets()->toArray();
		
		$this->assertEquals($page->BottomBar()->Widgets()->Count(), 1);
		$this->assertEquals($bottWidgets[0]->Title(), 'MyTestWidgetBottom');
		$this->assertEquals($page->SideBar()->Widgets()->Count(), 0);
	}

	function testDeletingAWidgetFromEachArea() {
		// First get some widgets in there
		$data = array(
			'Widget' => array(
				'SideBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidgetSide',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				),
				'BottomBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidgetBottom',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				)
			)
		);
		$request = new SS_HTTPRequest('get', 'post', array(), $data);
		
		$editorSide = new WidgetAreaEditor('SideBar');
		$editorBott = new WidgetAreaEditor('BottomBar');
		$form = new Form(new ContentController(), 'Form', new FieldList($editorSide, $editorBott), new FieldList());
		$form->setRequest($request);
		$page = new WidgetAreaEditorTest_FakePage();

		$form->saveInto($page);
		$page->write();
		$page->flushCache();
		$page->BottomBar()->flushCache();
		$page->SideBar()->flushCache();
		$sideWidgets = $page->SideBar()->Widgets()->toArray();
		$bottWidgets = $page->BottomBar()->Widgets()->toArray();
		
		// Save again (after removing the SideBar's widget)
		$data = array(
			'Widget' => array(
				'SideBar' => array(
				),
				'BottomBar' => array(
				)
			)
		);
		$request = new SS_HTTPRequest('get', 'post', array(), $data);
		$form->setRequest($request);
		$form->saveInto($page);
		
		$page->write();
		$page->flushCache();
		$page->BottomBar()->flushCache();
		$page->SideBar()->flushCache();
		$sideWidgets = $page->SideBar()->Widgets()->toArray();
		$bottWidgets = $page->BottomBar()->Widgets()->toArray();
		
		$this->assertEquals($page->BottomBar()->Widgets()->Count(), 0);
		$this->assertEquals($page->SideBar()->Widgets()->Count(), 0);
	}
	
	function testEditingOneWidget() {
		// First get some widgets in there
		$data = array(
			'Widget' => array(
				'SideBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidgetSide',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				),
				'BottomBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidgetBottom',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				)
			)
		);
		$request = new SS_HTTPRequest('get', 'post', array(), $data);
		
		$editorSide = new WidgetAreaEditor('SideBar');
		$editorBott = new WidgetAreaEditor('BottomBar');
		$form = new Form(new ContentController(), 'Form', new FieldList($editorSide, $editorBott), new FieldList());
		$form->setRequest($request);
		$page = new WidgetAreaEditorTest_FakePage();

		$form->saveInto($page);
		$page->write();
		$page->flushCache();
		$page->BottomBar()->flushCache();
		$page->SideBar()->flushCache();
		$sideWidgets = $page->SideBar()->Widgets()->toArray();
		$bottWidgets = $page->BottomBar()->Widgets()->toArray();
		
		// Save again (after removing the SideBar's widget)
		$data = array(
			'Widget' => array(
				'SideBar' => array(
					$sideWidgets[0]->ID => array(
						'Title' => 'MyTestWidgetSide-edited',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				),
				'BottomBar' => array(
					$bottWidgets[0]->ID => array(
						'Title' => 'MyTestWidgetBottom',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				)
			)
		);
		$request = new SS_HTTPRequest('get', 'post', array(), $data);
		$form->setRequest($request);
		$form->saveInto($page);

		$page->write();
		$page->flushCache();
		$page->BottomBar()->flushCache();
		$page->SideBar()->flushCache();
		$sideWidgets = $page->SideBar()->Widgets()->toArray();
		$bottWidgets = $page->BottomBar()->Widgets()->toArray();
		
		$this->assertEquals($page->BottomBar()->Widgets()->Count(), 1);
		$this->assertEquals($page->SideBar()->Widgets()->Count(), 1);
		$this->assertEquals($bottWidgets[0]->Title(), 'MyTestWidgetBottom');
		$this->assertEquals($sideWidgets[0]->Title(), 'MyTestWidgetSide-edited');
	}

	function testEditingAWidgetFromEachArea() {
		// First get some widgets in there
		$data = array(
			'Widget' => array(
				'SideBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidgetSide',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				),
				'BottomBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidgetBottom',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				)
			)
		);
		$request = new SS_HTTPRequest('get', 'post', array(), $data);
		
		$editorSide = new WidgetAreaEditor('SideBar');
		$editorBott = new WidgetAreaEditor('BottomBar');
		$form = new Form(new ContentController(), 'Form', new FieldList($editorSide, $editorBott), new FieldList());
		$form->setRequest($request);
		$page = new WidgetAreaEditorTest_FakePage();

		$form->saveInto($page);
		$page->write();
		$page->flushCache();
		$page->BottomBar()->flushCache();
		$page->SideBar()->flushCache();
		$sideWidgets = $page->SideBar()->Widgets()->toArray();
		$bottWidgets = $page->BottomBar()->Widgets()->toArray();
		
		// Save again (after removing the SideBar's widget)
		$data = array(
			'Widget' => array(
				'SideBar' => array(
					$sideWidgets[0]->ID => array(
						'Title' => 'MyTestWidgetSide-edited',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				),
				'BottomBar' => array(
					$bottWidgets[0]->ID => array(
						'Title' => 'MyTestWidgetBottom-edited',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				)
			)
		);
		$request = new SS_HTTPRequest('get', 'post', array(), $data);
		$form->setRequest($request);
		$form->saveInto($page);

		$page->write();
		$page->flushCache();
		$page->BottomBar()->flushCache();
		$page->SideBar()->flushCache();
		$sideWidgets = $page->SideBar()->Widgets()->toArray();
		$bottWidgets = $page->BottomBar()->Widgets()->toArray();
		
		$this->assertEquals($page->BottomBar()->Widgets()->Count(), 1);
		$this->assertEquals($page->SideBar()->Widgets()->Count(), 1);
		$this->assertEquals($bottWidgets[0]->Title(), 'MyTestWidgetBottom-edited');
		$this->assertEquals($sideWidgets[0]->Title(), 'MyTestWidgetSide-edited');
	}
	
	function testEditAWidgetFromOneAreaAndDeleteAWidgetFromAnotherArea() {
		// First get some widgets in there
		$data = array(
			'Widget' => array(
				'SideBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidgetSide',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				),
				'BottomBar' => array(
					'new-1' => array(
						'Title' => 'MyTestWidgetBottom',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				)
			)
		);
		$request = new SS_HTTPRequest('get', 'post', array(), $data);
		
		$editorSide = new WidgetAreaEditor('SideBar');
		$editorBott = new WidgetAreaEditor('BottomBar');
		$form = new Form(new ContentController(), 'Form', new FieldList($editorSide, $editorBott), new FieldList());
		$form->setRequest($request);
		$page = new WidgetAreaEditorTest_FakePage();

		$editorSide->saveInto($page);
		$editorBott->saveInto($page);
		$page->write();
		$page->flushCache();
		$page->BottomBar()->flushCache();
		$page->SideBar()->flushCache();
		$sideWidgets = $page->SideBar()->Widgets()->toArray();
		$bottWidgets = $page->BottomBar()->Widgets()->toArray();
		
		// Save again (after removing the SideBar's widget)
		$data = array(
			'Widget' => array(
				'SideBar' => array(
					$sideWidgets[0]->ID => array(
						'Title' => 'MyTestWidgetSide-edited',
						'Type' => $this->widgetToTest,
						'Sort' => 0
					)
				),
				'BottomBar' => array(
				)
			)
		);
		$request = new SS_HTTPRequest('get', 'post', array(), $data);
		$form->setRequest($request);
		$form->saveInto($page);

		$page->write();
		$page->flushCache();
		$page->BottomBar()->flushCache();
		$page->SideBar()->flushCache();
		$sideWidgets = $page->SideBar()->Widgets()->toArray();
		$bottWidgets = $page->BottomBar()->Widgets()->toArray();
		
		$this->assertEquals($page->BottomBar()->Widgets()->Count(), 0);
		$this->assertEquals($page->SideBar()->Widgets()->Count(), 1);
		$this->assertEquals($sideWidgets[0]->Title(), 'MyTestWidgetSide-edited');
	}
}

class WidgetAreaEditorTest_FakePage extends Page implements TestOnly {
	private static $has_one = array(
		"SideBar" => "WidgetArea",
		"BottomBar" => "WidgetArea",
	);
}

class WidgetAreaEditorTest_TestWidget extends Widget implements TestOnly {
	private static $cmsTitle = "Test widget";
	private static $title = "Test widget";
	private static $description = "Test widget";
}
