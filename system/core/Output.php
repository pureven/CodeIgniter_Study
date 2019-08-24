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
 * Output Class
 *
 * Responsible for sending final output to the browser.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Output
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/libraries/output.html
 */
class CI_Output {

	/**
	 * Final output string
	 *
	 * @var	string
	 */
	public $final_output; // 保存最终的输出结果

	/**
	 * Cache expiration time
	 *
	 * @var	int
	 */
	public $cache_expiration = 0; // 缓存生效时间

	/**
	 * List of server headers
	 *
	 * @var	array
	 */
	public $headers = array(); // headers列表

	/**
	 * List of mime types
	 *
	 * @var	array
	 */
	public $mimes =	array(); // mimes类型列表

	/**
	 * Mime-type for the current page
	 *
	 * @var	string
	 */
	protected $mime_type = 'text/html'; // text/html类型

	/**
	 * Enable Profiler flag
	 *
	 * @var	bool
	 */
	public $enable_profiler = FALSE; // 保存用户profile标志位

	/**
	 * php.ini zlib.output_compression flag
	 *
	 * @var	bool
	 */
	protected $_zlib_oc = FALSE; // ini开启zlib标志位

	/**
	 * CI output compression flag
	 *
	 * @var	bool
	 */
	protected $_compress_output = FALSE; // 是否压缩输出标志位

	/**
	 * List of profiler sections
	 *
	 * @var	array
	 */
	protected $_profiler_sections =	array(); // 保存用户profile的数组

	/**
	 * Parse markers flag
	 *
	 * Whether or not to parse variables like {elapsed_time} and {memory_usage}.
	 *
	 * @var	bool
	 */
	public $parse_exec_vars = TRUE; // 启用或禁用解析伪变量

	/**
	 * mbstring.func_overload flag
	 *
	 * @var	bool
	 */
	protected static $func_overload; // 函数重载标志位

	/**
	 * Class constructor
	 *
	 * Determines whether zLib output compression will be used.
	 *
	 * @return	void
	 */
	public function __construct()
	{
	    // 首先从ini配置里拿zlib.output_compression的值，看是否开启压缩
		$this->_zlib_oc = (bool) ini_get('zlib.output_compression');
		/**
         * 当php环境没有开启gzip压缩，且配置文件设置compress_output为Ture，安装了zlib扩展
         * 将属性_compress_output设置为True，通常情况下是启用服务器压缩而关闭程序本身的压缩功能
        **/
		$this->_compress_output = (
			$this->_zlib_oc === FALSE
			&& config_item('compress_output') === TRUE
			&& extension_loaded('zlib')
		);

        /**
         * 在 ini 中设置 mbstring.func_overload 将允许用 mbstring 扩展中的相似功能替换掉字符串函数的子集。
         * 例如，strlen() 将不再返回以字节为单位的字符串长度，而是根据当前选择的内部编码返回代码点中的长度。
         * 这意味着使用 mbstring.func_overload 的代码，与几乎所有在基本字符串操作正常工作的假设下编写的代码都不兼容。
         * 一些库完全禁止 func_overload （例如 Symfony），其它的则会出现一些不可察觉的行为变化。
         * 希望支持 func_overload 的代码需要有在普通字符串函数和具有8位编码的 mbstring 函数之间切换的条件（但是，通常只有加密库才会这么做）。
         */
		isset(self::$func_overload) OR self::$func_overload = (extension_loaded('mbstring') && ini_get('mbstring.func_overload'));

		// Get mime types for later
		$this->mimes =& get_mimes();

		log_message('info', 'Output Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Get Output
	 *
	 * Returns the current output string.
	 * 获取并返回$final_output
	 * @return	string
	 */
	public function get_output()
	{
		return $this->final_output;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Output
	 *
	 * Sets the output string.
	 * 设置$final_output返回类
	 * @param	string	$output	Output data
	 * @return	CI_Output
	 */
	public function set_output($output)
	{
		$this->final_output = $output;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Append Output
	 *
	 * Appends data onto the output string.
	 * 以append的方式修改$final_output，返回类
	 * @param	string	$output	Data to append
	 * @return	CI_Output
	 */
	public function append_output($output)
	{
		$this->final_output .= $output;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Header
	 *
	 * Lets you set a server header which will be sent with the final output.
     *
	 * 允许你手工设置服务器的 HTTP 头，输出类将在最终显示页面时发送它
     * $this->output->set_header('HTTP/1.0 200 OK');
     * $this->output->set_header('HTTP/1.1 200 OK');
     * $this->output->set_header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_update).' GMT');
     * $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
     * $this->output->set_header('Cache-Control: post-check=0, pre-check=0');
     *
     * $this->output->set_header('Pragma: no-cache');
	 * Note: If a file is cached, headers will not be sent.
	 * @todo	We need to figure out how to permit headers to be cached.
	 *
	 * @param	string	$header		Header
	 * @param	bool	$replace	Whether to replace the old header value, if already set
	 * @return	CI_Output
	 */
	public function set_header($header, $replace = TRUE)
	{
		// If zlib.output_compression is enabled it will compress the output,
		// but it will not modify the content-length header to compensate for
		// the reduction, causing the browser to hang waiting for more data.
		// We'll just skip content-length in those cases.
		if ($this->_zlib_oc && strncasecmp($header, 'content-length', 14) === 0)
		{
			return $this;
		}

		$this->headers[] = array($header, $replace);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Content-Type Header
	 *
	 * @param	string	$mime_type	Extension of the file we're outputting
	 * @param	string	$charset	Character set (default: NULL)
	 * @return	CI_Output
	 */
	// 设置页面的 MIME 类型，可以很方便的提供 JSON 数据、JPEG、XML 等等格式,通过$charset设置文档的字符集
	public function set_content_type($mime_type, $charset = NULL)
	{
		if (strpos($mime_type, '/') === FALSE)
		{
			$extension = ltrim($mime_type, '.');

			// Is this extension supported?
			if (isset($this->mimes[$extension]))
			{
				$mime_type =& $this->mimes[$extension];

				if (is_array($mime_type))
				{
					$mime_type = current($mime_type);
				}
			}
		}

		$this->mime_type = $mime_type;

		if (empty($charset))
		{
			$charset = config_item('charset');
		}

		$header = 'Content-Type: '.$mime_type
			.(empty($charset) ? '' : '; charset='.$charset);

		$this->headers[] = array($header, TRUE);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Current Content-Type Header
	 * 获取当前正在使用的 HTTP 头 Content-Type ，不包含字符集部分。
	 * @return	string	'text/html', if not already set
	 */
	public function get_content_type()
	{
		for ($i = 0, $c = count($this->headers); $i < $c; $i++)
		{
		    // sscanf($str, $format) 根据指定格式解析输入的字符
			if (sscanf($this->headers[$i][0], 'Content-Type: %[^;]', $content_type) === 1)
			{
				return $content_type;
			}
		}

		return 'text/html';
	}

	// --------------------------------------------------------------------

	/**
	 * Get Header
	 *
	 * @param	string	$header
	 * @return	string
	 */
	public function get_header($header)
	{
		// Combine headers already sent with our batched headers
		$headers = array_merge(
			// We only need [x][0] from our multi-dimensional array
			array_map('array_shift', $this->headers),
			headers_list()
		);

		if (empty($headers) OR empty($header))
		{
			return NULL;
		}

		// Count backwards, in order to get the last matching header
		for ($c = count($headers) - 1; $c > -1; $c--)
		{
			if (strncasecmp($header, $headers[$c], $l = self::strlen($header)) === 0)
			{
				return trim(self::substr($headers[$c], $l+1));
			}
		}

		return NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Set HTTP Status Header
	 *
	 * As of version 1.7.2, this is an alias for common function
	 * set_status_header().
	 *
	 * @param	int	$code	Status code (default: 200)
	 * @param	string	$text	Optional message
	 * @return	CI_Output
	 */
	public function set_status_header($code = 200, $text = '')
	{
		set_status_header($code, $text); // 走Common.php中的方法
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Enable/disable Profiler
	 *
	 * @param	bool	$val	TRUE to enable or FALSE to disable
	 * @return	CI_Output
	 */
	public function enable_profiler($val = TRUE)
	{
		$this->enable_profiler = is_bool($val) ? $val : TRUE;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Profiler Sections
	 *
	 * Allows override of default/config settings for
	 * Profiler section display.
	 *
	 * @param	array	$sections	Profiler sections
	 * @return	CI_Output
	 */
	public function set_profiler_sections($sections)
	{
		if (isset($sections['query_toggle_count']))
		{
			$this->_profiler_sections['query_toggle_count'] = (int) $sections['query_toggle_count'];
			unset($sections['query_toggle_count']);
		}

		foreach ($sections as $section => $enable)
		{
			$this->_profiler_sections[$section] = ($enable !== FALSE);
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Cache
	 *
	 * @param	int	$time	Cache expiration time in minutes
	 * @return	CI_Output
	 */
	public function cache($time)
	{
		$this->cache_expiration = is_numeric($time) ? $time : 0;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Output
	 *
	 * Processes and sends finalized output data to the browser along
	 * with any server headers and profile data. It also stops benchmark
	 * timers so the page rendering speed and memory usage can be shown.
	 *
	 * Note: All "view" data is automatically put into $this->final_output
	 *	 by controller class.
	 *
	 * @uses	CI_Output::$final_output
	 * @param	string	$output	Output data override
	 * @return	void
	 */
	public function _display($output = '')
	{
		// Note:  We use load_class() because we can't use $CI =& get_instance()
		// since this function is sometimes called by the caching mechanism,
		// which happens before the CI super object is available.
        /**
         *  我们实用load_class()而不直接用$CI =& get_instance()
         *  因为有时候本方法是被缓存机制调用的，这时候$CI超级对象还无法使用
         */
		$BM =& load_class('Benchmark', 'core');
		$CFG =& load_class('Config', 'core');

		// Grab the super object if we can. 如果可能的话，获取超级对象$CI
		if (class_exists('CI_Controller', FALSE))
		{
			$CI =& get_instance();
		}

		// --------------------------------------------------------------------

		// Set the output data
		if ($output === '')
		{
			$output =& $this->final_output;
		}

		// --------------------------------------------------------------------

		// Do we need to write a cache file? Only if the controller does not have its
		// own _output() method and we are not dealing with a cache file, which we
		// can determine by the existence of the $CI object above
        // 当$CI对象存在时证明我们不是在从缓存输出数据，这时如果Controller没有自定义_output方法就需要写缓存
		if ($this->cache_expiration > 0 && isset($CI) && ! method_exists($CI, '_output'))
		{
			$this->_write_cache($output);
		}

		// --------------------------------------------------------------------

		// Parse out the elapsed time and memory usage,
		// then swap the pseudo-variables with the data

        // $elapsed 为total_execution_time_start，total_execution_time_end两点之间所用的时间
		$elapsed = $BM->elapsed_time('total_execution_time_start', 'total_execution_time_end');

		if ($this->parse_exec_vars === TRUE)
		{
			$memory	= round(memory_get_usage() / 1024 / 1024, 2).'MB';
			// {elapsed_time} {memory_usage} 通常在HTML文件中使用，例如welcome_message.html
			$output = str_replace(array('{elapsed_time}', '{memory_usage}'), array($elapsed, $memory), $output);
		}

		// --------------------------------------------------------------------

		// Is compression requested?
		if (isset($CI) // This means that we're not serving a cache file, if we were, it would already be compressed
			&& $this->_compress_output === TRUE
			&& isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
		{
		    // 将打开输出缓冲。当输出缓冲激活后，脚本将不会输出内容（除http标头外），相反需要输出的内容被存储在内部缓冲区中
            // ob_gzhandler()目的是用在ob_start()中作回调函数，以方便将gz 编码的数据发送到支持压缩页面的浏览器。
            // 在ob_gzhandler()真正发送压缩过的数据之前，该 函数会确定（判定）浏览器可以接受哪种类型内容编码（"gzip","deflate",或者根本什么都不支持），
            // 然后 返回相应的输出。 所有可以发送正确头信息表明他自己可以接受压缩的网页的浏览器，都可以支持。
			ob_start('ob_gzhandler');
		}

		// --------------------------------------------------------------------

		// Are there any server headers to send?
		if (count($this->headers) > 0)
		{
			foreach ($this->headers as $header)
			{
				@header($header[0], $header[1]);
			}
		}

		// --------------------------------------------------------------------

		// Does the $CI object exist?
		// If not we know we are dealing with a cache file so we'll
		// simply echo out the data and exit.
		if ( ! isset($CI))
		{
			if ($this->_compress_output === TRUE)
			{
			    // $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate, br';
                // 请求头信息： Accept-Encoding: gzip, deflate, br
				if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
				{
					header('Content-Encoding: gzip');
					header('Content-Length: '.self::strlen($output));
				}
				else
				{
					// User agent doesn't support gzip compression,
					// so we'll have to decompress our cache
                    // 通常用于未定义gzdecode()函数的情况，使用gzinflate代替
					$output = gzinflate(self::substr($output, 10, -8));
				}
			}

			echo $output;
			log_message('info', 'Final output sent to browser');
			log_message('debug', 'Total execution time: '.$elapsed);
			return;
		}

		// --------------------------------------------------------------------

		// Do we need to generate profile data?
		// If so, load the Profile class and run it.
        // Bechmarking只能显示两个基准点之间所消耗的时间信息，
        // 这里使用Profile显示所有基准点的时间消耗，同时还会显示出提交的数据和数据库查询的信息
		if ($this->enable_profiler === TRUE)
		{
			$CI->load->library('profiler');
			if ( ! empty($this->_profiler_sections))
			{
				$CI->profiler->set_sections($this->_profiler_sections);
			}

			// If the output data contains closing </body> and </html> tags
			// we will remove them and add them back after we insert the profile data
			$output = preg_replace('|</body>.*?</html>|is', '', $output, -1, $count).$CI->profiler->run();
			if ($count > 0)
			{
				$output .= '</body></html>';
			}
		}

		// Does the controller contain a function named _output()?
		// If so send the output there.  Otherwise, echo it.
		if (method_exists($CI, '_output'))
		{
			$CI->_output($output);
		}
		else
		{
			echo $output; // Send it to the browser!
		}

		log_message('info', 'Final output sent to browser');
		log_message('debug', 'Total execution time: '.$elapsed);
	}

	// --------------------------------------------------------------------

	/**
	 * Write Cache
	 *
	 * @param	string	$output	Output data to cache 写入缓存文件
	 * @return	void
	 */
	public function _write_cache($output)
	{
		$CI =& get_instance();
		$path = $CI->config->item('cache_path');
		$cache_path = ($path === '') ? APPPATH.'cache/' : $path;
		// $cache_path: 'G:\wamp\www\CodeIgniter_hmvc\application\cache/'
		if ( ! is_dir($cache_path) OR ! is_really_writable($cache_path))
		{
			log_message('error', 'Unable to write cache file: '.$cache_path);
			return;
		}

		$uri = $CI->config->item('base_url')
			.$CI->config->item('index_page')
			.$CI->uri->uri_string();
		// $uri: 'http://[::1]/CodeIgniter_hmvc/index.phpwelcome'

		if (($cache_query_string = $CI->config->item('cache_query_string')) && ! empty($_SERVER['QUERY_STRING']))
		{
			if (is_array($cache_query_string))
			{
			    // http_build_query():  生成 URL-encode 之后的请求字符串
                // array_intersect_key(): 使用键名比较计算数组的交集
                // array_flip(): 交换数组中的键和值
                // $cache_query_string从config.php里配置，可配置成数组，待做实验！
				$uri .= '?'.http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
			}
			else
			{
				$uri .= '?'.$_SERVER['QUERY_STRING'];
			}
		}
		// 这时$uri为加上query_stirng后的url：'http://[::1]/CodeIgniter_hmvc/index.phpwelcome?welcome'
        // 然后对$uri进行md5编码，也就是说不同的参数对应不同的缓存文件
		$cache_path .= md5($uri);

		if ( ! $fp = @fopen($cache_path, 'w+b'))
		{
			log_message('error', 'Unable to write cache file: '.$cache_path);
			return;
		}

		// flock():轻便的咨询文件锁定
        /**
         *  LOCK_SH: 取得共享锁定（读取的程序）
         *  LOCK_EX: 取得独占锁定（写入的程序）
         *  LOCK_UN: 释放锁定
         */
		if ( ! flock($fp, LOCK_EX))
		{
			log_message('error', 'Unable to secure a file lock for file at: '.$cache_path);
			fclose($fp);
			return;
		}

		// If output compression is enabled, compress the cache
		// itself, so that we don't have to do that each time
		// we're serving it
		if ($this->_compress_output === TRUE)
		{
		    // 将$output进行gzip压缩
			$output = gzencode($output);

			if ($this->get_header('content-type') === NULL)
			{
				$this->set_content_type($this->mime_type);
			}
		}

		$expire = time() + ($this->cache_expiration * 60);

		// Put together our serialized info.
		$cache_info = serialize(array(
			'expire'	=> $expire,
			'headers'	=> $this->headers
		));

		// 内容前加上$cache_info . 'ENDCI--->'
		$output = $cache_info.'ENDCI--->'.$output;

		for ($written = 0, $length = self::strlen($output); $written < $length; $written += $result)
		{
		    // 自定义substr():这里考虑到可能mbstring重载，需告知字节数
			if (($result = fwrite($fp, self::substr($output, $written))) === FALSE)
			{
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		if ( ! is_int($result))
		{
			@unlink($cache_path);
			log_message('error', 'Unable to write the complete cache content at: '.$cache_path);
			return;
		}

		// 设置权限
		chmod($cache_path, 0640);
		log_message('debug', 'Cache file written: '.$cache_path);

		// Send HTTP cache-control headers to browser to match file cache settings.
        // 发送HTTP缓存控制头到浏览器以匹配文件缓存设置.
		$this->set_cache_header($_SERVER['REQUEST_TIME'], $expire);
	}

	// --------------------------------------------------------------------

	/**
	 * Update/serve cached output
	 *
	 * @uses	CI_Config
	 * @uses	CI_URI
	 *
	 * @param	object	&$CFG	CI_Config class instance
	 * @param	object	&$URI	CI_URI class instance
	 * @return	bool	TRUE on success or FALSE on failure
	 */
    /**
     * 在CodeIgniter.php里面有调用此方法，此方法是负责缓存的输出
     * 如果在CodeIgniter.php中调用此方法有输出，则本次请求的运行将直接结束，直接以缓存输出作为响应。
     *
     */
	public function _display_cache(&$CFG, &$URI)
	{
        //取得保存缓存的路径
		$cache_path = ($CFG->item('cache_path') === '') ? APPPATH.'cache/' : $CFG->item('cache_path');

		// Build the file path. The file name is an MD5 hash of the full URI
        // 一条准确的路由都会对应一个缓存文件，缓存文件是对应路由字符串的md5密文。
        // 这个$uri貌似有问题
		$uri = $CFG->item('base_url').$CFG->item('index_page').$URI->uri_string;

        /**
         * $_SERVER["REQUEST_URI"] => "/api/ctroller/function?param1=&param2=&param3=hello&param4=&page=1&limit=10"，访问的页面URI，包含查询字符串
         * $_SERVER['QUERY_STRING'] 查询字符串，如果有的话通过它来查询
         * $_SERVER["QUERY_STRING"] => "param1=&param2=&param3=hello&param4=&page=1&limit=10"，查询字符串，不存在为" "
         */
		if (($cache_query_string = $CFG->item('cache_query_string')) && ! empty($_SERVER['QUERY_STRING']))
		{
			if (is_array($cache_query_string))
			{
			    // array_flip() 交换数组中的键和值
                // array_intersect_key() 使用键名比较计算数组的交集
				$uri .= '?'.http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
			}
			else
			{
				$uri .= '?'.$_SERVER['QUERY_STRING'];
			}
		}
		// $filepath = 'G:\wamp\www\CodeIgniter_hmvc\application\cache/449a65bd3d6bad1ee34104f01d27cc26';
		$filepath = $cache_path.md5($uri);

		if ( ! file_exists($filepath) OR ! $fp = @fopen($filepath, 'rb'))
		{
			return FALSE;
		}

		// flock() 轻便的咨询文件锁定
        // LOCK_SH: 取得共享锁定（读取的程序）
        // LOCK_EX: 取得独占锁定（写入的程序）
        // LOCK_UN: 释放锁定   （无论共享或独占）
		flock($fp, LOCK_SH);

		$cache = (filesize($filepath) > 0) ? fread($fp, filesize($filepath)) : '';

		flock($fp, LOCK_UN);
		fclose($fp);

		// Look for embedded serialized file info.
        // 这个地方可参考_write_cache()方法中构造缓存的部分：$output = $cache_info.'ENDCI--->'.$output;
        // 下面这个ENDCI--->字样，只是因为CI的缓存文件里面的内容是规定以cache_info['expire', 'headers']＋ENDCI--->开头而已。
        // 如果不符合此结构，可视为非CI的缓存文件，或者文件已损坏，获取缓存内容失败，返回FALSE。
        // $match[0]是除页面内容之外的附加信息:   'a:2:{s:6:"expire";i:1566534312;s:7:"headers";a:0:{}}ENDCI--->'
        // $match[1]是附加信息中和时间有关的信息: 'a:2:{s:6:"expire";i:1566534312;s:7:"headers";a:0:{}}'
        // 缓存文件开头: a:2:{s:6:"expire";i:1566534312;s:7:"headers";a:0:{}}ENDCI---><!DOCTYPE html>
		if ( ! preg_match('/^(.*)ENDCI--->/', $cache, $match))
		{
			return FALSE;
		}

		$cache_info = unserialize($match[1]);
		$expire = $cache_info['expire']; // 1566535047

        // filemtime(): 取得文件修改时间
		$last_modified = filemtime($filepath); // 1566534987

		// Has the file expired? 过期删除
		if ($_SERVER['REQUEST_TIME'] >= $expire && is_really_writable($cache_path))
		{
			// If so we'll delete it.
			@unlink($filepath);
			log_message('debug', 'Cache file has expired. File deleted.');
			return FALSE;
		}

		// Send the HTTP cache control headers
        // 304 200
		$this->set_cache_header($last_modified, $expire);

		// Add headers from cache file.
        // 如果有的话就加到header里
		foreach ($cache_info['headers'] as $header)
		{
			$this->set_header($header[0], $header[1]);
		}

		// Display the cache
		$this->_display(self::substr($cache, self::strlen($match[0])));
		log_message('debug', 'Cache file is current. Sending it to browser.');
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete cache
	 *
	 * @param	string	$uri	URI string
	 * @return	bool
	 */
	public function delete_cache($uri = '')
	{
		$CI =& get_instance();
		$cache_path = $CI->config->item('cache_path');
		if ($cache_path === '')
		{
			$cache_path = APPPATH.'cache/';
		}

		if ( ! is_dir($cache_path))
		{
			log_message('error', 'Unable to find cache path: '.$cache_path);
			return FALSE;
		}

		if (empty($uri))
		{
			$uri = $CI->uri->uri_string();

			if (($cache_query_string = $CI->config->item('cache_query_string')) && ! empty($_SERVER['QUERY_STRING']))
			{
				if (is_array($cache_query_string))
				{
					$uri .= '?'.http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
				}
				else
				{
					$uri .= '?'.$_SERVER['QUERY_STRING'];
				}
			}
		}

		$cache_path .= md5($CI->config->item('base_url').$CI->config->item('index_page').ltrim($uri, '/'));

		if ( ! @unlink($cache_path))
		{
			log_message('error', 'Unable to delete cache file for '.$uri);
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Cache Header
	 *
	 * Set the HTTP headers to match the server-side file cache settings
	 * in order to reduce bandwidth.
	 *
	 * @param	int	$last_modified	Timestamp of when the page was last modified
	 * @param	int	$expiration	Timestamp of when should the requested page expire from cache
	 * @return	void
	 */
	public function set_cache_header($last_modified, $expiration)
	{
		$max_age = $expiration - $_SERVER['REQUEST_TIME'];

		// 如果文件最后修改时间在在所请求的资源在给定的日期时间之后则返回200，否则返回304
        // 参考：https://developer.mozilla.org/zh-CN/docs/Web/HTTP/Headers/If-Modified-Since
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $last_modified <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
		    // 未过期则走缓存
			$this->set_status_header(304);
			exit;
		}

		header('Pragma: public');
		header('Cache-Control: max-age='.$max_age.', public');
		header('Expires: '.gmdate('D, d M Y H:i:s', $expiration).' GMT');
		header('Last-modified: '.gmdate('D, d M Y H:i:s', $last_modified).' GMT');
	}

	// --------------------------------------------------------------------

	/**
	 * Byte-safe strlen()
	 *
	 * @param	string	$str
	 * @return	int
	 */
	protected static function strlen($str)
	{
		return (self::$func_overload)
			? mb_strlen($str, '8bit')
			: strlen($str);
	}

	// --------------------------------------------------------------------

	/**
	 * Byte-safe substr()
	 *
	 * @param	string	$str
	 * @param	int	$start
	 * @param	int	$length
	 * @return	string
	 */
	protected static function substr($str, $start, $length = NULL)
	{
		if (self::$func_overload)
		{
			// mb_substr($str, $start, null, '8bit') returns an empty
			// string on PHP 5.3
			isset($length) OR $length = ($start >= 0 ? self::strlen($str) - $start : -$start);
			return mb_substr($str, $start, $length, '8bit');
		}

		return isset($length)
			? substr($str, $start, $length)
			: substr($str, $start);
	}
}
