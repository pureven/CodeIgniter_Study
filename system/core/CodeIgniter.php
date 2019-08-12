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

/* 保证单一入口进入程序、防止跨站攻击直接访问文件路径等恶意攻击*/
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * System Initialization File
 *
 * Loads the base classes and executes the request.
 *
 * @package		CodeIgniter
 * @subpackage	CodeIgniter
 * @category	Front-controller
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/
 */

/**
 * CodeIgniter Version
 *
 * @var	string
 *
 */
/* 常量 CodeIgniter版本 */
	const CI_VERSION = '3.1.10';

/*
 * ------------------------------------------------------
 *  Load the framework constants
 * ------------------------------------------------------
 */
/*
 * echo APPPATH.'config/'.ENVIRONMENT.'/constants.php';
 * G:\wamp\www\CodeIgniter_hmvc\application\config/development/constants.php
*/
	if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/constants.php'))
	{
		require_once(APPPATH.'config/'.ENVIRONMENT.'/constants.php');
	}

	/* CodeIgniter 自定义的常量：主要包括错误追踪开关、文件目录读写模式、退出常量等*/
	if (file_exists(APPPATH.'config/constants.php'))
	{
		require_once(APPPATH.'config/constants.php');
	}

/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */

/*
 * echo BASEPATH.'core/Common.php';exit();
 * G:\wamp\www\CodeIgniter_hmvc\system\core/Common.php
 * 全局函数，主要包括:
 * is_php($version):
 *      将入参(PHP版本)写入静态数组$is_php中
 *
 * is_really_writable($file):
 *      判断文件是否可写,unix通过is_writable判断，windows通过追加写入返回值来判断
 *
 * load_class($class, $directory = 'libraries', $param = NULL):
 *      首先定义一个静态数组$_classes,当调用load_class()时，
 *      会首先判断是否在$_classes中，如果有直接返回，防止重复加载，实现单例话。如果没有则先判断application、
 *      system中是否有该类，有则加载(先require_once,然后放入$_classes数组中)，这里优先加载application目录里面的
 *      如果应用目录下存在MY_$class的类则加载，这是自定义的类，一般继承system对应的类了
 *
 * is_loaded($class = ''):
 *      记录已经加载的类
 *
 * get_config($replace = []):
 *      首先定义一个静态数组$config,require APPPATH.'config/config.php'、APPPATH.'config/'.ENVIRONMENT.'/config.php'文件，
 *      如果replace不为空则执行$config[$key] = $val;通过key value的形式动态更新$config数组中的值，其中
 *      APPPATH.'config/config.php'：G:\wamp\www\CodeIgniter_hmvc\system\core\Common.php:245:string 'G:\wamp\www\CodeIgniter_hmvc\application\config/config.php' (length=58)
 *      APPPATH.'config/'.ENVIRONMENT.'/config.php'：G:\wamp\www\CodeIgniter_hmvc\system\core\Common.php:251:string 'G:\wamp\www\CodeIgniter_hmvc\application\config/development/config.php' (length=70)
 *
 * config_item($item):
 *      从$config中获取$item
 *
 * get_mimes():
 *      首先定义一个静态数组$_mimes,include(APPPATH.'config/mimes.php')
 *      将mime文件中的数组赋给$_mime
 *
 * is_https:
 *      $_SERVER: HTTPS 如果脚本是通过 HTTPS 协议被访问，则被设为一个非空的值。
 *      $_SERVER: HTTP_X_FORWARDED_PROTO
 *      $_SERVER: HTTP_FRONT_END_HTTPS
 *      strtolower($_SERVER['HTTPS']) !== 'off' 或者 strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https' 或者 strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off'则说明是https请求
 *
 * is_cli:
 *      return (PHP_SAPI === 'cli' OR defined('STDIN'));
 *
 * show_error($message, $status_code = 500, $heading = 'An Error Was Encountered'):
 * show_error($page = '', $log_error = TRUE):
 * _error_handler($severity, $message, $filepath, $line):
 * _exception_handler($exception):
 * _shutdown_handler():
 *      加载Exceptions类，使用类中show_error()方法显示错误
 *
 * log_message($level, $message):
 *      用于向日志中写入错误信息
 *
 * set_status_header($code = 200, $text = ''):
 *      用于手动设置服务器的 HTTP 状态码,这里使用的header();
 *
 * remove_invisible_characters($str, $url_encoded = TRUE):
 *      用于删除不可见字符，防止在 ASCII 字符串中插入空字符，由preg_replace实现
 *
 * html_escape($var, $double_encode = TRUE):
 *      将特殊字符转换为 HTML 实体,用于防止XSS攻击，由htmlspecialchars实现
 *
 * _stringify_attributes($attributes, $js = FALSE):
 *      将$attributes字符串化，支持字符串、数组
 *
 * function_usable($function_name)：
 *      检查一个函数是否可用，可用返回 TRUE ，否则返回 FALSE 。
 *      该函数直接调用 function_exists() 函数，并检查当前是否加载了 Suhosin 扩展 <http://www.hardened-php.net/suhosin/> ，
 *      如果加载了 Suhosin ，检查函数有没有被它禁用。
 * */
	require_once(BASEPATH.'core/Common.php');


/*
 * ------------------------------------------------------
 * Security procedures
 * ------------------------------------------------------
 */

/* 版本小于5.4.0则做兼容处理 */
if ( ! is_php('5.4'))
{
    /* 关闭当前 magic_quotes_runtime 配置选项的激活状态,7.0版本PHP中废弃该选项*/
	ini_set('magic_quotes_runtime', 0);

	/* register_globals打开后，各种变量会被注入代码，非常不安全，PHP5.4.0将其移出 */
	if ((bool) ini_get('register_globals'))
	{
		$_protected = array(
			'_SERVER',
			'_GET',
			'_POST',
			'_FILES',
			'_REQUEST',
			'_SESSION',
			'_ENV',
			'_COOKIE',
			'GLOBALS',
			'HTTP_RAW_POST_DATA',
			'system_path',
			'application_folder',
			'view_folder',
			'_protected',
			'_registered'
		);

		$_registered = ini_get('variables_order');
		foreach (array('E' => '_ENV', 'G' => '_GET', 'P' => '_POST', 'C' => '_COOKIE', 'S' => '_SERVER') as $key => $superglobal)
		{
			if (strpos($_registered, $key) === FALSE)
			{
				continue;
			}

			foreach (array_keys($$superglobal) as $var)
			{
				if (isset($GLOBALS[$var]) && ! in_array($var, $_protected, TRUE))
				{
					$GLOBALS[$var] = NULL;
				}
			}
		}
	}
}


/*
 * ------------------------------------------------------
 *  Define a custom error handler so we can log PHP errors
 * ------------------------------------------------------
 */
/* 设置用户自定义的错误、异常、处理函数以及注册一个会在php中止时执行的函数 */
	set_error_handler('_error_handler');
	set_exception_handler('_exception_handler');
	register_shutdown_function('_shutdown_handler');

/*
 * ------------------------------------------------------
 *  Set the subclass_prefix
 * ------------------------------------------------------
 *
 * Normally the "subclass_prefix" is set in the config file.
 * The subclass prefix allows CI to know if a core class is
 * being extended via a library in the local application
 * "libraries" folder. Since CI allows config items to be
 * overridden via data set in the main index.php file,
 * before proceeding we need to know if a subclass_prefix
 * override exists. If so, we will set this value now,
 * before any classes are loaded
 * Note: Since the config file data is cached it doesn't
 * hurt to load it here.
 */
/* 设置子类前缀，这个地方打印$assign_to_config是报错notice，未定义*/
	if ( ! empty($assign_to_config['subclass_prefix']))
	{
		get_config(array('subclass_prefix' => $assign_to_config['subclass_prefix']));
	}

/*
 * ------------------------------------------------------
 *  Should we use a Composer autoloader?
 * ------------------------------------------------------
 */
/* 如有需要自动加载组件，则加载APPPATH/vendor/autoload.php，或者配置文件里设置的文件路径 */
	if ($composer_autoload = config_item('composer_autoload'))
	{
		if ($composer_autoload === TRUE)
		{
			file_exists(APPPATH.'vendor/autoload.php')
				? require_once(APPPATH.'vendor/autoload.php')
				: log_message('error', '$config[\'composer_autoload\'] is set to TRUE but '.APPPATH.'vendor/autoload.php was not found.');
		}
		elseif (file_exists($composer_autoload))
		{
			require_once($composer_autoload);
		}
		else
		{
			log_message('error', 'Could not find the specified $config[\'composer_autoload\'] path: '.$composer_autoload);
		}
	}

/*
 * ------------------------------------------------------
 *  Start the timer... tick tock tick tock...
 * ------------------------------------------------------
 */
/*
 * 加载benchmark，并记录total_execution_time_start,_base_classes_start两个时间点
 * 在性能分析器中使用基准测试点
 * 显示总执行时间
 * 显示内存占用
 * mark()用来标记起始点结束点
 * elapsed_time()用来计算时间差
 * */
	$BM =& load_class('Benchmark', 'core');
	$BM->mark('total_execution_time_start');
	$BM->mark('loading_time:_base_classes_start');

/*
 * ------------------------------------------------------
 *  Instantiate the hooks class
 * ------------------------------------------------------
 */
/**
 * 加载Hooks类
 */
	$EXT =& load_class('Hooks', 'core');

/*
 * ------------------------------------------------------
 *  Is there a "pre_system" hook?
 * ------------------------------------------------------
 */
/* call_hook()调用_run_hook()实现 */
	$EXT->call_hook('pre_system');

/*
 * ------------------------------------------------------
 *  Instantiate the config class
 * ------------------------------------------------------
 *
 * Note: It is important that Config is loaded first as
 * most other classes depend on it either directly or by
 * depending on another class that uses it.
 *
 */
/**
 * 加载config类并设置base_url
 */
	$CFG =& load_class('Config', 'core');

	// Do we have any manually set config items in the index.php file?
    /* 如果有在index.php定义配置数组，那么就丢给配置组件CFG，以后就由CFG来保管了配置信息了。 */
	if (isset($assign_to_config) && is_array($assign_to_config))
	{
		foreach ($assign_to_config as $key => $value)
		{
			$CFG->set_item($key, $value);
		}
	}

/*
 * ------------------------------------------------------
 * Important charset-related stuff
 * ------------------------------------------------------
 *
 * Configure mbstring and/or iconv if they are enabled
 * and set MB_ENABLED and ICONV_ENABLED constants, so
 * that we don't repeatedly do extension_loaded() or
 * function_exists() calls.
 *
 * Note: UTF-8 class depends on this. It used to be done
 * in it's constructor, but it's _not_ class-specific.
 *
 */
    /* 设置默认字符编码, 默认utf-8 */
	$charset = strtoupper(config_item('charset'));
	ini_set('default_charset', $charset);

	/* extension_load 检查扩展是否加载 */
	if (extension_loaded('mbstring'))
	{
		define('MB_ENABLED', TRUE);
		// mbstring.internal_encoding is deprecated starting with PHP 5.6
		// and it's usage triggers E_DEPRECATED messages.
		@ini_set('mbstring.internal_encoding', $charset);
		// This is required for mb_convert_encoding() to strip invalid characters.
		// That's utilized by CI_Utf8, but it's also done for consistency with iconv.
		mb_substitute_character('none');
	}
	else
	{
		define('MB_ENABLED', FALSE);
	}

	// There's an ICONV_IMPL constant, but the PHP manual says that using
	// iconv's predefined constants is "strongly discouraged".
	if (extension_loaded('iconv'))
	{
		define('ICONV_ENABLED', TRUE);
		// iconv.internal_encoding is deprecated starting with PHP 5.6
		// and it's usage triggers E_DEPRECATED messages.
		@ini_set('iconv.internal_encoding', $charset);
	}
	else
	{
		define('ICONV_ENABLED', FALSE);
	}

	if (is_php('5.6'))
	{
		ini_set('php.internal_encoding', $charset);
	}

/*
 * ------------------------------------------------------
 *  Load compatibility features
 * ------------------------------------------------------
 */

	require_once(BASEPATH.'core/compat/mbstring.php');
	require_once(BASEPATH.'core/compat/hash.php');
	require_once(BASEPATH.'core/compat/password.php');
	require_once(BASEPATH.'core/compat/standard.php');

/*
 * ------------------------------------------------------
 *  Instantiate the UTF-8 class
 * ------------------------------------------------------
 */
    /* 加载Utf8并设置常量UTF8_ENABLED */
	$UNI =& load_class('Utf8', 'core');

/*
 * ------------------------------------------------------
 *  Instantiate the URI class
 * ------------------------------------------------------
 */
    /* 加载URI */
	$URI =& load_class('URI', 'core');

/*
 * ------------------------------------------------------
 *  Instantiate the routing class and set the routing
 * ------------------------------------------------------
 */
	$RTR =& load_class('Router', 'core', isset($routing) ? $routing : NULL);

/*
 * ------------------------------------------------------
 *  Instantiate the output class
 * ------------------------------------------------------
 */
	$OUT =& load_class('Output', 'core');

/*
 * ------------------------------------------------------
 *	Is there a valid cache file? If so, we're done...
 * ------------------------------------------------------
 */
	if ($EXT->call_hook('cache_override') === FALSE && $OUT->_display_cache($CFG, $URI) === TRUE)
	{
		exit;
	}

/*
 * -----------------------------------------------------
 * Load the security class for xss and csrf support
 * -----------------------------------------------------
 */
	$SEC =& load_class('Security', 'core');

/*
 * ------------------------------------------------------
 *  Load the Input class and sanitize globals
 * ------------------------------------------------------
 */
	$IN	=& load_class('Input', 'core');

/*
 * ------------------------------------------------------
 *  Load the Language class
 * ------------------------------------------------------
 */
	$LANG =& load_class('Lang', 'core');

/*
 * ------------------------------------------------------
 *  Load the app controller and local controller
 * ------------------------------------------------------
 *
 */
	// Load the base controller class
	require_once BASEPATH.'core/Controller.php';

	/**
	 * Reference to the CI_Controller method.
	 *
	 * Returns current CI instance object
	 *
	 * @return CI_Controller
	 */
	function &get_instance()
	{
		return CI_Controller::get_instance();
	}

	if (file_exists(APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php'))
	{
		require_once APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php';
	}

	// Set a mark point for benchmarking
	$BM->mark('loading_time:_base_classes_end');

/*
 * ------------------------------------------------------
 *  Sanity checks
 * ------------------------------------------------------
 *
 *  The Router class has already validated the request,
 *  leaving us with 3 options here:
 *
 *	1) an empty class name, if we reached the default
 *	   controller, but it didn't exist;
 *	2) a query string which doesn't go through a
 *	   file_exists() check
 *	3) a regular request for a non-existing page
 *
 *  We handle all of these as a 404 error.
 *
 *  Furthermore, none of the methods in the app controller
 *  or the loader class can be called via the URI, nor can
 *  controller methods that begin with an underscore.
 */

	$e404 = FALSE;
	$class = ucfirst($RTR->class);
	$method = $RTR->method;

	if (empty($class) OR ! file_exists(APPPATH.'controllers/'.$RTR->directory.$class.'.php'))
	{
		$e404 = TRUE;
	}
	else
	{
		require_once(APPPATH.'controllers/'.$RTR->directory.$class.'.php');

		if ( ! class_exists($class, FALSE) OR $method[0] === '_' OR method_exists('CI_Controller', $method))
		{
			$e404 = TRUE;
		}
		elseif (method_exists($class, '_remap'))
		{
			$params = array($method, array_slice($URI->rsegments, 2));
			$method = '_remap';
		}
		elseif ( ! method_exists($class, $method))
		{
			$e404 = TRUE;
		}
		/**
		 * DO NOT CHANGE THIS, NOTHING ELSE WORKS!
		 *
		 * - method_exists() returns true for non-public methods, which passes the previous elseif
		 * - is_callable() returns false for PHP 4-style constructors, even if there's a __construct()
		 * - method_exists($class, '__construct') won't work because CI_Controller::__construct() is inherited
		 * - People will only complain if this doesn't work, even though it is documented that it shouldn't.
		 *
		 * ReflectionMethod::isConstructor() is the ONLY reliable check,
		 * knowing which method will be executed as a constructor.
		 */
		elseif ( ! is_callable(array($class, $method)))
		{
			$reflection = new ReflectionMethod($class, $method);
			if ( ! $reflection->isPublic() OR $reflection->isConstructor())
			{
				$e404 = TRUE;
			}
		}
	}

	if ($e404)
	{
		if ( ! empty($RTR->routes['404_override']))
		{
			if (sscanf($RTR->routes['404_override'], '%[^/]/%s', $error_class, $error_method) !== 2)
			{
				$error_method = 'index';
			}

			$error_class = ucfirst($error_class);

			if ( ! class_exists($error_class, FALSE))
			{
				if (file_exists(APPPATH.'controllers/'.$RTR->directory.$error_class.'.php'))
				{
					require_once(APPPATH.'controllers/'.$RTR->directory.$error_class.'.php');
					$e404 = ! class_exists($error_class, FALSE);
				}
				// Were we in a directory? If so, check for a global override
				elseif ( ! empty($RTR->directory) && file_exists(APPPATH.'controllers/'.$error_class.'.php'))
				{
					require_once(APPPATH.'controllers/'.$error_class.'.php');
					if (($e404 = ! class_exists($error_class, FALSE)) === FALSE)
					{
						$RTR->directory = '';
					}
				}
			}
			else
			{
				$e404 = FALSE;
			}
		}

		// Did we reset the $e404 flag? If so, set the rsegments, starting from index 1
		if ( ! $e404)
		{
			$class = $error_class;
			$method = $error_method;

			$URI->rsegments = array(
				1 => $class,
				2 => $method
			);
		}
		else
		{
			show_404($RTR->directory.$class.'/'.$method);
		}
	}

	if ($method !== '_remap')
	{
		$params = array_slice($URI->rsegments, 2);
	}

/*
 * ------------------------------------------------------
 *  Is there a "pre_controller" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('pre_controller');

/*
 * ------------------------------------------------------
 *  Instantiate the requested controller
 * ------------------------------------------------------
 */
	// Mark a start point so we can benchmark the controller
	$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_start');

	$CI = new $class();

/*
 * ------------------------------------------------------
 *  Is there a "post_controller_constructor" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_controller_constructor');

/*
 * ------------------------------------------------------
 *  Call the requested method
 * ------------------------------------------------------
 */
	call_user_func_array(array(&$CI, $method), $params);

	// Mark a benchmark end point
	$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_end');

/*
 * ------------------------------------------------------
 *  Is there a "post_controller" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_controller');

/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */
	if ($EXT->call_hook('display_override') === FALSE)
	{
		$OUT->_display();
	}

/*
 * ------------------------------------------------------
 *  Is there a "post_system" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_system');
