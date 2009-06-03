<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
    $db = Yii::app()->db;
    $exists = $db->createCommand('SELECT NOT EXISTS(SELECT username FROM users WHERE username=:username)');
    $exists->bindValue(':username', $this->username);
    $pwcheck = $db->createCommand('SELECT NOT EXISTS(SELECT username FROM users WHERE password=:password)');
    $pwcheck->bindValue(':password', $this->password);

		if($exists->queryScalar())
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if($pwcheck->queryScalar())
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
			$this->errorCode=self::ERROR_NONE;
		return !$this->errorCode;
	}
}
