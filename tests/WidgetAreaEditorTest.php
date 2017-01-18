<?php

namespace SilverStripe\Widgets\Tests;

use Page;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Widgets\Extensions\WidgetPageExtension;
use SilverStripe\Widgets\Forms\WidgetAreaEditor;
use SilverStripe\Widgets\Model\Widget;
use SilverStripe\Widgets\Tests\WidgetAreaEditorTest\FakePage;
use SilverStripe\Widgets\Tests\WidgetAreaEditorTest\TestWidget;

/**
 * @package cms
 * @subpackage tests
 */
class WidgetAreaEditorTest extends SapphireTest
{
    /**
     * This is the widget you want to use for your unit tests.
     */
    protected $widgetToTest = TestWidget::class;

    protected $extraDataObjects = array(
        FakePage::class,
        TestWidget::class,
    );

    protected $usesDatabase = true;

    protected $requiredExtensions = array(
        SiteTree::class => array(WidgetPageExtension::class)
    );

    public function testFillingOneArea()
    {
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
        $request = new HTTPRequest('get', 'post', array(), $data);

        $editorSide = new WidgetAreaEditor('SideBar');
        $editorBott = new WidgetAreaEditor('BottomBar');
        $form = new Form(
            new ContentController(),
            Form::class,
            new FieldList($editorSide, $editorBott),
            new FieldList()
        );
        $form->setRequest($request);

        $page = new FakePage();

        $form->saveInto($page);
        $page->write();
        $page->flushCache();
        $page->BottomBar()->flushCache();
        $page->SideBar()->flushCache();

        $this->assertEquals($page->BottomBar()->Widgets()->Count(), 1);
        $this->assertEquals($page->SideBar()->Widgets()->Count(), 0);
    }

    public function testFillingTwoAreas()
    {
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
        $request = new HTTPRequest('get', 'post', array(), $data);

        $editorSide = new WidgetAreaEditor('SideBar');
        $editorBott = new WidgetAreaEditor('BottomBar');
        $form = new Form(
            new ContentController(),
            Form::class,
            new FieldList($editorSide, $editorBott),
            new FieldList()
        );
        $form->setRequest($request);
        $page = new FakePage();

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
        $this->assertEquals($sideWidgets[0]->getTitle(), 'MyTestWidgetSide');
        $this->assertEquals($bottWidgets[0]->getTitle(), 'MyTestWidgetBottom');
    }

    public function testDeletingOneWidgetFromOneArea()
    {
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
        $request = new HTTPRequest('get', 'post', array(), $data);

        $editorSide = new WidgetAreaEditor('SideBar');
        $editorBott = new WidgetAreaEditor('BottomBar');
        $form = new Form(
            new ContentController(),
            Form::class,
            new FieldList($editorSide, $editorBott),
            new FieldList()
        );
        $form->setRequest($request);
        $page = new FakePage();

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
        $request = new HTTPRequest('get', 'post', array(), $data);
        $form->setRequest($request);
        $form->saveInto($page);

        $page->write();
        $page->flushCache();
        $page->BottomBar()->flushCache();
        $page->SideBar()->flushCache();
        $sideWidgets = $page->SideBar()->Widgets()->toArray();
        $bottWidgets = $page->BottomBar()->Widgets()->toArray();

        $this->assertEquals($page->BottomBar()->Widgets()->Count(), 1);
        $this->assertEquals($bottWidgets[0]->getTitle(), 'MyTestWidgetBottom');
        $this->assertEquals($page->SideBar()->Widgets()->Count(), 0);
    }

    public function testDeletingAWidgetFromEachArea()
    {
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
        $request = new HTTPRequest('get', 'post', array(), $data);

        $editorSide = new WidgetAreaEditor('SideBar');
        $editorBott = new WidgetAreaEditor('BottomBar');
        $form = new Form(
            new ContentController(),
            Form::class,
            new FieldList($editorSide, $editorBott),
            new FieldList()
        );
        $form->setRequest($request);
        $page = new FakePage();

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
        $request = new HTTPRequest('get', 'post', array(), $data);
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

    public function testEditingOneWidget()
    {
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
        $request = new HTTPRequest('get', 'post', array(), $data);

        $editorSide = new WidgetAreaEditor('SideBar');
        $editorBott = new WidgetAreaEditor('BottomBar');
        $form = new Form(
            new ContentController(),
            Form::class,
            new FieldList($editorSide, $editorBott),
            new FieldList()
        );
        $form->setRequest($request);
        $page = new FakePage();

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
        $request = new HTTPRequest('get', 'post', array(), $data);
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
        $this->assertEquals($bottWidgets[0]->getTitle(), 'MyTestWidgetBottom');
        $this->assertEquals($sideWidgets[0]->getTitle(), 'MyTestWidgetSide-edited');
    }

    public function testEditingAWidgetFromEachArea()
    {
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
        $request = new HTTPRequest('get', 'post', array(), $data);

        $editorSide = new WidgetAreaEditor('SideBar');
        $editorBott = new WidgetAreaEditor('BottomBar');
        $form = new Form(
            new ContentController(),
            Form::class,
            new FieldList($editorSide, $editorBott),
            new FieldList()
        );
        $form->setRequest($request);
        $page = new FakePage();

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
        $request = new HTTPRequest('get', 'post', array(), $data);
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
        $this->assertEquals($bottWidgets[0]->getTitle(), 'MyTestWidgetBottom-edited');
        $this->assertEquals($sideWidgets[0]->getTitle(), 'MyTestWidgetSide-edited');
    }

    public function testEditAWidgetFromOneAreaAndDeleteAWidgetFromAnotherArea()
    {
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
        $request = new HTTPRequest('get', 'post', array(), $data);

        $editorSide = new WidgetAreaEditor('SideBar');
        $editorBott = new WidgetAreaEditor('BottomBar');
        $form = new Form(
            new ContentController(),
            Form::class,
            new FieldList($editorSide, $editorBott),
            new FieldList()
        );
        $form->setRequest($request);
        $page = new FakePage();

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
        $request = new HTTPRequest('get', 'post', array(), $data);
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
        $this->assertEquals($sideWidgets[0]->getTitle(), 'MyTestWidgetSide-edited');
    }
}
