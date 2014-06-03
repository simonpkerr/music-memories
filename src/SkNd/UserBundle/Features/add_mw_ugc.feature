Feature: Add Memory Wall UGC
    In order to add ugc content
    as a website user
    I need the resulting response to be shown immediately on the wall
    
    @javascript
    Scenario: ajax adding ugc with invalid title shows errors 
        Given I am logged in as "simon" "simon"
        When I am on "/memorywall/show/3/simons-awesome-wall"
        And I fill in "Title" with "t"
        When I press "Add it"
        When I wait for errors to show
        Then I should see "Something went wrong there"

    @javascript
    Scenario: ajax adding ugc with valid details shows ugc
        Given I am logged in as "simon" "simon"
        When I am on "/memorywall/show/3/simons-awesome-wall"
        And I fill in "Title" with "simons ugc title"
        When I press "Add it"
        When I wait for ugc to appear
        Then I should see "simons ugc title"
        
    @javascript
    Scenario: ajax adding ugc with valid details shows ugc and can be immediately deleted
        Given I am logged in as "simon" "simon"
        When I am on "/memorywall/show/3/simons-awesome-wall"
        And I fill in "Title" with "simons ugc title"
        When I press "Add it"
        When I wait for ugc to appear
        When I press "Remove simons ugc title from this wall"
        Then I should not see "simons ugc title"

    @javascript
    Scenario: ajax adding ugc with valid details shows ugc and can be immediately edited

    @javascript
    Scenario: ajax adding ugc with valid details clears add ugc form and hides it
    
    