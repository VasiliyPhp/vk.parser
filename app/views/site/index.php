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
	. $form->field($GroupParser,'country')->dropDownlist($GroupParser->countries,['id'=>'x-country', 'prompt'=>'Выберите страну']) 
	. $form->field($GroupParser,'region')->dropDownlist([],['id'=>'x-region', 'prompt'=>'Выберите регион','disabled'=>true])
	. $form->field($GroupParser,'city')->dropDownlist([],['id'=>'x-city', 'prompt'=>'Выберите город','disabled'=>true])
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
	<?php if($result){?>
	<div class='row'>
		<div class='col-md-4'>
			<textarea class="form-control">
			<?= implode("\n", array_column($result, 'name')) ?>
			</textarea>
		</div>
	</div>
	<?php }?>
	<?php ActiveForm::end();?>
</div><!-- site-index -->




<?php 
$js = "
var city_cont = $('#x-city');
var region_cont = $('#x-region');
var country_cont = $('#x-country');
$('#x-country').change(function(){
	var country = this.value;
	var regions;
	var url = '".yii::$app->urlManager->createUrl(['site/get-regions'])."';
	region_cont.attr('disabled', true).html('');
	city_cont.attr('disabled', true).html('');
	if(!country){
		return ;
	}
	$.get(url+'?country='+country, function(response){
		var opt = '',
			src = null,
			into = null;
		if(response.regions){
			src = response.regions;
			into = region_cont;
		}else if(response.city){
			src = response.city;
			into = city_cont;
		}
		for(var i in src){
			opt += '<option value=\"'+i+'\">'+src[i]+'</option>';
		};
		$(opt).appendTo(into);
		into.attr('disabled', false);
	});
})

$('#x-region').change(function(){
	var region = this.value;
	var country = country_cont[0].value;
	var cities;
	var url = '".yii::$app->urlManager->createUrl(['site/get-cities'])."';
	// region_cont.attr('disabled', true).html('');
	city_cont.attr('disabled', true).html('');
	if(!region){
		return ;
	}
	$.get(url+'?region='+region+'&country='+country, function(response){
		var opt = '',
			src = null,
			into = null;
		if(response.cities){
			src = response.cities;
			into = city_cont;
		}
		for(var i in src){
			opt += '<option value=\"'+i+'\">'+src[i]+'</option>';
		};
		$(opt).appendTo(into);
		into.attr('disabled', false);
	});
})


";
$this->registerJs($js, yii\web\view::POS_READY);
?>






