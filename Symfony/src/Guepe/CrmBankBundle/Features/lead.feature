# features/lead.feature


Feature: Create an lead
  I try to create an lead
  I need to first log in.
  
Background:
	Given  I am on "/lead/"

Scenario:  logged in
	Given I am not logged in
	Then I should be redirected to login form
	And I fill in "username" with "guepe"
	And I fill in "password" with "essai1"
	And press "_submit"
	Then I should be on "/lead/"
	When I go to "/lead/add"
	Then I should be on "/lead/add"
	Then I should see "Code postal"
	And fill in the following:
		|lead[name]|TestName|
	And press "Enregistrer"
	Then I should see "TestName"
