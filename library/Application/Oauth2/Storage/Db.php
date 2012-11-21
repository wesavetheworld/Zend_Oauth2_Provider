<?php
require_once 'IOAuth2/OAuth2.php';
require_once 'IOAuth2/OAuth2Client.php';
require_once 'IOAuth2/IOAuth2Storage.php';
require_once 'IOAuth2/IOAuth2GrantUser.php';
require_once 'IOAuth2/Storage/Db/AccessTokens.php';
require_once 'IOAuth2/Storage/Db/AuthCodes.php';
require_once 'IOAuth2/Storage/Db/Clients.php';
require_once 'IOAuth2/Storage/Db/RefreshTokens.php';

class IOAuth2_Storage_Db implements IOAuth2GrantUser {

	/**
	 * User-provided configuration
	 *
	 * @var array
	 */
	protected $_options = array();
	
	/**
	 * @var IOAuth2_Storage_Db_AccessTokens
	 */
	protected $_accessTokenTable = null;
	
	/**
	 * @var IOAuth2_Storage_Db_AuthCodes
	 */
	protected $_authCodesTable = null;
	
	/**
	 * @var IOAuth2_Storage_Db_Clients
	 */
	protected $_clientsTable = null;
	
	/**
	 * @var IOAuth2_Storage_Db_RefreshTokens
	 */
	protected $_refreshTokenTable = null;

	/**
	 * 
	 * @param array $options
	 */
	public function __construct($options = array())
	{
		if ($options instanceof Zend_Config) {
			$options = $options->toArray();
		} elseif (!is_array($options)) {
			$options = array();
		}
		
		$this->_options = $options;
		
		if (isset($this->_options['dbAdapter'])
				&& $this->_options['dbAdapter'] instanceof Zend_Db_Adapter_Abstract) {
			$db = $this->_options['dbAdapter'];
		} else {
			$db = $this->_initDbAdapter();
		}
		
		$this->_accessTokenTable = new IOAuth2_Storage_Db_AccessTokens(array(
				'db' => $db,
		));
		
		$this->_authCodesTable = new IOAuth2_Storage_Db_AuthCodes(array(
				'db' => $db,
		));
		
		$this->_clientsTable = new IOAuth2_Storage_Db_Clients(array(
				'db' => $db,
		));
		
		$this->_refreshTokenTable = new IOAuth2_Storage_Db_RefreshTokens(array(
				'db' => $db,
		));
	}
	
	/**
	 * Initialize Db adapter using 'driverOptions' section of the _options array
	 *
	 * Throws an exception if the adapter cannot connect to DB.
	 *
	 * @return Zend_Db_Adapter_Abstract
	 * @throws Zend_Exception
	 */
	protected function _initDbAdapter()
	{
		$options = &$this->_options['driverOptions'];
		if (!array_key_exists('type', $options)) {
			require_once 'Zend/Exception.php';
			throw new Zend_Exception("Configuration array must have a key for 'type' for the database type to use");
		}
	
		if (!array_key_exists('host', $options)) {
			require_once 'Zend/Exception.php';
			throw new Zend_Exception("Configuration array must have a key for 'host' for the host to use");
		}
	
		if (!array_key_exists('username', $options)) {
			require_once 'Zend/Exception.php';
			throw new Zend_Exception("Configuration array must have a key for 'username' for the username to use");
		}
	
		if (!array_key_exists('password', $options)) {
			require_once 'Zend/Exception.php';
			throw new Zend_Exception("Configuration array must have a key for 'password' for the password to use");
		}
	
		if (!array_key_exists('dbname', $options)) {
			require_once 'Zend/Exception.php';
			throw new Zend_Exception("Configuration array must have a key for 'dbname' for the database to use");
		}
	
		$type = $options['type'];
		unset($options['type']);
	
		try {
			$db = Zend_Db::factory($type, $options);
		} catch (Zend_Db_Exception $e) {
			require_once 'Zend/Queue/Exception.php';
			throw new Zend_Exception('Error connecting to database: ' . $e->getMessage(), $e->getCode(), $e);
		}
	
		return $db;
	}
	
	
	
	/**
	 * Handle PDO exceptional cases.
	 */
	private function handleException($e) {
		echo 'Database error: ' . $e->getMessage();
		exit();
	}
	
	/**
	 * Implements IOAuth2Storage::checkClientCredentials().
	 */
	public function checkClientCredentials($client_id, $client_secret = NULL) {
		
		try {
			$list = $this->_clientsTable->find($client_id);
			if (count($list) === 0) {
				return false;
			}
			$client = $list->current();
			
			if ($client_secret === NULL) {
				return $client !== FALSE;
			}
			
			return $this->checkPassword($client_secret, $client['client_secret'], $client_id);
		} catch (Zend_Exception $e) {
			$this->handleException($e);
		}
	}
	
	/**
	 * Implements IOAuth2Storage::getRedirectUri().
	 */
	public function getClientDetails($client_id) {
		try {
			
			$list = $this->_clientsTable->find($client_id);
			if (count($list) === 0) {
				return false;
			}
			$client = $list->current();
			
			if ($client === FALSE) {
				return FALSE;
			}
				
			return isset($result['redirect_uri']) && $client['redirect_uri'] ? $client : NULL;
		} catch (Zend_Exception $e) {
			$this->handleException($e);
		}
	}
	
	/**
	 * Implements IOAuth2Storage::getAccessToken().
	 */
	public function getAccessToken($oauth_token) {
		return $this->getToken($oauth_token, FALSE);
	}
	
	/**
	 * Implements IOAuth2Storage::setAccessToken().
	 */
	public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = NULL) {
		$this->setToken($oauth_token, $client_id, $user_id, $expires, $scope, FALSE);
	}
	
	/**
	 * @see IOAuth2Storage::getRefreshToken()
	 */
	public function getRefreshToken($refresh_token) {
		return $this->getToken($refresh_token, TRUE);
	}
	
	/**
	 * @see IOAuth2Storage::setRefreshToken()
	 */
	public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = NULL) {
		return $this->setToken($refresh_token, $client_id, $user_id, $expires, $scope, TRUE);
	}
	
	/**
	 * @see IOAuth2Storage::unsetRefreshToken()
	 */
	public function unsetRefreshToken($refresh_token) {
		try {
			
			$list = $this->_refreshTokenTable->find($refresh_token);
			if (count($list) === 0) {
				return false;
			}
			$token = $list->current();
			
			if ($token === FALSE) {
				return FALSE;
			}
			
			$token->delete();
		} catch (Zend_Exception $e) {
			$this->handleException($e);
		}
	}
	
	/**
	 * Implements IOAuth2Storage::getAuthCode().
	 */
	public function getAuthCode($code) {
		try {
			$list = $this->_authCodesTable->find($code);
			if (count($list) === 0) {
				return false;
			}
			$auth_code = $list->current();
				
			if ($auth_code === FALSE) {
				return FALSE;
			}
			
			return $auth_code;
		} catch (Zend_Exception $e) {
			$this->handleException($e);
		}
	}
	
	/**
	 * Implements IOAuth2Storage::setAuthCode().
	 */
	public function setAuthCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = NULL) {
		try {
			
			$data = array(
				'code' => $code,
				'client_id' => $client_id,
				'user_id' => $user_id,
				'redirect_uri' => $redirect_uri,
				'expires' => $expires,
				'scope' => $scope
			);
			
			$auth_code = $this->_authCodesTable->createRow($data);
			$auth_code->save();
		} catch (Zend_Exception $e) {
			$this->handleException($e);
		}
	}
	
	/**
	 * @see IOAuth2Storage::checkRestrictedGrantType()
	 */
	public function checkRestrictedGrantType($client_id, $grant_type) {
		
		if ($grant_type==OAuth2::GRANT_TYPE_USER_CREDENTIALS) {
			return true;
		}
		
		return false;
	}
	
	
	public function checkUserCredentials($client_id, $email, $password) {
		$auth    = Zend_Auth::getInstance();
		
		// todo - implement login
		$user = $modelUsers->fetchByEmail($email);
		
		$adapter = new Auth_Adapter();
		$adapter->setUser($user);
		$adapter->setPassword($password);
		$result  = $auth->authenticate($adapter);
		
		if (!$result->isValid()) {
			throw new Application_Oauth2_Provider_Exception(OAuth2::HTTP_BAD_REQUEST, 'authentication_failure', $result->getMessages()[0]);
        }
        
		return array('user_id' => $user->getId());
	}
	
	/**
	 * Creates a refresh or access token
	 *
	 * @param string $token - Access or refresh token id
	 * @param string $client_id
	 * @param mixed $user_id
	 * @param int $expires
	 * @param string $scope
	 * @param bool $isRefresh
	 */
	protected function setToken($token, $client_id, $user_id, $expires, $scope, $isRefresh = TRUE) {
		try {
			
			$data = array(
				'token' => $token,
				'client_id' => $client_id,
				'user_id' => $user_id,
				'expires' => $expires,
				'scope' => $scope
			);
			
			if ($isRefresh) {
				$token = $this->_refreshTokenTable->createRow($data);
			} else {
				$token = $this->_accessTokenTable->createRow($data);
			}

			$token->save();
		} catch (Zend_Exception $e) {
			$this->handleException($e);
		}
	}
	
	/**
	 * Retrieves an access or refresh token.
	 *
	 * @param string $token
	 * @param bool $refresh
	 */
	protected function getToken($token, $isRefresh = true) {
		try {
			
			if ($isRefresh) {
				$list = $this->_refreshTokenTable->find($token);
			} else {
				$list = $this->_accessTokenTable->find($token);
			}
			
			if (count($list) === 0) {
				return false;
			}
			$token = $list->current();
			
			if ($token === FALSE) {
				return FALSE;
			}
				
			return $token;
		} catch (Zend_Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * Checks the client's secret.
	 *
	 * @param string $try
	 * @param string $client_id
	 * @param string $client_secret
	 */
	protected function checkPassword($try, $client_secret, $client_id) {
		return $try == $client_secret;
	}
	
}