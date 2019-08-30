<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2019, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Input Class
 *
 * Pre-processes global input data for security
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Input
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/libraries/input.html
 */
class CI_Input {

	/**
	 * IP address of the current user
	 *
	 * @var	string
	 */
	protected $ip_address = FALSE;

	/**
	 * Allow GET array flag
	 *
	 * If set to FALSE, then $_GET will be set to an empty array.
	 *
	 * @var	bool
	 */
	protected $_allow_get_array = TRUE;

	/**
	 * Standardize new lines flag
	 *
	 * If set to TRUE, then newlines are standardized.
	 *
	 * @var	bool
	 */
	protected $_standardize_newlines;

	/**
	 * Enable XSS flag
	 *
	 * Determines whether the XSS filter is always active when
	 * GET, POST or COOKIE data is encountered.
	 * Set automatically based on config setting.
	 *
	 * @var	bool
	 */
	protected $_enable_xss = FALSE;

	/**
	 * Enable CSRF flag
	 *
	 * Enables a CSRF cookie token to be set.
	 * Set automatically based on config setting.
	 *
	 * @var	bool
	 */
	protected $_enable_csrf = FALSE;

	/**
	 * List of all HTTP request headers
	 *
	 * @var array
	 */
	protected $headers = array();

	/**
	 * Raw input stream data
	 *
	 * Holds a cache of php://input contents
	 *
	 * @var	string
	 */
	protected $_raw_input_stream;

	/**
	 * Parsed input stream data
	 *
	 * Parsed from php://input at runtime
	 *
	 * @see	CI_Input::input_stream()
	 * @var	array
	 */
	protected $_input_stream;

	protected $security;
	protected $uni;

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * Determines whether to globally enable the XSS processing
	 * and whether to allow the $_GET array.
	 *
	 * @return	void
	 */
	public function __construct()
	{
        // 表示是否允许用户使用$_GET全局变量，如果设置为不允许，会在输入类构造函数处理中将$_GET清空。
		$this->_allow_get_array		= (config_item('allow_get_array') !== FALSE);

        // $config['global_xss_filtering']表示是否开启XSS全局防御的标志位，如果设置为允许，则会对用户输入和Cookie的内容中进行XSS过滤。
		$this->_enable_xss		= (config_item('global_xss_filtering') === TRUE);

		// $config['csrf_protection']表示是否开启CSRF防御，如果设置为允许，则会在对表单数据进行处理时进行CSRF方法的检查。
		$this->_enable_csrf		= (config_item('csrf_protection') === TRUE);

		// $config['standardize_newlines']表示是否标准化换行符，如果设置为允许，则会在对表单数据进行处理时用PHP_EOL代替数据中的换行符。
		$this->_standardize_newlines	= (bool) config_item('standardize_newlines');

		$this->security =& load_class('Security', 'core');

		// Do we need the UTF-8 class?
		if (UTF8_ENABLED === TRUE)
		{
			$this->uni =& load_class('Utf8', 'core');
		}

		// Sanitize global arrays 清理全局数组，即处理表单数据,$_GET,$_POST,$_COOKIE去掉不合要求的字符
		$this->_sanitize_globals();

		// CSRF Protection check
		if ($this->_enable_csrf === TRUE && ! is_cli())
		{
			$this->security->csrf_verify();
		}

		log_message('info', 'Input Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch from array
	 *
	 * Internal method used to retrieve values from global arrays.
	 *
	 * @param	array	&$array		$_GET, $_POST, $_COOKIE, $_SERVER, etc.
	 * @param	mixed	$index		Index for item to be fetched from $array
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	protected function _fetch_from_array(&$array, $index = NULL, $xss_clean = NULL)
	{
		is_bool($xss_clean) OR $xss_clean = $this->_enable_xss;

		// If $index is NULL, it means that the whole $array is requested
        // $index = NULL 表示获取所有
		isset($index) OR $index = array_keys($array);

		// allow fetching multiple keys at once
        // 如果Index是数组则单独获取一次
		if (is_array($index))
		{
			$output = array();
			foreach ($index as $key)
			{
				$output[$key] = $this->_fetch_from_array($array, $key, $xss_clean);
			}

			return $output;
		}

		if (isset($array[$index]))
		{
			$value = $array[$index];
		}
		elseif (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1) // Does the index contain array notation
		{
		    // '/(?:^[^\[]+)|\[[^]]*\]/' 待确认
			$value = $array;
			for ($i = 0; $i < $count; $i++)
			{
				$key = trim($matches[0][$i], '[]');
				if ($key === '') // Empty notation will return the value as array
				{
					break;
				}

				if (isset($value[$key]))
				{
					$value = $value[$key];
				}
				else
				{
					return NULL;
				}
			}
		}
		else
		{
			return NULL;
		}

		return ($xss_clean === TRUE)
			? $this->security->xss_clean($value)
			: $value;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the GET array
	 *
	 * @param	mixed	$index		Index for item to be fetched from $_GET
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public function get($index = NULL, $xss_clean = NULL)
	{
		return $this->_fetch_from_array($_GET, $index, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the POST array
	 *
	 * @param	mixed	$index		Index for item to be fetched from $_POST
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public function post($index = NULL, $xss_clean = NULL)
	{
		return $this->_fetch_from_array($_POST, $index, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from POST data with fallback to GET
	 *
	 * @param	string	$index		Index for item to be fetched from $_POST or $_GET
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public function post_get($index, $xss_clean = NULL)
	{
		return isset($_POST[$index])
			? $this->post($index, $xss_clean)
			: $this->get($index, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from GET data with fallback to POST
	 *
	 * @param	string	$index		Index for item to be fetched from $_GET or $_POST
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public function get_post($index, $xss_clean = NULL)
	{
		return isset($_GET[$index])
			? $this->get($index, $xss_clean)
			: $this->post($index, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the COOKIE array
	 *
	 * @param	mixed	$index		Index for item to be fetched from $_COOKIE
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public function cookie($index = NULL, $xss_clean = NULL)
	{
		return $this->_fetch_from_array($_COOKIE, $index, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the SERVER array
	 *
	 * @param	mixed	$index		Index for item to be fetched from $_SERVER
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public function server($index, $xss_clean = NULL)
	{
		return $this->_fetch_from_array($_SERVER, $index, $xss_clean);
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch an item from the php://input stream
	 *
	 * Useful when you need to access PUT, DELETE or PATCH request data.
	 *
	 * @param	string	$index		Index for item to be fetched
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public function input_stream($index = NULL, $xss_clean = NULL)
	{
		// Prior to PHP 5.6, the input stream can only be read once,
		// so we'll need to check if we have already done that first.
		if ( ! is_array($this->_input_stream))
		{
			// $this->raw_input_stream will trigger __get().
			parse_str($this->raw_input_stream, $this->_input_stream);
			is_array($this->_input_stream) OR $this->_input_stream = array();
		}

		return $this->_fetch_from_array($this->_input_stream, $index, $xss_clean);
	}

	// ------------------------------------------------------------------------

	/**
	 * Set cookie
	 *
	 * Accepts an arbitrary number of parameters (up to 7) or an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param	string|mixed[]	$name		Cookie name or an array containing parameters
	 * @param	string		$value		Cookie value
	 * @param	int		$expire		Cookie expiration time in seconds
	 * @param	string		$domain		Cookie domain (e.g.: '.yourdomain.com')
	 * @param	string		$path		Cookie path (default: '/')
	 * @param	string		$prefix		Cookie name prefix
	 * @param	bool		$secure		Whether to only transfer cookies via SSL
	 * @param	bool		$httponly	Whether to only makes the cookie accessible via HTTP (no javascript)
	 * @return	void
	 */
	public function set_cookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = NULL, $httponly = NULL)
	{
		if (is_array($name))
		{
			// always leave 'name' in last place, as the loop will break otherwise, due to $$item
			foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name') as $item)
			{
				if (isset($name[$item]))
				{
					$$item = $name[$item];
				}
			}
		}

		if ($prefix === '' && config_item('cookie_prefix') !== '')
		{
			$prefix = config_item('cookie_prefix');
		}

		if ($domain == '' && config_item('cookie_domain') != '')
		{
			$domain = config_item('cookie_domain');
		}

		if ($path === '/' && config_item('cookie_path') !== '/')
		{
			$path = config_item('cookie_path');
		}

		$secure = ($secure === NULL && config_item('cookie_secure') !== NULL)
			? (bool) config_item('cookie_secure')
			: (bool) $secure;

		$httponly = ($httponly === NULL && config_item('cookie_httponly') !== NULL)
			? (bool) config_item('cookie_httponly')
			: (bool) $httponly;

		if ( ! is_numeric($expire))
		{
			$expire = time() - 86500;
		}
		else
		{
			$expire = ($expire > 0) ? time() + $expire : 0;
		}

		setcookie($prefix.$name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the IP Address
	 *
	 * Determines and validates the visitor's IP address.
     * 返回当前用户的 IP 地址，如果 IP 地址无效，则返回 '0.0.0.0'
     * 该方法会根据 $config['proxy_ips'] 配置，来返回 HTTP_X_FORWARDED_FOR、 HTTP_CLIENT_IP、HTTP_X_CLIENT_IP 或 HTTP_X_CLUSTER_CLIENT_IP 。
	 *
	 * @return	string	IP address
	 */
	public function ip_address()
	{
		if ($this->ip_address !== FALSE)
		{
			return $this->ip_address;
		}

        /**
         * 当服务器使用了代理时，REMOTER_ADDR获取的就是代理服务器的IP了，
         * 需要从HTTP_X_FORWARDED_FOR、HTTP_CLIENT_IP、HTTP_X_CLIENT_IP、HTTP_X_CLUSTER_CLIENT_IP或其他设定的值中获取。
         * 这里设定的就是代理服务器的IP，逗号分隔。
         */
		$proxy_ips = config_item('proxy_ips');
		if ( ! empty($proxy_ips) && ! is_array($proxy_ips))
		{
			$proxy_ips = explode(',', str_replace(' ', '', $proxy_ips));
		}

        /**
         * REMOTE_ADDR代表着客户端的IP，但是这个客户端是相对服务器而言的，也就是实际上与服务器相连的机器的IP（建立tcp连接的那个），这个值是不可伪造的，
         * 如果没有代理的话，这个值就是用户实际的IP值，有代理的话，用户的请求会经过代理再到服务器，这个时候REMOTE_ADDR会被设置为代理机器的IP值。
         */
		$this->ip_address = $this->server('REMOTE_ADDR');

		if ($proxy_ips)
		{
            /**
             * HTTP_X_FORWARDED_FOR: 是有标准定义,用来识别通过HTTP代理或负载均衡方式连接到Web服务器的客户端最原始的IP地址,
             *      有了代理就获取不了用户的真实IP，由此X-Forwarded-For应运而生，它是一个非正式协议，
             *      在请求转发到代理的时候代理会添加一个X-Forwarded-For头，将连接它的客户端IP（也就是你的上网机器IP）加到这个头信息里，
             *      这样末端的服务器就能获取真正上网的人的IP了
             * HTTP_CLIENT_IP: 头是有的，只是未成标准，不一定服务器都实现了
             * HTTP_X_CLIENT_IP:
             * HTTP_X_CLUSTER_CLIENT_IP:
             */
			foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP') as $header)
			{
				if (($spoof = $this->server($header)) !== NULL)
				{
					// Some proxies typically list the whole chain of IP
					// addresses through which the client has reached us.
					// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
					sscanf($spoof, '%[^,]', $spoof);

					// 非ipv4/ipv6则返回false
					if ( ! $this->valid_ip($spoof))
					{
						$spoof = NULL;
					}
					else
					{
						break;
					}
				}
			}

			if ($spoof)
			{
				for ($i = 0, $c = count($proxy_ips); $i < $c; $i++)
				{
					// Check if we have an IP address or a subnet
					if (strpos($proxy_ips[$i], '/') === FALSE)
					{
						// An IP address (and not a subnet) is specified.
						// We can compare right away.
						if ($proxy_ips[$i] === $this->ip_address)
						{
							$this->ip_address = $spoof;
							break;
						}

						continue;
					}

					// We have a subnet ... now the heavy lifting begins
                    // ipv6:    xxxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx
                    // ipv4:    10.120.78.40
					isset($separator) OR $separator = $this->valid_ip($this->ip_address, 'ipv6') ? ':' : '.';

					// If the proxy entry doesn't match the IP protocol - skip it
					if (strpos($proxy_ips[$i], $separator) === FALSE)
					{
						continue;
					}

					// Convert the REMOTE_ADDR IP address to binary, if needed
                    // isset()只有在$ip, $sprintf全部设置时才返回true，这里返回的是false，因为$ip $sprintf未被设置
					if ( ! isset($ip, $sprintf))
					{
					    // : 表示IPv6
						if ($separator === ':')
						{
							// Make sure we're have the "full" IPv6 format
                            /**
                             *  str_repeat() 重复一个字符串
                             *  substr_count() 计算字符串出现次数
                             *  :: 表示0位压缩，比如FF01::1101表示FF01:0:0:0:0:0:0:1101
                             */
							$ip = explode(':',
								str_replace('::',
									str_repeat(':', 9 - substr_count($this->ip_address, ':')),
									$this->ip_address
								)
							);

							for ($j = 0; $j < 8; $j++)
							{
								$ip[$j] = intval($ip[$j], 16);
							}

							$sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
						}
						else
						{
							$ip = explode('.', $this->ip_address);
							$sprintf = '%08b%08b%08b%08b';
						}

						// vsprintf(): 返回格式化字符串
						$ip = vsprintf($sprintf, $ip);
					}

					// Split the netmask length off the network address
                    // sscanf根据format将$proxy_ips[$i]格式化为$netaddr和$masklen
					sscanf($proxy_ips[$i], '%[^/]/%d', $netaddr, $masklen);

					// Again, an IPv6 address is most likely in a compressed form
					if ($separator === ':')
					{
						$netaddr = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($netaddr, ':')), $netaddr));
						for ($j = 0; $j < 8; $j++)
						{
							$netaddr[$j] = intval($netaddr[$j], 16);
						}
					}
					else
					{
						$netaddr = explode('.', $netaddr);
					}

					// Convert to binary and finally compare
					if (strncmp($ip, vsprintf($sprintf, $netaddr), $masklen) === 0)
					{
						$this->ip_address = $spoof;
						break;
					}
				}
			}
		}

		if ( ! $this->valid_ip($this->ip_address))
		{
			return $this->ip_address = '0.0.0.0';
		}

		return $this->ip_address;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate IP Address
	 *
	 * @param	string	$ip	IP address
	 * @param	string	$which	IP protocol: 'ipv4' or 'ipv6'
	 * @return	bool
	 */
	public function valid_ip($ip, $which = '')
	{
		switch (strtolower($which))
		{
			case 'ipv4':
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6':
				$which = FILTER_FLAG_IPV6;
				break;
			default:
				$which = NULL;
				break;
		}

        /**
         * filter_var(): 使用特定的过滤器过滤一个变量
         * $ip: 待过滤的变量。注意：标量的值在过滤前，会被转换成字符串。
         * FILTER_VALIDATE_IP: validate ip,詳見https://www.php.net/manual/zh/filter.filters.validate.php
         * $witch: 一个选项的关联数组，或者按位区分的标示。如果过滤器接受选项，可以通过数组的 "flags" 位去提供这些标示。
         */
		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $which);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch User Agent string
	 * HTTP_USER_AGENT: 获取用户的所有信息， 比如，Mozilla/5.0 平台操作系统（包括版本号） 引擎版本  浏览器（包括版本号）
	 * @return	string|null	User Agent string or NULL if it doesn't exist
	 */
	public function user_agent($xss_clean = NULL)
	{
		return $this->_fetch_from_array($_SERVER, 'HTTP_USER_AGENT', $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Sanitize Globals
	 *
	 * Internal method serving for the following purposes:
	 *
	 *	- Unsets $_GET data, if query strings are not enabled
	 *	- Cleans POST, COOKIE and SERVER data
	 * 	- Standardizes newline characters to PHP_EOL
	 *  表单处理方法
	 * @return	void
	 */
	protected function _sanitize_globals()
	{
		// Is $_GET data allowed? If not we'll set the $_GET to an empty array
		if ($this->_allow_get_array === FALSE)
		{
			$_GET = array();
		}
		elseif (is_array($_GET))
		{
		    // ?k=aa&v=bb&**(*=$%##
            /**
             * $_GET = [
             *      'k' => string 'aa' (length=2)
             *      'v' => string 'bb' (length=2)
             *      '**(*' => string '$%' (length=2)
             * ]
             */
			foreach ($_GET as $key => $val)
			{
				$_GET[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
			/**
             * $_GET = [
             *      'k' => string 'aa'
             *      'v' => string 'bb'
             *      '**(*' => string '$%'
             *      0 => string '$%'
             * ]
             */
		}

		// Clean $_POST Data
		if (is_array($_POST))
		{
            /**
             * $_POST  = [
             *      'k' => string 'aa'
             *      'v' => string 'bb'
             *      '**(*' => string '$%##'
             * ]
             */
			foreach ($_POST as $key => $val)
			{
				$_POST[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
			/**
             *  $POST = [
             *      'k' => string 'aa'
             *      'v' => string 'bb'
             *      '**(*' => string '$%##'
             *      0 => string '$%##'
             * ]
             */
		}

		// Clean $_COOKIE Data
		if (is_array($_COOKIE))
		{
			// Also get rid of specially treated cookies that might be set by a server
			// or silly application, that are of no use to a CI application anyway
			// but that when present will trip our 'Disallowed Key Characters' alarm
			// http://www.ietf.org/rfc/rfc2109.txt
			// note that the key names below are single quoted strings, and are not PHP variables
			unset(
				$_COOKIE['$Version'],
				$_COOKIE['$Path'],
				$_COOKIE['$Domain']
			);

			// $_COOKIE 的话不符合的key直接删掉
			foreach ($_COOKIE as $key => $val)
			{
				if (($cookie_key = $this->_clean_input_keys($key)) !== FALSE)
				{
					$_COOKIE[$cookie_key] = $this->_clean_input_data($val);
				}
				else
				{
					unset($_COOKIE[$key]);
				}
			}
		}

		// Sanitize PHP_SELF
        // strip_tags(): 去除 HTML 和 PHP 标记
		$_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);

		log_message('debug', 'Global POST, GET and COOKIE data sanitized');
	}

	// --------------------------------------------------------------------

	/**
	 * Clean Input Data
	 *
	 * Internal method that aids in escaping data and
	 * standardizing newline characters to PHP_EOL.
	 *
	 * @param	string|string[]	$str	Input string(s)
	 * @return	string
	 */
	protected function _clean_input_data($str)
	{
	    // 如果$str是个数组，则对数组的键和值进行过滤
		if (is_array($str))
		{
			$new_array = array();
			foreach (array_keys($str) as $key)
			{
				$new_array[$this->_clean_input_keys($key)] = $this->_clean_input_data($str[$key]);
			}
			return $new_array;
		}

		/* We strip slashes if magic quotes is on to keep things consistent

		   NOTE: In PHP 5.4 get_magic_quotes_gpc() will always return 0 and
		         it will probably not exist in future versions at all.
		5.4.0開始 魔术引号功能从PHP中移除！
		小于5.4的版本如果magic_quotes_gpc的配置选项开启则反引用一个引用字符串
		*/
		if ( ! is_php('5.4') && get_magic_quotes_gpc())
		{
		    // stripslashes(): 返回一个去除转义反斜线后的字符串（\' 转换为 ' 等等）。
            //                  双反斜线（\\）被转换为单个反斜线（\）。
			$str = stripslashes($str);
		}

		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE)
		{
			$str = $this->uni->clean_string($str);
		}

		// Remove control characters 删除不可见字符
		$str = remove_invisible_characters($str, FALSE);

		// Standardize newlines if needed 默认不进行替换，参考$config['standardize_newlines']
		if ($this->_standardize_newlines === TRUE)
		{
			return preg_replace('/(?:\r\n|[\r\n])/', PHP_EOL, $str);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Clean Keys
	 *
	 * Internal method that helps to prevent malicious users
	 * from trying to exploit keys we make sure that keys are
	 * only named with alpha-numeric text and a few other items.
	 *
	 * @param	string	$str	Input string
	 * @param	bool	$fatal	Whether to terminate script exection
	 *				or to return FALSE if an invalid
	 *				key is encountered
     *  表单key数据处理
	 * @return	string|bool
	 */
	protected function _clean_input_keys($str, $fatal = TRUE)
	{
        //如果$str中有不允许的字符串则根据$fatal取值返回false活着直接报503，exit
		if ( ! preg_match('/^[a-z0-9:_\/|-]+$/i', $str))
		{
			if ($fatal === TRUE)
			{
				return FALSE;
			}
			else
			{
				set_status_header(503);
				echo 'Disallowed Key Characters.';
				exit(7); // EXIT_USER_INPUT
			}
		}

		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE)
		{
			return $this->uni->clean_string($str);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Request Headers
	 *
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	array
	 */
	public function request_headers($xss_clean = FALSE)
	{
		// If header is already defined, return it immediately
		if ( ! empty($this->headers))
		{
			return $this->_fetch_from_array($this->headers, NULL, $xss_clean);
		}

		// In Apache, you can simply call apache_request_headers()
		if (function_exists('apache_request_headers'))
		{
			$this->headers = apache_request_headers();
		}
		else
		{
			isset($_SERVER['CONTENT_TYPE']) && $this->headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];

			foreach ($_SERVER as $key => $val)
			{
				if (sscanf($key, 'HTTP_%s', $header) === 1)
				{
					// take SOME_HEADER and turn it into Some-Header
					$header = str_replace('_', ' ', strtolower($header));
					$header = str_replace(' ', '-', ucwords($header));

					$this->headers[$header] = $_SERVER[$key];
				}
			}
		}

		return $this->_fetch_from_array($this->headers, NULL, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Request Header
	 *
	 * Returns the value of a single member of the headers class member
	 * 获取指定头信息
	 * @param	string		$index		Header name
	 * @param	bool		$xss_clean	Whether to apply XSS filtering
	 * @return	string|null	The requested header on success or NULL on failure
	 */
	public function get_request_header($index, $xss_clean = FALSE)
	{
		static $headers;

		// 如果未定义$headers则定义并赋值头信息
		if ( ! isset($headers))
		{
			empty($this->headers) && $this->request_headers();
			foreach ($this->headers as $key => $value)
			{
				$headers[strtolower($key)] = $value;
			}
		}

		$index = strtolower($index);

		// 没有则返回NULL
		if ( ! isset($headers[$index]))
		{
			return NULL;
		}

		// 如果存在则返回对应的值，当然根据需求进行xss滤波
		return ($xss_clean === TRUE)
			? $this->security->xss_clean($headers[$index])
			: $headers[$index];
	}

	// --------------------------------------------------------------------

	/**
	 * Is AJAX request?
	 *
	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
	 *
	 * @return 	bool
	 */
	public function is_ajax_request()
	{
		return ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
	}

	// --------------------------------------------------------------------

	/**
	 * Is CLI request?
	 *
	 * Test to see if a request was made from the command line.
	 *
	 * @deprecated	3.0.0	Use is_cli() instead
	 * @return	bool
	 */
	public function is_cli_request()
	{
		return is_cli();
	}

	// --------------------------------------------------------------------

	/**
	 * Get Request Method
	 *
	 * Return the request method
	 *
	 * @param	bool	$upper	Whether to return in upper or lower case
	 *				(default: FALSE)
	 * @return 	string
	 */
	public function method($upper = FALSE)
	{
		return ($upper)
			? strtoupper($this->server('REQUEST_METHOD'))
			: strtolower($this->server('REQUEST_METHOD'));
	}

	// ------------------------------------------------------------------------

	/**
	 * Magic __get()
	 *
	 * Allows read access to protected properties
	 * 最后定义了一个魔术方法，用来获取受保护属性
	 * @param	string	$name
	 * @return	mixed
	 */
	public function __get($name)
	{
		if ($name === 'raw_input_stream')
		{
			isset($this->_raw_input_stream) OR $this->_raw_input_stream = file_get_contents('php://input');
			return $this->_raw_input_stream;
		}
		elseif ($name === 'ip_address')
		{
			return $this->ip_address;
		}
	}

}
