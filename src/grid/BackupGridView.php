<?php

namespace hipanel\modules\hosting\grid;

use hipanel\grid\ActionColumn;
use hipanel\grid\MainColumn;
use hipanel\modules\hosting\widgets\backup\ObjectLabelWidget;
use hipanel\modules\server\grid\ServerColumn;
use Yii;
use yii\helpers\Html;

class BackupGridView extends \hipanel\grid\BoxedGridView
{
    static public function defaultColumns()
    {
        return [
            'id' => [
                'format' => 'html',
                'attribute' => 'id',
                'value' => function($model) {
                    return Html::tag('span', $model->id, ['class' => 'bold']);
                },
            ],
            'object_id' => [
                'class' => MainColumn::className(),
                'filterAttribute' => 'id',
                'attribute' => 'object_id'
            ],
            'backup' => [
                'class' => MainColumn::className(),
                'filterAttribute' => 'backup_like',
            ],
            'server' => [
                'attribute' => 'server_id',
                'class' => ServerColumn::className(),
            ],
            'account' => [
                'attribute' => 'account_id',
                'class' => AccountColumn::className()
            ],
            'object' => [
                'format' => 'raw',
                'attribute' => 'name',
                'value' => function($model) {
                    $labelType = ObjectLabelWidget::widget(compact('model'));
                    return $labelType
                    . '&nbsp;' .
                    Html::a($model->name, [sprintf('/hosting/%s/view', $model->object), 'id' => $model->object_id], ['data-pjax' => 0]);
                }
            ],
            'name' => [
                'format' => 'raw',
                'attribute' => 'name',
                'value' => function($model) {
                    return Html::a($model->name, [sprintf('/hosting/%s/view', $model->object), 'id' => $model->object_id], ['data-pjax' => 0]);
                }
            ],
            'size_gb' => [
                'attribute' => 'size_gb',
                'filter' => false,
                'value' => function($model) {
                    return sprintf('%s GB', $model->size_gb);
                }
            ],
            'actions' => [
                'class' => ActionColumn::className(),
                'template' => '{view} {delete}',
            ],
        ];
    }
}
