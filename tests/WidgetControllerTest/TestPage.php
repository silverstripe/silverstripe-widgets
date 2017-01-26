<?php

namespace SilverStripe\Widgets\Tests\WidgetControllerTest;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\View\SSViewer;
use SilverStripe\Widgets\Model\WidgetArea;

/**
 * @package cms
 * @subpackage tests
 */
class TestPage extends Page implements TestOnly
{
    private static $table_name = 'TestPage';

    private static $has_one = array(
        'WidgetControllerTestSidebar' => WidgetArea::class
    );
}
