<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class LocalBrowserHackIdentity extends CUserIdentity
{
  public function __construct()
  {
    return parent::__construct('admin', '');
  }

  /**
   * Authenticates a user.
   * @return boolean whether authentication succeeds.
   */
  public function authenticate()
  {
    // Auto-Login hack for access from NMT browser
    // try and limit to nmt platform with the SCRIPT_NAME check
    return ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' &&
       substr($_SERVER['SCRIPT_NAME'], 0, 12) === '/NMTDVR_web/');
  }
}
