<?php

use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Client;

/**
 * Features context.
 */
class RestContext extends BehatContext
{
    private $variables = array();
    private $uri;
    private $method = 'get';

    private $currentUser = 'anonymous';
    private $token  = '';
    private $secret = '';
    private $nonce  = '';

    private $users = array(
        'anonymous' => array(
            'username' => 'anonymous@xizlr.net',
            'password' => ''
        ),
        'admin' => array(
            'username' => 'ken.lalobo@xizlr.net',
            'password' => 'password'
        )
    );

    private $resources = array(
        'user' => array(
            'section' => 'account',
            'resource' => 'users'
        ),
        'security token' => array(
            'section' => 'security',
            'resource' => 'tokens'
        )
    );

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->_parameters = $parameters;
        $baseUrl = $this->getParameter('base_url');
        $client = new Client(['base_url' => $baseUrl]);
        $this->_client = $client;
    }

    public function getParameter($name)
    {
        if (count($this->_parameters) === 0) {
            throw new \Exception('Parameters not loaded!');
        } else {
            $parameters = $this->_parameters;
            return (isset($parameters[$name])) ? $parameters[$name] : null;
        }
    }

    /**
     * @Given /^I am an "([^"]*)" user without a valid security token$/
     */
    public function iAmAnUserWithoutAValidSecurityToken($userType)
    {
        $this->currentUser = $userType;
        $this->token = '';
    }

    /**
     * @Given /^I am an "([^"]*)" user with a valid security token$/
     */
    public function iAmAnUserWithAValidSecurityToken($userType)
    {
        $this->currentUser = $userType;
        if (empty($this->token)) {
            $this->iAmAnUserWithoutAValidSecurityToken('anonymous');
            $this->iWantToCreateANew('security token');
            $this->iHaveAOf('username', $this->users[$userType]['username']);
            $this->iCreateTheResource();

            $data = json_decode($this->_response->getBody(true), true);

            $this->currentUser = $userType;
            $this->token       = $data['response']['token'];
            $this->secret      = $data['response']['secret'];
            $this->nonce       = $data['response']['nonce'];
            if (empty($this->token) || empty($this->secret) || empty($this->nonce)) {
                throw new Exception('token invalid');
            }
        }
    }

    /**
     * @Given /^I want to create a new "([^"]*)"$/
     */
    public function iWantToCreateANew($resource)
    {
        $this->uri = '/v1/'.$this->resources[$resource]['section'].'/'.$this->resources[$resource]['resource'];
        $this->method = 'post';
        $this->variables = array();
    }

    /**
     * @Given /^I have "([^"]*)" set to "([^"]*)"$/
     */
    public function iHaveSetTo($key, $value)
    {
        $this->variables[$key] = $value;
    }

    /**
     * @Given /^I have a "([^"]*)" of "([^"]*)"$/
     */
    public function iHaveAOf($key, $value)
    {
        $this->iHaveSetTo($key, $value);
    }

    /**
     * @When /^I request a "([^"]*)" with an ID of "([^"]*)"$/
     */
    public function iRequestAWithAnIdOf($resource, $id)
    {
        $uri = '/v1/'.$this->resources[$resource]['section'].'/'.$this->resources[$resource]['resource'].'/'.$id;
        $this->method = 'get';
        $this->variables = array();
        $this->iRequest($uri);
    }

    /**
     * @When /^I create the resource$/
     */
    public function iCreateTheResource()
    {
        $this->iRequest($this->uri);
    }

    /**
     * @When /^I reuse a nonce in a request$/
     */
    public function iReuseANonceInARequest()
    {
        $this->iRequestAWithAnIdOf('user', 'current');
        $this->iRequestAWithAnIdOf('user', 'current');
    }

    /**
     * @When /^I do not reuse a nonce in a request$/
     */
    public function iDoNotReuseANonceInARequest()
    {
        $this->iRequestAWithAnIdOf('user', 'current');
        $this->nonce = $this->_response->getHeader('X-HMAC-Nonce');
        $this->iRequestAWithAnIdOf('user', 'current');
    }


    /**
     * @When /^I request "([^"]*)"$/
     */
    public function iRequest($uri)
    {
        try {
            $date = date('r');
            $user = $this->users[$this->currentUser];
            $queryString = http_build_query($this->variables);

            $authenticationMessage = hash_hmac(
                'sha256',
                strtolower($this->method).$uri.$queryString.$date.$this->nonce,
                hash('sha256', $this->secret.hash('sha256', $user['password']))
            );

            $headers = array(
                'Authorization' => ' hmac '.$user['username'].':'.$this->token.':'.$authenticationMessage,
                'Date' => $date
            );

            switch ($this->method) {
                case 'get':
                    $url = $uri.(empty($queryString) == false?'?'.$queryString:'');
                    $response = $this->_client->get($url, array('headers' => $headers));
                    break;
                default:
                    $response = $this->_client->post($uri, array('headers' => $headers, 'body' => $this->variables));
                    break;
            }
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $response =$e->getResponse();
        }
        //hash_hmac('sha1', "Message", "Secret Key");
        $this->_response = $response;
    }

    /**
     * @Then /^the response should be JSON$/
     */
    public function theResponseShouldBeJson()
    {
        $data = json_decode($this->_response->getBody(true), true);
        if (empty($data)) {
            throw new Exception("Response was not JSON\n" . $this->_response);
        }
    }

    /**
     * @Then /^the response status code should be (\d+)$/
     */
    public function theResponseStatusCodeShouldBe($httpStatus)
    {
        if ((string)$this->_response->getStatusCode() !== $httpStatus) {
            throw new \Exception('HTTP code does not match '.$httpStatus.' (actual: '.$this->_response->getStatusCode().')');
        }
    }

    /**
     * @Given /^the response should have a "([^"]*)" property$/
     */
    public function theResponseShouldHaveAProperty($propertyName)
    {
        $data = json_decode($this->_response->getBody(true), true);
        if (!empty($data)) {
            if (!isset($data['response'][$propertyName])) {
                throw new Exception("Property '".$propertyName."' is not set!\n");
            }
        } else {
            throw new Exception("Response was not JSON\n" . $this->_response->getBody(true));
        }
    }

    /**
     * @Given /^The "([^"]*)" Header Contains "([^"]*)"$/
     */
    public function theHeaderContains($headerKey, $partialValue)
    {
        $headerValue = $this->_response->getHeader($headerKey);
        if (strripos($headerValue, $partialValue) === false) {
            throw new Exception($partialValue." not found in header: ".$headerKey);
            
        }
    }

    /**
     * @Given /^the "([^"]*)" property should equal "([^"]*)"$/
     */
    public function thePropertyShouldEqual($propertyName, $propertyValue)
    {
        $data = json_decode($this->_response->getBody(true), true);
 
        if (!empty($data)) {
            if (!isset($data['response'][$propertyName])) {
                throw new Exception("Property '".$propertyName."' is not set!\n");
            }
            if ($data['response'][$propertyName] !== $propertyValue) {
                throw new \Exception('Property value mismatch! (given: '.$propertyValue.', match: '.$data['response'][$propertyName].')');
            }
        } else {
            throw new Exception("Response was not JSON\n" . $this->_response->getBody(true));
        }
    }

    /**
     * @Given /^The "([^"]*)" Header should be a "([^"]*)"$/
     */
    public function theHeaderShouldBeA($headerKey, $type)
    {
        $headerValue = $this->_response->getHeader($headerKey);
        if (empty($headerValue)) {
            throw new \Exception($headerKey.' is empty');
        }
        $this->isA($headerValue, $type);
    }

    /**
     * @Given /^the "([^"]*)" property should be a "([^"]*)"$/
     */
    public function thePropertyShouldBeA($propertyKey, $type)
    {
        $data = json_decode($this->_response->getBody(true), true);
 
        if (!empty($data)) {
            if (!isset($data['response'][$propertyKey])) {
                throw new Exception("Property '".$propertyKey."' is not set!\n");
            }

            $this->isA($data['response'][$propertyKey], $type);
        } else {
            throw new Exception("Response was not JSON\n" . $this->_response->getBody(true));
        }
    }

    /**
     * @Given /^The "([^"]*)" Header should be empty$/
     */
    public function theHeaderShouldBeEmpty($headerKey)
    {
        $headerValue = $this->_response->getHeader($headerKey);
        if (!empty($headerValue)) {
            throw new \Exception($headerKey.' is  not empty');
        }
    }

    /**
     * @Given /^"([^"]*)" is A "([^"]*)"$/
     */
    public function isA($value, $type)
    {
        switch($type){
            case "uuidv4":
                if (!preg_match("/[a-f0-9]{8}-?[a-f0-9]{4}-?4[a-f0-9]{3}-?[89ab][a-f0-9]{3}-?[a-f0-9]{12}/i", strtolower($value))) {
                    throw new \Exception($value.' is not a '.$type);
                }
        }
    }
}
