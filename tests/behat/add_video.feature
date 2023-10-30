@mod @mod_video @mod_video_add
Feature: Add video activities
  In order to deliver video content to other users
  As a teacher
  I need to add video activities to moodle courses

  @javascript
  Scenario: Add a video
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Video" to section "1" and I fill the form with:
      | Name        | Test video                     |
      | Type        | Vimeo                          |
      | Description | Testing out vimeo in behat     |
      | Video ID    | 212928250                      |
    And I am on the "Test video" "video activity" page logged in as student1
    Then I wait until the video player is ready
    And I wait until the Plyr play button appears
    And I click on the play button
    Then the video should start playing
