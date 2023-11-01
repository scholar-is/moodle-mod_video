@mod @mod_video @mod_video_comments
Feature: Add comments to videos
  In order to increase engagement and provide feedback
  As a student
  I need to comment on a video

  Background:
    Given the following "users" exist:
      | username  | firstname | lastname | email                 |
      | teacher1  | Teacher   | 1        | teacher1@example.com  |
      | student1  | Student   | 1        | student1@example.com  |
    And the following "courses" exist:
      | fullname  | shortname | category |
      | Course 1  | C1        | 0        |
    And the following "course enrolments" exist:
      | user      | course | role           |
      | teacher1  | C1     | editingteacher |
      | student1  | C1     | student        |
    And the following "activities" exist:
      | activity        | name                     | idnumber | course | type    | videoid     | debug | comments |
      | video           | Test youtube             | 1        | C1     | youtube | jNQXAC9IVRw | 1     | 1        |
      | video           | Test youtube no comments | 2        | C1     | youtube | jNQXAC9IVRw | 1     | 0        |
    And I log in as "teacher1"
    And I am on the "Test youtube" "video activity" page
    And I click on ".comment-link" "css_element"
    And I set the field with xpath "//div[@class='comment-area']//textarea" to "Comment from teacher"
    And I click on "//div[@class='comment-area']//a[contains(text(), 'Save comment')]" "xpath_element"
    And I log out
    And I log in as "student1"
    And I am on the "Test youtube" "video activity" page
    And I click on ".comment-link" "css_element"
    And I set the field with xpath "//div[@class='comment-area']//textarea" to "Comment from student"
    And I click on "//div[@class='comment-area']//a[contains(text(), 'Save comment')]" "xpath_element"

  @javascript
  Scenario: Comment on a video
    Given I log in as "student1"
    And I am on the "Test youtube" "video activity" page
    Then I click on ".comment-link" "css_element"
    And I set the field with xpath "//div[@class='comment-area']//textarea" to "This is my comment"
    And I should see "Save comment"
    And I click on "//div[@class='comment-area']//a[contains(text(), 'Save comment')]" "xpath_element"
    And I should see "This is my comment"

  @javascript
  Scenario: Delete comment as teacher
    And I log in as "teacher1"
    And I am on the "Test youtube" "video activity" page
    And I click on ".comment-link" "css_element"
    # Delete 2nd (student) comment.
    And I click on "(//div[@class='comment-delete']/a)[2]" "xpath_element"
    And I should not see "Comment from student"

  @javascript
  Scenario: View comments without the ability to comment
    Given I log in as "teacher1"
    And I am on the "Course 1" "permissions" page
    And I override the system permissions of "Student" role with:
      | capability        | permission |
      | mod/video:comment | Prohibit   |
    And I log out
    And I log in as "student1"
    And I am on the "Test youtube" "video activity" page
    And I click on ".comment-link" "css_element"
    And I should see "Comment from teacher"
    And I should see "Comment from student"
    And I should not see "Save comment"
