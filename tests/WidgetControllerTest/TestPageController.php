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
        if (file_exists('widgets')) {
            SSViewer::add_themes(['silverstripe/widgets:tests/WidgetControllerTest']);
        } else {
            // When installed as the root project, e.g. Travis
            SSViewer::add_themes(['tests/WidgetControllerTest']);
        }
        return new SSViewer(TestPage::class);
    }
}
