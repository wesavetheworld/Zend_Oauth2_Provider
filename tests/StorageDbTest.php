<?php

class Application_Oauth2_StorageDbTest extends PHPUnit_Framework_TestCase
{

    /**
     * 
     * @var Application_Oauth2_Provider_Storage_Db
     */
    private $_storage;

    public function setUp()
    {
        $this->_storage = new Application_Oauth2_Provider_Storage_Db(array('driverOptions' => array('type' => 'pdo_sqlite', 'dbname' => 'database.sqlite')));
    }
    
    
    public function testConstruct()
    {
        // Test Zend_Config
        $config = array(
            'driverOptions' => array(
                    'type' => 'pdo_sqlite',
                    'dbname' => 'database.sqlite'
            )
        );
    
        $zend_config = new Zend_Config($config);
    
        $obj = new Application_Oauth2_Provider_Storage_Db($config);
        $this->assertInstanceOf('Application_Oauth2_Provider_Storage_Db', $obj);
    
        $obj = new Application_Oauth2_Provider_Storage_Db($zend_config);
        $this->assertInstanceOf('Application_Oauth2_Provider_Storage_Db', $obj);
            
        try {
            $obj = new Application_Oauth2_Provider_Storage_Db('ops');
            $this->fail('Application_Oauth2_Provider_Storage_Db cannot accept a string');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }
}