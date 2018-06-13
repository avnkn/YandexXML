<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<h1>Редактирование сайта</h1>
<div class="row">
    <div class="col-md-6">
        <?php $form = ActiveForm::begin([
            'id' => 'update-form',
            'method' => 'update',
            'action' => "/sitepositions/update",
        ]) ?>
            <?= $form->field($model, 'id')->hiddenInput(); ?>
            <?= $form->field($model, 'url')->label('Url сайта (http://..)') ?>
            <?= $form->field($model, 'lr')->label('Регион') ?>
            <?= $form->field($model, 'key')->label('Запросы')->textarea(['rows' => '15']) ?>
            <div class="form-group">
                <?= Html::submitButton('Изменить', ['class' => 'btn btn-primary']) ?>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
