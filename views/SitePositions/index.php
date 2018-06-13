<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
?>
<h1>Сайты</h1>
<div class="row">
    <div class="col-md-4">
        <table class="table table-bordered">
            <tr>
                <th>№</th>
                <th>Сайт</th>
                <th>Регион</th>
                <th>Действия</th>
            </tr>
        <?php $i = 1; ?>
        <?php foreach ($sites as $site) : ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $site->host ?></td>
                <td><?= $site->lr ?></td>
                <td>
                    <?php $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'method' => 'delete',
                        'action' => "/sitepositions/delete",
                        'options' => ['class' => 'form-icons'],
                    ]) ?>
                    <?php echo $form->field($model, 'id')
                        ->hiddenInput(['value' => $site->id]); ?>
                    <?= Html::submitButton('<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>', ['class' => 'button-delete']) ?>
                    <?php ActiveForm::end() ?>

                    <?php $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'method' => 'update',
                        'action' => "/sitepositions/update",
                        'options' => ['class' => 'form-icons'],
                    ]) ?>
                    <?php echo $form->field($model, 'id')
                        ->hiddenInput(['value' => $site->id]); ?>
                    <?= Html::submitButton('<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>', ['class' => 'button-update']) ?>
                    <?php ActiveForm::end() ?>

                    <a href="/sitepositions/get-positions?id=<?= $site->id ?>" title="Получить позиции">
                        <span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </table>

    </div>
</div>


<?= LinkPager::widget(['pagination' => $pagination]) ?>
