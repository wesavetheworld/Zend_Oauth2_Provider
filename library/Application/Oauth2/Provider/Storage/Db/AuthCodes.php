<?php

class Application_Oauth2_Provider_Storage_Db_AuthCodes extends Zend_Db_Table_Abstract
{
    /**
     * @var string
     */
    protected $_name = 'oauth_auth_codes';

    /**
     * @var string
     */
    protected $_primary = 'token';

    /**
     * @var mixed
     */
    protected $_sequence = true;
}
