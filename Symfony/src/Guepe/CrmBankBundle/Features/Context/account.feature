# features/account.feature


Feature: Create an account
  I try to create an account
  I need to first log in.
  
Background:
	Given  I am on "/account/"

Scenario:  logged in
	Given I am not logged in
	Then I sould be redirected to login form
	And I fill in "username" with "guepe"
	And I fill in "password" with "essai1"
	And press "_submit"
	Then I should be on "/account/"
	When I go to "/account/add"
	Then I should be on "/account/add"
	Then I should see "Code postal"
