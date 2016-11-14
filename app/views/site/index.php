<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Tabs;
/* @var $this yii\web\View */
/* @var $model \app\models\SourceAlbumForm */
/* @var $form ActiveForm */

$this->title = 'Вк парсер';
?>
<h1><?=$this->title?></h1>
<div class="site-index">
<?php 
ob_start();
$form = ActiveForm::begin(['action'=>['site/group-parser']]);
echo $form->field($GroupParser,'queries') ->textarea()
	. $form->field($GroupParser,'country')->dropDownlist($GroupParser->countries,['id'=>'x-country']) 
	. $form->field($GroupParser,'region')->dropDownlist([],['id'=>'x-region','disabled'=>true])
	. $form->field($GroupParser,'city')->dropDownlist([],['id'=>'x-city','disabled'=>true])
	. $form->field($GroupParser,'closed')->checkbox()
	. Html::submitButton('Собрать', ['class'=>'btn btn-success']);
	$form1 = ob_get_contents();
ob_end_clean();
?>
  <div class=row>
  	<div class="col-md-4">
			<?=  Tabs::widget([
				  'items'=>[
					  [
						  'label'=>'Поиск групп',
							'content'=>$form1,		
						],
					  [
						  'label'=>'Поиск групп',
							'content'=>'привет',
						],
					]
				]);
			?>
		</div>
	</div>
	<?php ActiveForm::end();?>
</div><!-- site-index -->




<?php 
$js = "
$('#x-country').change(function(){
	var country = this.value;
	var regions;
	var city_cont = $('#x-region')
	var url = '".yii::$app->urlManager->createUrl(['site/get-regions'])."';
	city_cont.html('');
	if(!country){
		return ;
	}
	$.get(url+'?country='+country, function(regions){
  	var opt = '';
		for(var i in regions){
			opt += '<option vaue=\"'+i+'\">'+regions[i]+'</option>';
		};
		$(opt).appendTo(city_cont);
	})
})
$('#x-region').change(function(){
	var val = this.value;
})


";
$this->registerJs($js, yii\web\view::POS_READY);
?>






