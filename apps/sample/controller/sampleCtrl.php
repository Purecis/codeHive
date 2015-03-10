<?

class sampleCtrl{
	public static function start(&$scope,$router){
		global $config;

		Assets::script("app.js");
		Assets::style("app.css");

		$scope->title = $config['title'];

		return View::load("index");
	}
}