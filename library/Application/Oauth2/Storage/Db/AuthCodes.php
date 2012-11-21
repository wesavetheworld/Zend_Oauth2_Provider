<?php
require_once 'Zend/Db/Table/Abstract.php';
require_once "IOAuth2/Storage/Db.php";

class IOAuth2_Storage_Db_AuthCodes extends Zend_Db_Table_Abstract
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
