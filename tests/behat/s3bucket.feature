@repository @repository_s3bucket @_file_upload
Feature: S3 bucket repository should throw no errors
  An admin should be able to configure the plugin

  @javascript
  Scenario: Install the plugin
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And I log in as "admin"
    And I navigate to "Plugins > Repositories > Manage repositories" in site administration
    Then I should see "Amazon S3"
    And I should see "Amazon S3 bucket"