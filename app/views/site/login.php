<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use VK\VK;

$this->title = 'VK.Parser вход не выполнен';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin(); ?>
<h1>Вход не выполнен</h1>
<div class="site-login row">
	<div class='col-md-3'>
		<?= $form->field($model, 'vk_token') ?>
		<p class="help-block">
		  Чтобы получить токен, перейдите по <?= Html::a('ссылке', $authorize_url, ['target'=>'new-wind']);?>,
			скопируйте адрес в адресной строке и вставьте в это поле. 
		</p>
		<div class="form-group">
       		<?= Html::a('Получить токен', $authorize_url, ['target'=>'new-wind', 'class'=>'btn btn-success']);?> 
					<?= Html::submitButton('Войти', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
		</div>
	</div>
</div>
<?php ActiveForm::end(); ?>
<?php $js = " 
		$('[target=new-wind]').click(function(ev){
			ev.preventDefault();
			window.open(this.href, '_blank', 'width=420,height=230,resizable=yes,scrollbars=yes,status=yes')
		})
";
$this->registerJs($js, \yii\web\view::POS_READY);