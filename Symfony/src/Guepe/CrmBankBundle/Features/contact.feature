# features/contact.feature


Feature: Create an contact
  I try to create an contact
  I need to first log in.
  
Background:
	Given  I am on "/contact/"

Scenario:  Create a contact
	Given I am not logged in
	Then I should be redirected to login form
	And I fill in "username" with "guepe"
	And I fill in "password" with "essai1"
	And press "_submit"
	Then I should be on "/contact/"
	When I follow "Nouveau Contact"
	Then I should be on "/contact/add"
	Then I should see "Code postal"
	And fill in the following:
		|contact[lastname]|TestName|
	And press "Enregistrer"
	Then I should see "TestName"
	
@javascript
Scenario: Search a contact
	Given I am not logged in
	Then I should be redirected to login form
	And I fill in "username" with "guepe"
	And I fill in "password" with "essai1"
	And press "_submit"
	Then I should be on "/contact/"  
 	When I fill in "contactsearch[lastname]" with "Test"
	And press "Rechercher"
	Then I should see "TestName"
	When I fill in "contactsearch[lastname]" with "NoTest"
	And press "Rechercher"
	Then I should not see "TestName"
	Then I follow "Déconnexion"

