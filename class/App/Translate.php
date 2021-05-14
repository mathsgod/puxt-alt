<?php
namespace App;

use Symfony\Component\Yaml\Yaml;


class Translate extends Model
{
	private static $_CACHE = [];
	private static $_CACHE_DB = null;
	public static function ByModule($module, $language)
	{
		//self::$_app->log("Translate::ByModule");
		if (self::$_CACHE[$module][$language]) return self::$_CACHE[$module][$language];
		$data = [];

		$lang = explode("-", $language);

		if ($ini = self::$_app->getFile("translate.ini")) {
			$ini = parse_ini_file($ini, true);
			if ($ini[$lang[0]]) $data = array_merge($data, $ini[$lang[0]]);
			if ($ini[$language]) $data = array_merge($data, $ini[$language]);
		}

		if ($yml = self::$_app->getFile("translate.yml")) {
			$yml = Yaml::parseFile($yml);
			if ($yml[$lang[0]]) $data = array_merge($data, $yml[$lang[0]]);
			if ($yml[$language]) $data = array_merge($data, $yml[$language]);
		}

		if ($ini = self::$_app->getFile("pages/translate.ini")) {
			$ini = parse_ini_file($ini, true);
			if ($ini[$lang[0]]) $data = array_merge($data, $ini[$lang[0]]);
			if ($ini[$language]) $data = array_merge($data, $ini[$language]);
		}

		if ($yml = self::$_app->getFile("pages/translate.yml")) {
			$yml = Yaml::parseFile($yml);
			if ($yml[$lang[0]]) $data = array_merge($data, $yml[$lang[0]]);
			if ($yml[$language]) $data = array_merge($data, $yml[$language]);
		}

		if ($ini = self::$_app->getFile("pages/{$module}/translate.ini")) {
			$ini = parse_ini_file($ini, true);
			if ($ini[$lang[0]]) $data = array_merge($data, $ini[$lang[0]]);
			if ($ini[$language]) $data = array_merge($data, $ini[$language]);
		}

		if ($yml = self::$_app->getFile("pages/{$module}/translate.yml")) {
			$yml = Yaml::parseFile($yml);
			if ($yml[$lang[0]]) $data = array_merge($data, $yml[$lang[0]]);
			if ($yml[$language]) $data = array_merge($data, $yml[$language]);
		}

		if (!isset(self::$_CACHE_DB)) {
			self::$_CACHE_DB = [];
			foreach (self::Query() as $t) {
				if (!$t->module) {
					self::$_CACHE_DB["-"][$t->language][$t->name] = $t->value;
				} else {
					self::$_CACHE_DB[$t->module][$t->language][$t->name] = $t->value;
				}
			}
		}
		if (self::$_CACHE_DB["-"][$language]) {
			foreach (self::$_CACHE_DB["-"][$language] as $k => $v) {
				$data[$k] = $v;
			}
		}

		if (self::$_CACHE_DB[$module][$language]) {
			foreach (self::$_CACHE_DB[$module][$language] as $k => $v) {
				$data[$k] = $v;
			}
		}

		/*

		$w = [];
		$w[] = "module is null";
		$w[] = "language=" . self::__db()->quote($language);

		foreach (Translate::Find($w) as $translate) {
			$data[$translate->name] = $translate->value;
		}

		$w = [];
		$w[] = "module=" . self::__db()->quote($module);
		$w[] = "language=" . self::__db()->quote($language);

		foreach (Translate::Find($w) as $translate) {
			$data[$translate->name] = $translate->value;
		}*/

		self::$_CACHE[$module][$language] = $data;
		return $data;
	}

	public static function _($name, $language)
	{
		self::$_app->log("Translate::_", [$name, $language]);
		$w[] = "name=" . self::__db()->quote($name);
		$w[] = "language=" . self::__db()->quote($language);

		$t = Translate::first($w);
		if (!$t) {
			// find in ini
			$translate_ini = parse_ini_file(SYSTEM . "/translate.ini", true);

			if (isset($translate_ini[$language][$name])) {
				return $translate_ini[$language][$name];
			}
			return $name;
		} else {
			return $t->value;
		}
	}

	public function __toString()
	{
		return $this->value;
	}

	public function delete()
	{
		// delete other
		$w[] = ["name=?", $this->name];
		if ($this->module) {
			$w[] = ["module=?", $this->module];
		} else {
			$w[] = "module is null";
		}

		if ($this->action) {
			$w[] = ["action=?", $this->action];
		} else {
			$w[] = "action is null";
		}

		Translate::Query()->where($w)->delete()->execute();
	}

	public function get($language)
	{
		$w[] = "name=" . self::__db()->quote($this->name);
		$w[] = "language=" . self::__db()->quote($language);
		if ($this->module) {
			$w[] = "module=" . self::__db()->quote($this->module);
		} else {
			$w[] = "module is null";
		}

		if ($this->action) {
			$w[] = "action=" . self::__db()->quote($this->action);
		} else {
			$w[] = "action is null";
		}

		return Translate::first($w);
	}
}
