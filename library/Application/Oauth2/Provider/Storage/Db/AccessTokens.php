<?php
require_once 'Zend/Db/Table/Abstract.php';

class Application_Oauth2_Provider_Storage_Db_AccessTokens extends Zend_Db_Table_Abstract
{
    /**
     * @var string
     */
    protected $_name = 'oauth_access_tokens';

    /**
     * @var string
     */
    protected $_primary = 'token';
	
}
