<?php

namespace SilverStripe\Widgets\Tests\WidgetControllerTest;

use PageController;
use ReflectionClass;
use SilverStripe\Dev\TestOnly;
use SilverStripe\View\SSViewer;
use SilverStripe\Widgets\Tests\WidgetControllerTest\TestPage;

/**
 * @package cms
 * @subpackage tests
 */
class TestPageController extends PageController implements TestOnly
{
    /**
     * Template selection doesnt work in test folders, so we add a test theme a template name.
     */
    public function getViewer($action)
    {
        SSViewer::add_themes(["silverstripe/widgets:widgets/tests/WidgetControllerTest"]);
        return new SSViewer(TestPage::class);
    }
}
