Feature: Add MW UGC
    In order to post ugc content
    as a website user
    I need to use ajax

    Scenario: Searching for a page that does exist
        Given I am on "/login"
        When I fill in "username" with "simon"
        Then I should see "login"
        

    
    
