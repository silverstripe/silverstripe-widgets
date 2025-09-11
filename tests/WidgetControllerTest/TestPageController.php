<?php

namespace SilverStripe\Widgets\Tests\WidgetControllerTest;

use PageController;
use SilverStripe\Dev\TestOnly;
use SilverStripe\View\SSViewer;

/**
 * @package cms
 * @subpackage tests
 */
class TestPageController extends PageController implements TestOnly
{
    /**
     * Template selection doesnt work in test folders, so we add a test theme a template name.
     */
    public function getViewer(string $action): SSViewer
    {
        SSViewer::add_themes(['silverstripe/widgets:/tests/WidgetControllerTest']);

        return new SSViewer(TestPage::class);
    }
}
