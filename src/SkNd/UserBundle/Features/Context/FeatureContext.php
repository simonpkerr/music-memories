<?php

namespace SkNd\UserBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Feature context.
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    private $kernel;
    private $parameters;

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Sets HttpKernel instance.
     * This method will be automatically called by Symfony2Extension ContextInitializer.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    /**
     * @Then /^I wait for errors to show$/
     */
    public function iWaitForErrorsToShow()
    {
        //$container = $this->kernel->getContainer();
        //$container->get('some_service')->doSomethingWith($argument);
        $this->getSession()->wait(5000, "$('div.error').children().length > 0");
    }
    
    /**
     * @Given /^I am logged in as "([^"]*)" "([^"]*)"$/
     */
    public function iAmLoggedInAs($username, $password){
        $this->getSession()->visit($this->locatePath('/login'));
        $this->fillField('username', $username);
        $this->fillField('password', $password);
        $this->pressButton('Login');
        //$this->getSession('goutte')->setBasicAuth($username, $password);
                
    }
    
   

}
