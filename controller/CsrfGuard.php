<?php
/**
 * CSRF Guard
 *
 * Use this middleware with your Slim Framework application
 * to protect you from CSRF attacks.
 *
 * USAGE
 *
 * $app = new \Slim\Slim();
 * $app->add(new \Slim\Extras\Middleware\CsrfGuard());
 *
 */
namespace Slim\Extras\Middleware;

class CsrfGuard extends \Slim\Middleware
{
	const MAX_TOKEN = 10;

	/**
	 * CSRF token key name.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * Constructor.
	 *
	 * @param string    $key        The CSRF token key name.
	 * @return void
	 */
	public function __construct($key = 'csrf_token')
	{
		if (! is_string($key) || empty($key) || preg_match('/[^a-zA-Z0-9\-\_]/', $key)) {
			throw new \OutOfBoundsException('Invalid CSRF token key "' . $key . '"');
		}

		$this->key = $key;
	}

	/**
	 * Call middleware.
	 *
	 * @return void
	 */
	public function call() 
	{
		// Attach as hook.
		$this->app->hook('slim.before', array($this, 'check'));

		// Call next middleware.
		$this->next->call();
	}

	/**
	 * Check CSRF token is valid.
	 * Note: Also checks POST data to see if a Moneris RVAR CSRF token exists.
	 *
	 * @return void
	 */
	public function check() {
		// Check sessions are enabled.
		if (session_id() === '') {
			throw new \Exception('Sessions are required to use the CSRF Guard middleware.');
		}

		// Validate the CSRF token.
		if (in_array($this->app->request()->getMethod(), array('POST', 'PUT', 'DELETE'))) {
			$userToken = $this->app->request()->post($this->key);
			if (!$this->check_token($userToken)) {
				$this->app->halt(400, 'Invalid or missing CSRF token.');
			}
		}

		// Assign CSRF token key and value to view.
		$token = $this->generate_token();
		$this->app->view()->appendData(array(
			'csrf_key'      => $this->key,
			'csrf_token'    => $token,
		));
	}

	protected function generate_token() {
		$tokens = isset($_SESSION[$this->key]) ? $_SESSION[$this->key] : array();
		if (count($tokens) >= self::MAX_TOKEN) {
			array_shift($tokens);
		}
		
		$token = sha1(session_id() . microtime() . rand() . uniqid());
		$tokens[] = $token;
		$_SESSION[$this->key] = $tokens;

		return $token;
	}

	protected function check_token($token) {
		$tokens = isset($_SESSION[$this->key]) ? $_SESSION[$this->key] : array();
		
		if (false !== ($pos = array_search($token, $tokens, true))) {
			unset($tokens[$pos]);
			$_SESSION[$this->key] = $tokens;

			return true;
		}
		return false;
	}
}