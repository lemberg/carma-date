<?php
/**
 * @file
 * Date validation tests.
 */

namespace Drupal\date\Tests;

use Drupal\simpletest\WebTestBase;

class DateValidationTest extends DateFieldBase {

  /**
   * @todo.
   */
  public static function getInfo() {
    return array(
      'name' => 'Date Validation',
      'description' => 'Test date validation.',
      'group' => 'Date',
    );
  }

  /**
   * @todo.
   */
  public function testValidation() {
    // Attempts to create text date field stored as a date with default settings
    // (from input which is not valid).
    foreach (array('date', 'datestamp', 'datetime') as $field_type) {
      foreach (array('date_select', 'date_popup', 'date_text') as $widget_type) {
        $field_name = 'field_test';
        $label = 'Test';
        $options = array(
          'label' => $label,
          'field_name' => $field_name,
          'field_type' => $field_type,
          'widget_type' => $widget_type,
          'input_format' => 'm/d/Y - H:i',
        );
        $this->createDateField($options);

        // Malformed date test won't work on date_select, which won't allow
        // invalid input.
        if ($widget_type != 'date_select') {
          $this->malFormedDate($field_name, $field_type, $widget_type);
        }

        $this->wrongGranularity($field_name, $field_type, $widget_type);
        $this->deleteDateField($label);
      }
    }
  }

  /**
   * @todo.
   */
  function malFormedDate($field_name, $field_type, $widget_type) {
    // Tests that date field filters improper dates.
    $edit = array();
    $edit['title'] = $this->randomName(8);
    $edit['body[und][0][value]'] = $this->randomName(16);
    if ($widget_type == 'date_select') {
      $edit[$field_name . '[und][0][value][year]'] = '2011';
      $edit[$field_name . '[und][0][value][month]'] = '15';
      $edit[$field_name . '[und][0][value][day]'] = '49';
      $edit[$field_name . '[und][0][value][hour]'] = '10';
      $edit[$field_name . '[und][0][value][minute]'] = '30';
    }
    elseif ($widget_type == 'date_text') {
      $edit[$field_name . '[und][0][value][date]'] = '15/49/2011 - 10:30';
    }
    elseif ($widget_type == 'date_popup') {
      $edit[$field_name . '[und][0][value][date]'] = '15/49/2011';
      $edit[$field_name . '[und][0][value][time]'] = '10:30';
    }
    $this->drupalPost('node/add/story', $edit, t('Save'));
    $should_not_be = $edit['title'] . "has been created";
    $this->assertNoText($should_not_be, "Correctly blocked creation of node with invalid month and day for a $field_type field using the $widget_type widget.");
    $this->assertText('The month is invalid.', "Correctly blocked invalid month for a $field_type field using the $widget_type widget.");
    $this->assertText('The day is invalid.', "Correctly blocked invalid day for a $field_type field using the $widget_type widget.");

    // Test two-digit entry for year where 4-digit is expected.
    if ($widget_type == 'date_select') {
      $edit[$field_name . '[und][0][value][year]'] = '11';
      $edit[$field_name . '[und][0][value][month]'] = '12';
      $edit[$field_name . '[und][0][value][day]'] = '10';
      $edit[$field_name . '[und][0][value][hour]'] = '10';
      $edit[$field_name . '[und][0][value][minute]'] = '30';
    }
    elseif ($widget_type == 'date_text') {
      $edit[$field_name . '[und][0][value][date]'] = '12/10/11 - 10:30';
    }
    elseif ($widget_type == 'date_popup') {
      $edit[$field_name . '[und][0][value][date]'] = '12/10/11';
      $edit[$field_name . '[und][0][value][time]'] = '10:30';
    }
    $this->drupalPost('node/add/story', $edit, t('Save'));
    $should_not_be = $edit['title'] . " has been created";
    $this->assertNoText($should_not_be, "Correctly blocked creation of node with invalid year for a $field_type field using the $widget_type widget.");
    $should_be = 'The year is invalid. Please check that entry includes four digits.';
    $this->assertText($should_be, "Correctly blocked two digit year for a $field_type field using the $widget_type widget.");

    // Test invalid hour/minute entry for time.
    if ($widget_type == 'date_select') {
      $edit[$field_name . '[und][0][value][year]'] = '2011';
      $edit[$field_name . '[und][0][value][month]'] = '12';
      $edit[$field_name . '[und][0][value][day]'] = '10';
      $edit[$field_name . '[und][0][value][hour]'] = '29';
      $edit[$field_name . '[und][0][value][minute]'] = '95';
    }
    elseif ($widget_type == 'date_text') {
      $edit[$field_name . '[und][0][value][date]'] = '12/10/2011 - 29:95';
    }
    elseif ($widget_type == 'date_popup') {
      $edit[$field_name . '[und][0][value][date]'] = '12/10/2011';
      $edit[$field_name . '[und][0][value][time]'] = '29:95';
    }
    $this->drupalPost('node/add/story', $edit, t('Save'));
    $should_not_be = $edit['title'] . " has been created";
    $this->assertNoText($should_not_be, "Correctly blocked creation of node with invalid time for a $field_type field using the $widget_type widget.");
    $should_be = 'The hour is invalid.';
    $this->assertText($should_be, "Correctly blocked invalid hour for a $field_type field using the $widget_type widget.");
    $should_be = 'The minute is invalid.';
    $this->assertText($should_be, "Correctly blocked invalid minute for a $field_type field using the $widget_type widget.");

  }

  /**
   * @todo.
   */
  public function wrongGranularity($field_name, $field_type, $widget_type) {
    // Create a node with incorrect granularity -- missing time.
    $edit = array();
    $edit['title'] = $this->randomName(8);
    $edit['body[und][0][value]'] = $this->randomName(16);
    if ($widget_type == 'date_select') {
      $edit[$field_name . '[und][0][value][year]'] = '2011';
      $edit[$field_name . '[und][0][value][month]'] = '12';
      $edit[$field_name . '[und][0][value][day]'] = '10';
      $edit[$field_name . '[und][0][value][hour]'] = '';
      $edit[$field_name . '[und][0][value][minute]'] = '';
    }
    elseif ($widget_type == 'date_text') {
      $edit[$field_name . '[und][0][value][date]'] = '12/10/2011';
    }
    elseif ($widget_type == 'date_popup') {
      $edit[$field_name . '[und][0][value][date]'] = '12/10/2011';
      $edit[$field_name . '[und][0][value][time]'] = '';
    }
    $this->drupalPost('node/add/story', $edit, t('Save'));
    $should_not_be = $edit['title'] . " has been created";
    $this->assertNoText($should_not_be, "Correctly blocked creation of node with missing time for a $field_type field using the $widget_type widget.");
    $this->assertText('invalid', "Marked form with missing time as invalid for a $field_type field using the $widget_type widget.");
  }
}
