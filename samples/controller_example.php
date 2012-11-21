<?php
class Api_OauthController extends Zend_Controller_Action
{

	/**
	 * 
	 * @var Application_Oauth2_Provider
	 */
	var $_oauthProvider;
	
	public function init()
	{
		parent::init();
		
		// @todo implement an example
		$options = array(
			'dbAdapter' => $dbAdapter
		);
		
		$this->_oauthProvider = new Application_Oauth2_Provider(new Application_Oauth2_Provider_Storage_Db($options));
		$this->_oauthProvider->setVariable(Application_Oauth2_Provider::CONFIG_ACCESS_LIFETIME, 60*60*30);
	}
	
	public function accesstokenAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
				
		try {
			$token = $this->_oauthProvider->grantAccessToken();
			
			$access_token = $this->_oauthProvider->verifyAccessToken($token['access_token']);
			
			// @todo - implement an example
			$modelUsers = new Model_Users;
			$user = $modelUsers->fetch($access_token->user_id);
			
			if (!$user instanceof Model_User) {
				$this->returnError('Unable to find user');
			}
				
			$data = array(
				'access_token' => $token['access_token'],
				'user' => array (
					'userId' => $user->getId(),
					'userType' => $user->getUserRoleType(),
					'email' => $user->getEmail(),
					'fullname' => $user->getFullName(),
					'profilePicture' => $user->getUserAvatarImage()
				)
			);
			
			echo $this->_helper->json($data, array('enableJsonExprFinder' => true));
							
		} catch (Application_Oauth2_Provider_Exception $exception) {
			$exception->sendHttpResponse();
		}
	}

	/**
	 * return an oauth error
	 * @param string $errorMessage
	 */
	protected function returnError($errorMessage) {
	
		$data = array(
			'error' => 'authentication_failure',
			'error_description' => $errorMessage
		);
	
		echo $this->_helper->json($data, array('enableJsonExprFinder' => true));
	
	}
	
	
}
	