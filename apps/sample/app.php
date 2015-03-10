<?

Module::import([
	"Router",
	"Directives",
	"mod"
]);


Router::on("index",array("sampleCtrl","start"));

Router::on("page","pageCtrl");