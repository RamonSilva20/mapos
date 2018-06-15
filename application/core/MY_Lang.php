<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018, British Columbia Institute of Technology
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
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014-2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Language Class extension.
 * 
 * Adds language fallback handling.
 * 
 * When loading a language file, CodeIgniter will load first the english version, 
 * if appropriate, and then the one appropriate to the language you specify. 
 * This lets you define only the language settings that you wish to over-ride 
 * in your idiom-specific files.
 * 
 * This has the added benefit of the language facility not breaking if a new 
 * language setting is added to the built-in ones (english), but not yet 
 * provided for in one of the translations.
 * 
 * To use this capability, transparently, copy this file (MY_Lang.php)
 * into your application/core folder.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Language
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/libraries/language.html
 */
class MY_Lang extends CI_Lang {

	/**
	 * Refactor: base language provided inside system/language
	 * 
	 * @var string
	 */
	public $base_language = 'english';

	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Load a language file, with fallback to english.
	 *
	 * @param	mixed	$langfile	Language file name
	 * @param	string	$idiom		Language name (english, etc.)
	 * @param	bool	$return		Whether to return the loaded array of translations
	 * @param 	bool	$add_suffix	Whether to add suffix to $langfile
	 * @param 	string	$alt_path	Alternative path to look for the language file
	 *
	 * @return	void|string[]	Array containing translations, if $return is set to TRUE
	 */
	public function load($langfile, $idiom = '', $return = FALSE, $add_suffix = TRUE, $alt_path = '')
	{
		if (is_array($langfile))
		{
			foreach ($langfile as $value)
			{
				$this->load($value, $idiom, $return, $add_suffix, $alt_path);
			}

			return;
		}

		$langfile = str_replace('.php', '', $langfile);

		if ($add_suffix === TRUE)
		{
			$langfile = preg_replace('/_lang$/', '', $langfile) . '_lang';
		}

		$langfile .= '.php';

		if (empty($idiom) OR ! preg_match('/^[a-z_-]+$/i', $idiom))
		{
			$config = & get_config();
			$idiom = empty($config['language']) ? $this->base_language : $config['language'];
		}

		if ($return === FALSE && isset($this->is_loaded[$langfile]) && $this->is_loaded[$langfile] === $idiom)
		{
			return;
		}

		// load the default language first, if necessary
		// only do this for the language files under system/
		$basepath = SYSDIR . 'language/' . $this->base_language . '/' . $langfile;
		if (($found = file_exists($basepath)) === TRUE)
		{
			include($basepath);
		}

		// Load the base file, so any others found can override it
		$basepath = BASEPATH . 'language/' . $idiom . '/' . $langfile;
		if (($found = file_exists($basepath)) === TRUE)
		{
			include($basepath);
		}

		// Do we have an alternative path to look in?
		if ($alt_path !== '')
		{
			$alt_path .= 'language/' . $idiom . '/' . $langfile;
			if (file_exists($alt_path))
			{
				include($alt_path);
				$found = TRUE;
			}
		} else
		{
			foreach (get_instance()->load->get_package_paths(TRUE) as $package_path)
			{
				$package_path .= 'language/' . $idiom . '/' . $langfile;
				if ($basepath !== $package_path && file_exists($package_path))
				{
					include($package_path);
					$found = TRUE;
					break;
				}
			}
		}

		if ($found !== TRUE)
		{
			show_error('Unable to load the requested language file: language/' . $idiom . '/' . $langfile);
		}

		if (!isset($lang) OR ! is_array($lang))
		{
			log_message('error', 'Language file contains no data: language/' . $idiom . '/' . $langfile);

			if ($return === TRUE)
			{
				return array();
			}
			return;
		}

		if ($return === TRUE)
		{
			return $lang;
		}

		$this->is_loaded[$langfile] = $idiom;
		$this->language = array_merge($this->language, $lang);

		log_message('info', 'Language file loaded: language/' . $idiom . '/' . $langfile);
		return TRUE;
	}
	
}

// --------------------------------------------------------------------
// The method below was used with phpunit to ensure correctness of the above.
//	public function test_fallback()
//	{
//		// system target language file
//		$this->ci_vfs_create('system/language/martian/number_lang.php', "<?php \$lang['fruit'] = 'Apfel';");
//		$this->assertTrue($this->lang->load('number', 'martian'));
//		$this->assertEquals('Apfel', $this->lang->language['fruit']);
//		$this->assertEquals('Bytes', $this->lang->language['bytes']);
//
//		// application target language file
//		$this->ci_vfs_create('application/language/klingon/number_lang.php', "<?php \$lang['fruit'] = 'Apfel';");
//		$this->assertTrue($this->lang->load('number', 'klingon'));
//		$this->assertEquals('Apfel', $this->lang->language['fruit']);
//		$this->assertEquals('Bytes', $this->lang->language['bytes']);
//
//		// both system & application language files
//		$this->ci_vfs_create('system/language/romulan/number_lang.php', "<?php \$lang['apple'] = 'Core';");
//		$this->ci_vfs_create('application/language/romulan/number_lang.php', "<?php \$lang['fruit'] = 'Cherry';");
//		$this->assertTrue($this->lang->load('number', 'romulan'));
//		$this->assertEquals('Cherry', $this->lang->language['fruit']);
//		$this->assertEquals('Bytes', $this->lang->language['bytes']);
//		$this->assertEquals('Core', $this->lang->language['apple']);
//	}