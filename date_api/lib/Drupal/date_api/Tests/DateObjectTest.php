<?php

/**
 * @file
 * Test Date API functions
 */

namespace Drupal\date_api\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\date_api\DateObject;
use DateTimeZone;

class DateObjectTest extends WebTestBase {
  /**
   * @todo.
   */
  public static function getInfo() {
    return array(
      'name' => t('Date Object'),
      'description' => t('Test Date Object.') ,
      'group' => t('Date'),
    );
  }

  /**
   * Set up required modules.
   */
  public static $modules = array('date_api');

  /**
   * @todo.
   */
  public function setUp() {
    parent::setUp();
    config('date_api.settings')->set('iso8601', FALSE)->save();
    variable_set('date_first_day', 1);
  }

  /**
   * @todo.
   */
  public function testDateAPI() {

    // Create date object from datetime string.
    $input = '2009-03-07 10:30';
    $timezone = 'America/Chicago';
    $date = new dateObject($input, $timezone);
    $value = $date->format('c');
    $expected = '2009-03-07T10:30:00-06:00';
    $this->assertEqual($expected, $value, "Test new dateObject($input, $timezone): should be $expected, found $value.");

    // Same during daylight savings time.
    $input = '2009-06-07 10:30';
    $timezone = 'America/Chicago';
    $date = new dateObject($input, $timezone);
    $value = $date->format('c');
    $expected = '2009-06-07T10:30:00-05:00';
    $this->assertEqual($expected, $value, "Test new dateObject($input, $timezone): should be $expected, found $value.");

    // Create date object from date string.
    $input = '2009-03-07';
    $timezone = 'America/Chicago';
    $date = new dateObject($input, $timezone);
    $value = $date->format('c');
    $expected = '2009-03-07T00:00:00-06:00';
    $this->assertEqual($expected, $value, "Test new dateObject($input, $timezone): should be $expected, found $value.");

    // Same during daylight savings time.
    $input = '2009-06-07';
    $timezone = 'America/Chicago';
    $date = new dateObject($input, $timezone);
    $value = $date->format('c');
    $expected = '2009-06-07T00:00:00-05:00';
    $this->assertEqual($expected, $value, "Test new dateObject($input, $timezone): should be $expected, found $value.");

    // Create date object from date array, date only.
    $input = array('year' => 2010, 'month' => 2, 'day' => 28);
    $timezone = 'America/Chicago';
    $date = new dateObject($input, $timezone);
    $value = $date->format('c');
    $expected = '2010-02-28T00:00:00-06:00';
    $this->assertEqual($expected, $value, "Test new dateObject(array('year' => 2010, 'month' => 2, 'day' => 28), $timezone): should be $expected, found $value.");

    // Create date object from date array with hour.
    $input = array('year' => 2010, 'month' => 2, 'day' => 28, 'hour' => 10);
    $timezone = 'America/Chicago';
    $date = new dateObject($input, $timezone);
    $value = $date->format('c');
    $expected = '2010-02-28T10:00:00-06:00';
    $this->assertEqual($expected, $value, "Test new dateObject(array('year' => 2010, 'month' => 2, 'day' => 28, 'hour' => 10), $timezone): should be $expected, found $value.");

    // 0 = January 1, 1970 00:00:00 (UTC);
    // 1000000000 = September 9, 2001 01:46:40 (UTC);
    // Create date object from unix timestamp and convert it to a local date.
    $input = 0;
    $timezone = 'UTC';
    $date = new dateObject($input, $timezone);
    $value = $date->format('c');
    $expected = '1970-01-01T00:00:00+00:00';
    $this->assertEqual($expected, $value, "Test new dateObject($input, $timezone): should be $expected, found $value.");

    $expected = 'UTC';
    $value = $date->getTimeZone()->getName();
    $this->assertEqual($expected, $value, "The current timezone is $value: should be $expected.");
    $expected = 0;
    $value = $date->getOffset();
    $this->assertEqual($expected, $value, "The current offset is $value: should be $expected.");

    $timezone = 'America/Los_Angeles';
    $date->setTimezone(new DateTimeZone($timezone));
    $value = $date->format('c');
    $expected = '1969-12-31T16:00:00-08:00';
    $this->assertEqual($expected, $value, "Test \$date->setTimezone(new DateTimeZone($timezone)): should be $expected, found $value.");

    $expected = 'America/Los_Angeles';
    $value = $date->getTimeZone()->getName();
    $this->assertEqual($expected, $value, "The current timezone should be $expected, found $value.");
    $expected = '-28800';
    $value = $date->getOffset();
    $this->assertEqual($expected, $value, "The current offset should be $expected, found $value.");

    // Convert the local version of a timestamp to UTC.
    $input = 0;
    $timezone = 'America/Los_Angeles';
    $date = new dateObject($input, $timezone);
    $offset = $date->getOffset();
    $value = $date->format('c');
    $expected = '1969-12-31T16:00:00-08:00';
    $this->assertEqual($expected, $value, "Test new dateObject($input, $timezone):  should be $expected, found $value.");

    $expected = 'America/Los_Angeles';
    $value = $date->getTimeZone()->getName();
    $this->assertEqual($expected, $value, "The current timezone should be $expected, found $value.");
    $expected = '-28800';
    $value = $date->getOffset();
    $this->assertEqual($expected, $value, "The current offset should be $expected, found $value.");

    $timezone = 'UTC';
    $date->setTimezone(new DateTimeZone($timezone));
    $value = $date->format('c');
    $expected = '1970-01-01T00:00:00+00:00';
    $this->assertEqual($expected, $value, "Test \$date->setTimezone(new DateTimeZone($timezone)): should be $expected, found $value.");

    $expected = 'UTC';
    $value = $date->getTimeZone()->getName();
    $this->assertEqual($expected, $value, "The current timezone should be $expected, found $value.");
    $expected = '0';
    $value = $date->getOffset();
    $this->assertEqual($expected, $value, "The current offset should be $expected, found $value.");

    // Create date object from datetime string and convert it to a
    // local date.
    $input = '1970-01-01 00:00:00';
    $timezone = 'UTC';
    $date = new dateObject($input, $timezone);
    $value = $date->format('c');
    $expected = '1970-01-01T00:00:00+00:00';
    $this->assertEqual($expected, $value, "Test new dateObject('$input', '$timezone'): should be $expected, found $value.");

    $expected = 'UTC';
    $value = $date->getTimeZone()->getName();
    $this->assertEqual($expected, $value, "The current timezone is $value: should be $expected.");
    $expected = 0;
    $value = $date->getOffset();
    $this->assertEqual($expected, $value, "The current offset is $value: should be $expected.");

    $timezone = 'America/Los_Angeles';
    $date->setTimezone(new DateTimeZone($timezone));
    $value = $date->format('c');
    $expected = '1969-12-31T16:00:00-08:00';
    $this->assertEqual($expected, $value, "Test \$date->setTimezone(new DateTimeZone($timezone)): should be $expected, found $value.");

    $expected = 'America/Los_Angeles';
    $value = $date->getTimeZone()->getName();
    $this->assertEqual($expected, $value, "The current timezone should be $expected, found $value.");
    $expected = '-28800';
    $value = $date->getOffset();
    $this->assertEqual($expected, $value, "The current offset should be $expected, found $value.");

    // Convert the local version of a datetime string to UTC.
    $input = '1969-12-31 16:00:00';
    $timezone = 'America/Los_Angeles';
    $date = new dateObject($input, $timezone);
    $offset = $date->getOffset();
    $value = $date->format('c');
    $expected = '1969-12-31T16:00:00-08:00';
    $this->assertEqual($expected, $value, "Test new dateObject('$input', '$timezone'):  should be $expected, found $value.");

    $expected = 'America/Los_Angeles';
    $value = $date->getTimeZone()->getName();
    $this->assertEqual($expected, $value, "The current timezone should be $expected, found $value.");
    $expected = '-28800';
    $value = $date->getOffset();
    $this->assertEqual($expected, $value, "The current offset should be $expected, found $value.");

    $timezone = 'UTC';
    $date->setTimezone(new DateTimeZone($timezone));
    $value = $date->format('c');
    $expected = '1970-01-01T00:00:00+00:00';
    $this->assertEqual($expected, $value, "Test \$date->setTimezone(new DateTimeZone($timezone)): should be $expected, found $value.");

    $expected = 'UTC';
    $value = $date->getTimeZone()->getName();
    $this->assertEqual($expected, $value, "The current timezone should be $expected, found $value.");
    $expected = '0';
    $value = $date->getOffset();
    $this->assertEqual($expected, $value, "The current offset should be $expected, found $value.");

     // Create year-only date.
    $input = '2009';
    $timezone = NULL;
    $format = 'Y';
    $date = new dateObject($input, $timezone, $format);
    $value = $date->format('Y');
    $expected = '2009';
    $this->assertEqual($expected, $value, "Test new dateObject($input, $timezone, $format): should be $expected, found $value.");

     // Create month and year-only date.
    $input = '2009-10';
    $timezone = NULL;
    $format = 'Y-m';
    $date = new dateObject($input, $timezone, $format);
    $value = $date->format('Y-m');
    $expected = '2009-10';
    $this->assertEqual($expected, $value, "Test new dateObject($input, $timezone, $format): should be $expected, found $value.");

     // Create time-only date.
    $input = '0000-00-00T10:30:00';
    $timezone = NULL;
    $format = 'Y-m-d\TH:i:s';
    $date = new dateObject($input, $timezone, $format);
    $value = $date->format('H:i:s');
    $expected = '10:30:00';
    $this->assertEqual($expected, $value, "Test new dateObject($input, $timezone, $format): should be $expected, found $value.");

     // Create time-only date.
    $input = '10:30:00';
    $timezone = NULL;
    $format = 'H:i:s';
    $date = new dateObject($input, $timezone, $format);
    $value = $date->format('H:i:s');
    $expected = '10:30:00';
    $this->assertEqual($expected, $value, "Test new dateObject($input, $timezone, $format): should be $expected, found $value.");

    // Test for invalid month names when we are using a short version
    // of the month
    $input = '23 abc 2012';
    $timezone = NULL;
    $format = 'd M Y';
    $date = new dateObject($input, $timezone, $format);
    $this->assertNotEqual(count($date->errors), 0, "$input contains an invalid month name and produces errors.");

     // Test for invalid hour.
    $input = '0000-00-00T45:30:00';
    $timezone = NULL;
    $format = 'Y-m-d\TH:i:s';
    $date = new dateObject($input, $timezone, $format);
    $this->assertNotEqual(count($date->errors), 0, "$input contains an invalid hour and produces errors.");

     // Test for invalid day.
    $input = '0000-00-99T05:30:00';
    $timezone = NULL;
    $format = 'Y-m-d\TH:i:s';
    $date = new dateObject($input, $timezone, $format);
    $this->assertNotEqual(count($date->errors), 0, "$input contains an invalid day and produces errors.");

     // Test for invalid month.
    $input = '0000-75-00T15:30:00';
    $timezone = NULL;
    $format = 'Y-m-d\TH:i:s';
    $date = new dateObject($input, $timezone, $format);
    $this->assertNotEqual(count($date->errors), 0, "$input contains an invalid month and produces errors.");

     // Test for invalid year.
    $input = '11-08-01T15:30:00';
    $timezone = NULL;
    $format = 'Y-m-d\TH:i:s';
    $date = new dateObject($input, $timezone, $format);
    $this->assertNotEqual(count($date->errors), 0, "$input contains an invalid year and produces errors.");

    // Test for invalid year from date array. 10000 as a year will
    // create an exception error in the PHP DateTime object.
    $input = array('year' => 10000, 'month' => 7, 'day' => 8, 'hour' => 8, 'minute' => 0, 'second' => 0);
    $timezone = 'America/Chicago';
    $date = new dateObject($input, $timezone);
    $this->assertNotEqual(count($date->errors), 0, "$input contains an invalid month and produces errors.");

    // Test for invalid month from date array.
    $input = array('year' => 2010, 'month' => 27, 'day' => 8, 'hour' => 8, 'minute' => 0, 'second' => 0);
    $timezone = 'America/Chicago';
    $date = new dateObject($input, $timezone);
    $this->assertNotEqual(count($date->errors), 0, "$input contains an invalid month and produces errors.");

    // Test for invalid hour from date array.
    $input = array('year' => 2010, 'month' => 2, 'day' => 28, 'hour' => 80, 'minute' => 0, 'second' => 0);
    $timezone = 'America/Chicago';
    $date = new dateObject($input, $timezone);
    $this->assertNotEqual(count($date->errors), 0, "$input contains an invalid hour and produces errors.");

    // Test for invalid minute from date array.
    $input = array('year' => 2010, 'month' => 7, 'day' => 8, 'hour' => 8, 'minute' => 88, 'second' => 0);
    $timezone = 'America/Chicago';
    $date = new dateObject($input, $timezone);
    $this->assertNotEqual(count($date->errors), 0, "$input contains an invalid month and produces errors.");

  }

  /**
   * @todo.
   */
  public function tearDown() {
    variable_del('date_first_day');
    parent::tearDown();
  }
}
