<?php

class Application_Oauth2_ProviderTest extends PHPUnit_Framework_TestCase
{

    /**
     * 
     * @var Application_Oauth2_Provider
     */
    private $_provider;

    public function setUp()
    {
        $dbStorage = new Application_Oauth2_Provider_Storage_Db(array('driverOptions' => array('type' => 'pdo_sqlite', 'dbname' => 'database.sqlite')));
        $this->_provider = new Application_Oauth2_Provider($dbStorage);
    }

    public function testConstruct()
    {
        $dbStorage = new Application_Oauth2_Provider_Storage_Db(array('driverOptions' => array('type' => 'pdo_sqlite', 'dbname' => 'database.sqlite')));
        $provider = new Application_Oauth2_Provider($dbStorage);
        
        $this->assertInstanceOf('Application_Oauth2_Provider', $provider);
        
        
        try {
            $obj = new Application_Oauth2_Provider('ops');
            $this->fail('Application_Oauth2_Provider cannot accept a string');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }
    
    public function testDefaultOptions()
    {
        $this->assertEquals(3600, $this->_provider->getOption(Application_Oauth2_Provider::CONFIG_ACCESS_LIFETIME));
        $this->assertEquals(1209600, $this->_provider->getOption(Application_Oauth2_Provider::CONFIG_REFRESH_LIFETIME));
        $this->assertEquals(30, $this->_provider->getOption(Application_Oauth2_Provider::CONFIG_AUTH_LIFETIME));
        $this->assertEquals(array(), $this->_provider->getOption(Application_Oauth2_Provider::CONFIG_SUPPORTED_SCOPES));
        $this->assertEquals('bearer', $this->_provider->getOption(Application_Oauth2_Provider::CONFIG_TOKEN_TYPE));
        $this->assertEquals('Service', $this->_provider->getOption(Application_Oauth2_Provider::CONFIG_WWW_REALM));
        $this->assertEquals(false, $this->_provider->getOption(Application_Oauth2_Provider::CONFIG_ENFORCE_INPUT_REDIRECT));
        $this->assertEquals(false, $this->_provider->getOption(Application_Oauth2_Provider::CONFIG_ENFORCE_STATE));
    }
    
}
