# features/login.feature

Feature: First have to log in
  In order to access to bank-crm
  As a guepe User
  I need to first log in.
  
Scenario: Not logged
	Given  I am on "/account"
	When I am not logged in
	Then I sould be redirected to login form
	
Scenario: Try to logged in
	Given I am on "/account"
	And fill in the following:
		|_username| guepe |
		|_password| essai1 |
	And press "_submit"
	Then I should be on "/account/"