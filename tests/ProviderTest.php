<?php

class Application_Oauth2_ProviderdTest extends PHPUnit_Framework_TestCase
{


    public function setUp ()
    {
        $dbStorage = new Application_Oauth2_Provider_Storage_Db(array('driverOptions' => array('type' => 'pdo_sqlite', 'dbname' => 'database.sqlite')));
        $this->_provider = new Application_Oauth2_Provider($dbStorage);
    }

    public function testSomething ()
    {
        $this->assertInstanceOf('Application_Oauth2_Provider', $this->_provider);
    }
}
