<?php

namespace SilverStripe\Widgets\Tests\WidgetAreaEditorTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Widgets\Model\Widget;

class TestWidget extends Widget implements TestOnly
{
    private static $table_name = 'WidgetAreaEditorTest_TestWidget';
    private static $cmsTitle = "Test widget";
    private static $title = "Test widget";
    private static $description = "Test widget";
}
