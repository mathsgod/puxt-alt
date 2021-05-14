<?php
// Created By: Raymond Chong
namespace App;

class UI extends Model
{


	public function hasWidth()
	{
		foreach ($this->layout() as $k => $v) {
			if ($v["width"]) return true;
		}
		return false;
	}

	public function content()
	{
		return json_decode($this->layout, true);
	}

	public function layout(): array
	{
		return json_decode($this->layout, true) ?? [];
	}

	public function isShowColumn($index)
	{
		$layout = $this->Layout();
		if (is_null($layout[$index])) return true;
		if (is_null($layout[$index]["show"])) return true;
		return false;
	}

	public function Width($index)
	{
		$layout = $this->Layout();
		return $layout[$index]["width"];
	}

	public static function set($uri, $index, $value)
	{
		$ui = self::_($uri);
		if (!$ui) {
			$ui = new UI();
			$ui->user_id = App::UserID();
			$ui->uri = $uri;
			$ui->layout = json_encode(array($index => $value));
			return $ui->save();
		} else {
			$layout = json_decode($ui->layout, true);
			if ($layout[$index] == $value) return;
			$layout[$index] = $value;
			$ui->layout = json_encode($layout);
			return $ui->save();
		}
	}

	public static function get($uri, $index)
	{
		$ui = self::_($uri);
		if (!$ui) return null;
		$layout = json_decode($ui->layout, true);
		return $layout[$index];
	}
}
