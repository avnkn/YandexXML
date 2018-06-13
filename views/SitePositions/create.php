<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<h1>Добавление сайта</h1>
<div class="row">
    <div class="col-md-4">
        <?php $form = ActiveForm::begin(); ?>
            <?= $form->field($model, 'url')->label('Url сайта (http://..)') ?>
            <?= $form->field($model, 'lr')->label('Регион') ?>
            <?= $form->field($model, 'key')->label('Запросы')->textarea(['rows' => '10']) ?>
            <div class="form-group">
                <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
