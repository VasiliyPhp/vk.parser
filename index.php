<?php
// echo phpinfo(); exit;
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

set_time_limit(0);

require(__DIR__ . '/../vk.photos1/app.yii/vendor/autoload.php');
require(__DIR__ . '/../vk.photos1/app.yii/vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/app/config/vk.php');

(new yii\web\Application($config))->run();

function j($m){
	printf('<pre>%s</pre>',print_r($m,1));
  die;	
}
function x($m){
	printf('<pre>%s</pre>',print_r($m,1));
	flush();
	ob_flush();
}
