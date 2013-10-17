Feature: Add MW UGC
    In order to post ugc content
    as a website user
    I can use ajax
    and the resulting response should be shown immediately
    
    Scenario: Logged in user sees add ugc form
        Given I am logged in as "simon" "simon"
        When I am on "/memorywall/show/3/simons-awesome-wall"
        Then I should see "Add yer own stuff"
    
    @javascript
    Scenario: ajax adding invalid ugc shows errors 
        Given I am logged in as "simon" "simon"
        When I am on "/memorywall/show/3/simons-awesome-wall"
        And I fill in "Title" with "t"
        When I press "Add it"
        When I wait for errors to show
        Then I should see "there was a problem with that"

        
        

    
    
