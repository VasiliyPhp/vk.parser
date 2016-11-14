<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
$user = \yii::$app->user;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <link rel="shortcut icon" href="<?=\yii::$app->homeUrl?>/favicon.ico">
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    if(!$user->isGuest){
			NavBar::begin([
					'brandLabel' => 'VK.Parser',
					'brandUrl' => Yii::$app->homeUrl,
					'options' => [
							'class' => 'navbar-inverse navbar-fixed-top',
					],
			]);
			$ident = $user->identity;
			echo Nav::widget([
					'options' => ['class' => 'navbar-nav navbar-right'],
					'items' => [
										// ['label'=>$ident->vk_first_name . ' ' . $ident->vk_last_name . ', id  ' . $ident->vk_user_id],
									  ['label'=>'Выйти',
											'url' => ['site/logout'],
											'linkOptions'=>[
												'data' => [
														'confirm' => 'Уверены, что хотите выйти?',
														'method' => 'post',
												],
											],
										],
					],
			]);
			NavBar::end();
    } 
    ?>

    <div class="container">
				<?= Breadcrumbs::widget([
				    'homeLink' => [
							 'label' => 'VK.Photos',  // required
							 'url' => Yii::$app->homeUrl,      // optional, will be processed by Url::to()
							 // 'template' => 'own template of the item', // optional, if not set $this->itemTemplate will be used
							],
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
