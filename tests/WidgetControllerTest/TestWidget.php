<?php

namespace SilverStripe\Widgets\Tests\WidgetControllerTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Widgets\Model\Widget;

/**
 * @package widgets
 * @subpackage tests
 */
class TestWidget extends Widget implements TestOnly
{
    private static $table_name = 'TestWidgetB';

    private static $db = array(
        'TestValue' => 'Text'
    );
}
