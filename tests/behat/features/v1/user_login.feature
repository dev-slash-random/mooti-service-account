Feature: User Login
 
 I would like to be able to login as various types of user
 
Scenario: Anonymous user cannot access restricted sections
  Given I am an "anonymous" user without a valid security token
  When I request a "user" with an ID of "current"
  Then the response should be JSON
  And The "X-HMAC-Nonce" Header should be empty
  And the response status code should be 401
  And the response should have a "message" property
  And the "message" property should equal "Access Denied"

Scenario: Admin user cannot access restricted sections without a token
  Given I am an "admin" user without a valid security token
  When I request a "user" with an ID of "current"
  Then the response should be JSON
  And The "X-HMAC-Nonce" Header should be empty
  And the response status code should be 401
  And the response should have a "message" property
  And the "message" property should equal "Access denied. The request was invalid"

Scenario: Get A security Token 
  Given I am an "anonymous" user without a valid security token
  And I want to create a new "security token"
  And I have a "username" of "admin"
  When I create the resource
  Then the response should be JSON
  And The "X-HMAC-Nonce" Header should be empty 
  And the response status code should be 201
  And the response should have a "token" property
  And the "token" property should be a "uuidv4"
  And the "secret" property should be a "uuidv4"
  And the "nonce" property should be a "uuidv4"

Scenario: Access my user details
  Given I am an "admin" user with a valid security token
  When I request a "user" with an ID of "current"
  Then the response should be JSON
  And The "X-HMAC-Nonce" Header should be a "uuidv4" 
  And the response status code should be 200
  And the response should have a "username" property
  And the "username" property should equal "ken.lalobo@xizlr.net"

Scenario: user cannot reuse a nonce
  Given I am an "admin" user with a valid security token
  When I reuse a nonce in a request
  Then the response should be JSON
  And The "X-HMAC-Nonce" Header should be empty
  And the response status code should be 401
  And the response should have a "message" property
  And the "message" property should equal "Access denied. The request was invalid"

Scenario: user can make more than one request
  Given I am an "admin" user with a valid security token
  When I do not reuse a nonce in a request
  Then the response should be JSON
  And The "X-HMAC-Nonce" Header should be a "uuidv4" 
  And the response status code should be 200