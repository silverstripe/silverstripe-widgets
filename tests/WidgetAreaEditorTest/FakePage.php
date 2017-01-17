<?php

namespace SilverStripe\Widgets\Tests\WidgetAreaEditorTest;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Widgets\Model\WidgetArea;

class FakePage extends Page implements TestOnly
{
    private static $table_name = 'FakePage';

    private static $has_one = array(
        "BottomBar" => WidgetArea::class
    );
}
