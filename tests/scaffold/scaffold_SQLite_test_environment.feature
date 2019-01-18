Feature: scaffold SQLite test environment
  In order to be able to run tests without a MySQL server
  As a user initializing wp-browser
  I need to be able to select a SQLite based test environment and have it set up for me

  Scenario: choosing to use a SQLite based setup should result in a completely configured wp-browser installation
    Given I am initializing wp-browser
    And pick default answers
    When I reply "yes" to the question "Would you like to scaffold a SQLite based testing setup?"
    And I reply "9002" to the question "What localhost port should the WordPress installation use?"
    And pick default answers
    Then I should receive confirmation WordPress was configured to run from the vendor folder with SQLite

