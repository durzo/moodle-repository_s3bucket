@repository @repository_s3bucket
Feature: S3 bucket repository should throw no errors
  An admin should be able to configure the plugin

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "users" exist:
      | username | email | firstname | lastname |
      | student | s@example.com | Student | 1 |
      | teacher | t@example.com | Teacher | 1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student | C1 | student |
      | teacher | C1 | editingteacher |
    And I log in as "admin"
    And I navigate to "Plugins > Repositories > Manage repositories" in site administration
    And I should see "Amazon S3 bucket"
    And I should not see "0 Site-wide common instance(s)"
    And I enable repository "s3bucket"
    And I navigate to "Plugins > Repositories > Amazon S3 bucket" in site administration
    And I click on "Create a repository instance" "button"
    And I set the following fields to these values:
        | name        | Testrepo      |
        | bucket_name | Testbucket    |
    And I click on "Save" "button"
    Then I should see "Required"
    And I set the field "Access key" to "KeleiqoakaKEHS"
    And I set the field "Secret key" to "KeleiqoakaKEHS"
    And I click on "Save" "button"
    Then I should not see "Required"
    And I should see "Allow users to add a repository instance into the course"
    And I should see "Allow users to add a repository instance into the user context"
    When I click on "Allow users to add a repository instance into the course" "checkbox"
    And I click on "Allow users to add a repository instance into the user context" "checkbox"
    And I click on "Save" "button"

  @javascript
  Scenario: An admin can add a user and course instances
    When I navigate to "Plugins > Repositories > Manage repositories" in site administration
    Then I should see "Amazon S3 bucket"
    And I should see "1 Site-wide common instance(s)"
    And I follow "Preferences" in the user menu
    Then I should see "Repositories"
    And I follow "Manage instances"
    Then I should see "Amazon S3 bucket"
    And I should see "Create"
    And I am on "Course 1" course homepage with editing mode on
    And I navigate to "Repositories" in current page administration
    Then I should see "Amazon S3 bucket"
    And I should see "Create"

  @javascript
  Scenario: A teacher cannot add a user or course instance
    When I log out
    And I log in as "teacher"
    And I follow "Preferences" in the user menu
    Then I should see "Repositories"
    And I follow "Manage instances"
    Then I should not see "Amazon S3 bucket"
    And I am on "Course 1" course homepage with editing mode on
    And I navigate to "Repositories" in current page administration
    Then I should see "Amazon S3 bucket"
    And I should see "Create"

  @javascript
  Scenario: Add a user instance as a atudent
    When I log out
    And I log in as "student"
    And I follow "Preferences" in the user menu
    Then I should see "Repositories"
    And I follow "Manage instances"
    Then I should not see "Amazon S3 bucket"
    And I am on "Course 1" course homepage
    And I should not see "Amazon S3 bucket"
    