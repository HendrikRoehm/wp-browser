Feature: scaffold SQLite test environment
  In order to be able to run tests without a MySQL server
  As a user initializing wp-browser
  I need to be able to select a SQLite based test environment and have it set up for me

  Scenario: choosing to use a SQLite based setup should result in a completely configured wp-browser installation
    Given I want to initialize wp-browser interactively
    And I will reply "yes" to the question "Would you like to scaffold a SQLite and PHP built-in server based test WordPress installation?"
    And I will reply "9002" to the question "What localhost port should the WordPress installation use?"
    When I init WPBrowser
    Then I should see WordPress was configured to run from the vendor folder with SQLite

