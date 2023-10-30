@mod @mod_video
Feature: Video playback functionality
  In order to engage with educational content
  As a student
  I need to be able to play videos

  Background:
    Given the following "courses" exist:
      | shortname | fullname  |
      | C1        | Course 1  |
    And the following "users" exist:
      | username | firstname |
      | teacher  | Teacher   |
      | student  | Student   |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
      | student | C1     | student        |
    And the following "activities" exist:
      | activity        | name         | idnumber | course | type    | videoid     | debug |
      | video           | Test youtube | 1        | C1     | youtube | jNQXAC9IVRw | 1     |
      | video           | Test vimeo   | 2        | C1     | vimeo   | 449787858   | 1     |

  @javascript
  Scenario: User can play a youtube video
    When I am on the "Test youtube" "video activity" page logged in as "student"
    Then I wait until the video player is ready
    And I wait until the Plyr play button appears
    And I click on the play button
    Then the video should start playing

  @javascript
  Scenario: User can play a vimeo video
    When I am on the "Test vimeo" "video activity" page logged in as "student"
    Then I wait until the video player is ready
    And I wait until the Plyr play button appears
    And I click on the play button
    Then the video should start playing