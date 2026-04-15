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
	When I follow "Nouveau Prospects"
	Then I should be on "/lead/add"
	Then I should see "Code postal"
	And fill in the following:
		|lead[name]|TestName|
	And press "Enregistrer"
	Then I should see "TestName"


@javascript
Scenario: Search a lead
	Given I am not logged in
	Then I should be redirected to login form
	And I fill in "username" with "guepe"
	And I fill in "password" with "essai1"
	And press "_submit"
	Then I should be on "/lead/"  
 	When I fill in "leadsearch[name]" with "Test"
	And press "Rechercher"
	Then I should see "TestName"
	When I fill in "leadsearch[name]" with "NoTest"
	And press "Rechercher"
	Then I should not see "TestName"
	Then I follow "Déconnexion"
