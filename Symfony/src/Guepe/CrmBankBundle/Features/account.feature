# features/account.feature


Feature: Create an account
  I try to create an account
  I need to first log in.
  
Background:
	Given  I am on "/account/"

Scenario:  logged in
	Given I am not logged in
	Then I should be redirected to login form
	And I fill in "username" with "guepe"
	And I fill in "password" with "essai1"
	And press "_submit"
	Then I should be on "/account/"
	When I follow "Nouveau Compte"
	Then I should be on "/account/add"
	Then I should see "Code postal"
	And fill in the following:
		|account[name]|TestName|
	And press "Enregistrer"
	Then I should see "TestName"



@javascript
Scenario: Search a account
	Given I am not logged in
	Then I should be redirected to login form
	And I fill in "username" with "guepe"
	And I fill in "password" with "essai1"
	And press "_submit"
	Then I should be on "/account/"  
 	When I fill in "accountsearch[motcle]" with "Test"
	And press "Rechercher"
	Then I should see "TestName"
	When I fill in "accountsearch[motcle]" with "NoTest"
	And press "Rechercher"
	Then I should not see "TestName"
	Then I follow "Déconnexion"
