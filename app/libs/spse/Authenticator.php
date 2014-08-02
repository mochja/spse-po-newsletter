<?php namespace spse;

use Nette\Security as NS;

/**
 * Users authenticator.
 *
 * @author     Jan Mochnak
 * @package    spse
 */
class Authenticator extends \Nette\Object implements NS\IAuthenticator
{
    protected $admins;

    public function __construct(array $admins)
    {
        $this->admins = $admins;
    }

    /**
     * Performs an authentication
     * @param  array
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;

        if ( !in_array($username, $this->admins) ) {
            throw new NS\AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
        }

	    $imap = \imap_open("{localhost:143/imap/novalidate-cert}", $username, $password);

	    if (!$imap) {
            throw new NS\AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
        }
		\imap_close($imap);
        
        return new NS\Identity($username, 'admin', $credentials);
    }

}
