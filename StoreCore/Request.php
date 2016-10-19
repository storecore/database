<?php
namespace StoreCore;

/**
 * Client Request
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright (c) 2015-2016 StoreCore
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @package   StoreCore\Core
 * @version   0.1.0
 *
 * @api
 * @method void __construct ( void )
 * @method mixed|null get ( string $key )
 * @method string getAcceptEncoding ( void )
 * @method mixed|null getCookie ( string $cookie_name )
 * @method string getHostName ( void )
 * @method string getMethod ( void )
 * @method string getRequestPath ( void )
 * @method string|null getUserAgent ( void )
 * @method bool hasCookie ( string $cookie_name )
 * @method bool isSecure ( void )
 */
class Request
{
    const VERSION = '0.1.0';

    /** @var string $HostName */
    private $HostName;

    /** @var string $RequestMethod */
    private $RequestMethod = 'GET';

    /** @var string $RequestPath */
    private $RequestPath = '/';

    /**
     * @var array|null $Cookies
     * @var array|null $Get
     * @var array|null $Post
     * @var array      $Server
     */
    private $Cookies;
    private $Get;
    private $Post;
    private $Server;

    /**
     * @param void
     * @return void
     */
    public function __construct()
    {
        $magic_quotes_gpc = get_magic_quotes_gpc();

        // Set internal character encoding to UTF-8
        mb_internal_encoding('UTF-8');

        // Non-empty $_SERVER string variables
        $data = array();
        foreach ($_SERVER as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                if ($magic_quotes_gpc !== false) {
                    $value = stripslashes($value);
                }
                if (!empty($value)) {
                    $data[mb_strtoupper($key)] = strip_tags($value);
                }
            }
        }
        $this->Server = $data;

        /*
         * Set the HTTP request method (POST, HEAD, PUT, etc.) other than the
         * default method (GET).  The HTTP request method in the superglobal
         * PHP variable $_SERVER['REQUEST_METHOD'] is controlled by the client.
         * It MAY be considered reliable as long as the web server allows only
         * certain request methods.
         */
        if (
            isset($_SERVER['REQUEST_METHOD'])
            && ($_SERVER['REQUEST_METHOD'] !== $this->RequestMethod)
            && is_string($_SERVER['REQUEST_METHOD'])
        ) {
            if ($magic_quotes_gpc !== false) {
                $_SERVER['REQUEST_METHOD'] = stripslashes($_SERVER['REQUEST_METHOD']);
            }
            $this->RequestMethod = strtoupper(trim($_SERVER['REQUEST_METHOD']));
        }

        // Request path (URI without host)
        if (isset($_SERVER['REQUEST_URI'])) {
            if ($magic_quotes_gpc !== false) {
                $_SERVER['REQUEST_URI'] = stripslashes($_SERVER['REQUEST_URI']);
            }
            $this->setRequestPath($_SERVER['REQUEST_URI']);
            unset($this->Server['REQUEST_URI']);
        }

        // Posted data
        if ($this->getMethod() == 'POST') {
            $data = array();
            foreach ($_POST as $key => $value) {
                if (is_string($value)) {
                    $value = trim($value);
                    if ($magic_quotes_gpc !== false) {
                        $key = stripslashes($key);
                        $value = stripslashes($value);
                    }
                    $value = strip_tags($value);
                    if (!empty($value)) {
                        $data[mb_strtolower($key)] = $value;
                    }
                }
            }
            $this->Post = $data;
        }

        // Add cookie data
        if (isset($_COOKIE) && !empty($_COOKIE)) {
            $data = array();
            foreach ($_COOKIE as $name => $value) {
                if (is_string($name) && !empty($value)) {
                    if ($magic_quotes_gpc !== false) {
                        $name = stripslashes($name);
                        $value = stripslashes($value);
                    }
                    $name = mb_strtolower($name);
                    $data[$name] = $value;
                }
            }
            if (!empty($data)) {
                $this->Cookies = $data;
            }
        }
    }

    /**
     * Get a request value by name.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        if ($this->Post !== null && array_key_exists($key, $this->Post)) {
            return $this->Post[$key];
        } elseif ($this->Get !== null && array_key_exists($key, $this->Get)) {
            return $this->Get[$key];
        } else {
            return null;
        }
    }

    /**
     * Get the HTTP "Accept-Encoding" request-header field.
     *
     * @param void
     * @return string
     */
    public function getAcceptEncoding()
    {
        if (array_key_exists('HTTP_ACCEPT_ENCODING', $this->Server)) {
            return $this->Server['HTTP_ACCEPT_ENCODING'];
        }
        return (string)null;
    }

    /**
     * Get a cookie by name.
     *
     * @param string $cookie_name
     *   Case-insensitive name of a cookie.
     *
     * @return mixed|null
     *   Returns the contents of a cookie or null if the cookie does not exist.
     */
    public function getCookie($cookie_name)
    {
        $cookie_name = mb_strtolower($cookie_name, 'UTF-8');
        if ($this->hasCookie($cookie_name)) {
            return $this->Cookies[$cookie_name];
        }
        return null;
    }

    /**
     * Get the host name.
     *
     * @return string
     *   Returns the HTTP host being requested.
     */
    public function getHostName()
    {
        if ($this->HostName == null) {
            $this->setHostName();
        }
        return $this->HostName;
    }

    /**
     * Get the current request method.
     *
     * @param void
     * @return string
     */
    public function getMethod()
    {
        return $this->RequestMethod;
    }

    /**
     * Get the current request path.
     *
     * @param void
     * @return string
     */
    public function getRequestPath()
    {
        return $this->RequestPath;
    }

    /**
     * Get the HTTP User-Agent request-header field.
     *
     * @param void
     * @return string|null
     */
    public function getUserAgent()
    {
        if (array_key_exists('HTTP_USER_AGENT', $this->Server)) {
            return $this->Server['HTTP_USER_AGENT'];
        } else {
            return null;
        }
    }

    /**
     * Check if a cookie exists.
     *
     * @param string $cookie_name
     *   Case-insensitive name of a cookie.
     *
     * @return bool
     *   Returns true if the cookie exists, otherwise false.
     */
    public function hasCookie($cookie_name)
    {
        if (!is_array($this->Cookies)) {
            return false;
        }
        $cookie_name = mb_strtolower($cookie_name);
        return array_key_exists($cookie_name, $this->Cookies);
    }

    /**
     * Check if HTTP/S and SSL are used.
     *
     * @param void
     * @return bool
     */
    public function isSecure()
    {
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * Set the requested host name.
     *
     * @param void
     * @return void
     */
    private function setHostName()
    {
        if (isset($this->Server['HTTP_HOST']) && !empty($this->Server['HTTP_HOST'])) {
            $host_name = $this->Server['HTTP_HOST'];
        } else {
            $host_name = gethostname();
        }

        $host_name = preg_replace('/:\d+$/', '', $host_name);
        $host_name = mb_strtolower($host_name, 'UTF-8');
        $this->HostName = $host_name;
    }

    /**
     * Set the request path.
     *
     * @param string $path
     * @return void
     */
    private function setRequestPath($path)
    {
        $path = urldecode($path);
        $path = mb_strtolower($path, 'UTF-8');
        $path = str_ireplace('/index.php', '/', $path);
        $this->RequestPath = $path;
    }
}
