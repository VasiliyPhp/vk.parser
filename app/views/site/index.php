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
  <div class=row>
<?php 
ob_start();
$form = ActiveForm::begin(['action'=>['site/group-parser']]);
echo $form->field($GroupParser,'queries') ->textarea()
	. $form->field($GroupParser,'country')->dropDownlist($GroupParser->countries,['data-x-country'=>1, 'prompt'=>'Выберите страну']) 
	. $form->field($GroupParser,'region')->dropDownlist([],['data-x-region'=>1, 'prompt'=>'','disabled'=>true])
	. $form->field($GroupParser,'city')->dropDownlist([],['data-x-city'=>1, 'prompt'=>'','disabled'=>true])
	. $form->field($GroupParser,'m_city')->dropDownlist([],['data-x-m-city'=>1, 'prompt'=>'','disabled'=>true])
	. $form->field($GroupParser,'closed')->checkbox()
	. '<div class=form-group >' . Html::submitButton('Собрать', ['class'=>'btn btn-success']) . '</div>';

	 ActiveForm::end();
	 $form1 = ob_get_contents();
ob_end_clean();
ob_start();
$form = ActiveForm::begin(['action'=>['site/people-from-group-parser']]);
echo $form->field($PeopleFromGroup,'groups') ->textarea()
		. $form->field($PeopleFromGroup,'open_mess')->checkbox()
		. $form->field($PeopleFromGroup,'open_wall')->checkbox()
  	. '<div class=form-group >' . Html::submitButton('Собрать', ['class'=>'btn btn-success']) . '</div>';
  ActiveForm::end();
	$form2 = ob_get_contents();
ob_end_clean();
ob_start();
$form = ActiveForm::begin(['action'=>['site/people-from-search-parser']]);
echo $form->field($PeopleSearch,'queries') ->textarea()
	. $form->field($PeopleSearch,'country')->dropDownlist(\app\models\parser\GroupParser::getCountries(),
	  ['data-x-country'=>1, 'prompt'=>'Выберите страну']) 
	. $form->field($PeopleSearch,'region')->dropDownlist([],['data-x-region'=>1, 'prompt'=>'','disabled'=>true])
	. $form->field($PeopleSearch,'city')->dropDownlist([],['data-x-city'=>1, 'prompt'=>'','disabled'=>true])
	. $form->field($PeopleSearch,'m_city')->dropDownlist([],['data-x-m-city'=>1, 'prompt'=>'','disabled'=>true])
	. $form->field($PeopleSearch,'age_from')->textInput(['type'=>'number'])
	. $form->field($PeopleSearch,'age_to')->textInput(['type'=>'number'])
	. $form->field($PeopleSearch,'sex')->dropDownlist(['0'=>'Любой','2'=>'Мужской','1'=>'Женский'])
	. $form->field($PeopleSearch,'open_mess')->checkbox()
	. $form->field($PeopleSearch,'open_wall')->checkbox()
  . '<div class=form-group >' . Html::submitButton('Собрать', ['class'=>'btn btn-success']) . '</div>';

	 ActiveForm::end();
	 $form3 = ob_get_contents();
ob_end_clean();
ob_start();
$form = ActiveForm::begin(['action'=>['site/people-info-parser']]);
echo $form->field($PeopleInfo,'peoples')->textarea()
	. '<div class=form-group >' . Html::submitButton('Собрать', ['class'=>'btn btn-success']) . '</div>';
	 ActiveForm::end();
	 $form4 = ob_get_contents();
ob_end_clean();
?>
<div class='col-md-12 col-lg-12'>
  <a class='btn btn-success' href='#' id='clear-cache'>Очистить кэш</a>
	<br/>
</div>

  	<div class="col-lg-8 col-md-10">
			<?=  Tabs::widget([
				  'items'=>[
					  [
						  'label'=>'Поиск групп',
							'content'=>$form1,		
						],
					  [
						  'label'=>'Поиск людей из групп',
							'content'=>$form2,
						],
					  [
						  'label'=>'Люди из поиска ВК',
							'content'=>$form3,
						],
					  [
						  'label'=>'Информация о людях ВК',
							'content'=>$form4,
						],
					]
				]);
			?>
		</div>
	</div>
	<?php if(isset($result) and $result){?>
	<div class="form-group lead">
    <p><span class="label label-primary"><?= count($result) . '  найдено'?></span></p>
    <button class='btn btn-primary x-gr-cp'>Скопировать группы</button>
	</div>	
	<div class="row">
		<div class=col-md-12 >
			<table class="x-gr-res table" >
				<?php foreach($result as $res){?>
				<tr>
					<td><?=$res['id']?></td><td><?=$res['contacts']?></td>
				</tr>
				<?php } ?> 
			</table>
		</div>
	</div>
	<?php }?>
	<?php if(isset($resultPeopleFrom) and $resultPeopleFrom){?>
	<div class="form-group lead">
    <p><span class="label label-primary"><?= count($resultPeopleFrom) . '  найдено'?></span></p>
		<button class='btn btn-primary x-gr-cp'>Скопировать людей</button>
	</div>	
	<div class="row">
		<div class=col-md-12 >
			<table class="x-gr-res table" >
				<?php foreach($resultPeopleFrom as $res){?>
				<tr>
					<td><?=$res['id']?></td>
				</tr>
				<?php } ?> 
			</table>
		</div>
	</div>
	<?php }?>  
	<?php if(isset($resultPeopleInfo) and $resultPeopleInfo){?>
	<div class="form-group lead">
    <p><span class="label label-primary"><?= count($resultPeopleInfo) . '  найдено'?></span></p>
		<button class='btn btn-primary x-gr-cp'>Скопировать</button>
	</div>	
	<div class="row">
		<div class=col-md-12 >
			<table class="x-gr-res table" >
				<?php foreach($resultPeopleInfo as $res){?>
				<tr>
					<td><?=$res['id']?></td>
					<td><?=$res['first_name']?></td>
					<td><?=$res['last_name']?></td>
					<td><?=$res['sex']?></td>
					<td><?=$res['bdate']?></td>
					<td><?=$res['country']?></td>
					<td><?=$res['city']?></td>
					<td><?=$res['open_mess']?></td>
					<td><?=$res['last_seen']?></td>
					<td><?=$res['child']?></td>
					<td><?=$res['relation']?></td>
				</tr>
				<?php } ?> 
			</table>
		</div>
	</div>
	<?php }?>
</div><!-- site-index -->

<?php 
$css = ".fix-height>div{
	height: 40px;
	overflow:auto;
	padding:4px;
}
.fix-height>div:nth-of-type(even){
	background:#e9e9ea;
}";
$this->registerCss($css);
$js = "
var city_cont = $('[data-x-city]');
var m_city_cont = $('[data-x-m-city]');
var region_cont = $('[data-x-region]');
var country_cont = $('[data-x-country]');
var country_cont = $('[data-x-country]');
country_cont.change(function(){
	var country = this.value;
	var regions;
	var url = '".yii::$app->urlManager->createUrl(['site/get-regions'])."';
	var url_m = '".yii::$app->urlManager->createUrl(['site/get-main-cities'])."';
	country_cont.val(country);
	region_cont.attr('disabled', true).html('');
	city_cont.attr('disabled', true).html('');
	m_city_cont.attr('disabled', true).html('');
	if(!country){
		return ;
	}
	$.get(url_m+'?country='+country, function(response){
		var opt = '',
			src = response.main_cities,
		  into = m_city_cont;
		for(var i in src){
			opt += '<option value=\"'+i+'\">'+src[i]+'</option>';
		};
		$(opt).appendTo(into);
		into.attr('disabled', false);
	});
	$.get(url+'?country='+country, function(response){
		var opt = '',
			src = null,
			into = null;
		if(response.regions){
			src = response.regions;
			into = region_cont;
		}else if(response.cities){
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

region_cont.change(function(){
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
$('#clear-cache').click(function(ev){
	ev.preventDefault();
	ev.stopPropagation();
	var url = '".yii::$app->urlManager->createUrl(['site/clear-cache'])."';
	var \$this = $(this);
	\$this.attr('disabled',true);
	$.get(url, function(){
		\$this.attr('disabled',false);
	});
});
$('.x-gr-cp').click(function(ev){
	var copied = $('.x-gr-res tbody');
	ev.preventDefault();
	ev.stopPropagation();
	copied.attr('contentEditable',true).focus();
	document.execCommand('SelectAll');
	document.execCommand('Copy');
	// copied.attr('contentEditable',false)
	
});

";
$this->registerJs($js, yii\web\view::POS_READY);
?>






