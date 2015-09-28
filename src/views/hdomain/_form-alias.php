<?php

/* @var $this View */
/* @var $model hipanel\modules\hosting\models\Hdomain */
/* @var $type string */

use hipanel\base\View;
use hipanel\modules\client\widgets\combo\ClientCombo;
use hipanel\modules\hosting\widgets\combo\HdomainCombo;
use hipanel\modules\hosting\widgets\combo\SshAccountCombo;
use hipanel\modules\hosting\widgets\combo\VhostCombo;
use hipanel\modules\server\widgets\combo\ServerCombo;
use hiqdev\combo\StaticCombo;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$form = ActiveForm::begin([
    'id'                     => 'dynamic-form',
    'enableClientValidation' => true,
    'validateOnBlur'         => true,
    'enableAjaxValidation'   => true,
    'validationUrl'          => Url::toRoute(['validate-form', 'scenario' => $model->scenario]),
]); ?>

<div class="container-items">
    <?php foreach ($models as $i => $model) { ?>
        <div class="row">
            <div class="col-md-4">
                <div class="box box-danger">
                    <div class="box-body">
                        <div class="form-instance" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
                            <?php
                            if (Yii::$app->user->can('support')) {
                                print $form->field($model, "[$i]client")->widget(ClientCombo::className(), ['formElementSelector' => '.form-instance']);
                            }

                            print $form->field($model, "[$i]server")->widget(ServerCombo::className(), ['formElementSelector' => '.form-instance']);
                            print $form->field($model, "[$i]account")->widget(SshAccountCombo::className(), [
                                'formElementSelector' => '.form-instance',
                                'inputOptions'        => [
                                    'data-field' => 'account'
                                ],
                            ]);
                            print $form->field($model, "[$i]vhost_id")->widget(VhostCombo::className(), ['formElementSelector' => '.form-instance']);

                            $model->alias_type = 'subdomain';
                            print $form->field($model, "[$i]alias_type")->radio([
                                    'value' => 'subdomain',
                                    'class' => 'alias-type',
                                    'label' => Yii::t('app', 'Subdomain of existing domain'),
                                ]);
                            print $form->field($model, "[$i]alias_type")->radio([
                                    'id' => $model->formName() . '-' . $i . '-alias_type-new',
                                    'value' => 'new',
                                    'class' => 'alias-type',
                                    'label' => Yii::t('app', 'New domain')
                                ]);
                            ?>

                            <div class="alias-subdomain form-inline">
                                <?= $form->field($model, "[$i]subdomain")->input('text',  ['data-field' => 'subdomain'])->label(false) ?>
                                <?= Html::tag('span', '.') ?>
                                <?= $form->field($model, "[$i]dns_hdomain_id")->widget(HdomainCombo::className(), [
                                    'formElementSelector' => '.form-instance',
                                    'inputOptions' => [
                                        'data-field' => 'dns_hdomain_id'
                                    ],
                                    'pluginOptions' => [
                                        'onChange' => new JsExpression("function () {
                                                $(this).closest('.form-instance').find('input[data-field=\"sub-with-domain\"]').trigger('update');
                                            }
                                        ")
                                    ]
                                ])->label(false) ?>
                                <?= $form->field($model, "[$i]domain")->hiddenInput([
                                    'id' => $model->formName() . '-' . $i . '-domain-sub',
                                    'data-field' => 'sub-with-domain'
                                ])->label(false) ?>
                            </div>
                            <div class="alias-newdomain">
                                <?= $form->field($model, "[$i]domain")->input('text', [
                                    'data-field' => 'domain',
                                    'disabled' => true,
                                    'class' => 'form-control collapse'
                                ])->label(false) ?>
                            </div>
                            <?= $form->field($model, "[$i]with_www")->checkbox() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
<?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-default']) ?>
    &nbsp;
<?= Html::button(Yii::t('app', 'Cancel'), ['class' => 'btn btn-default', 'onclick' => 'history.go(-1)']) ?>
<?php ActiveForm::end();

$this->registerJs(<<<'JS'
    $(this).on('change', '.alias-type', function (e) {
        var $form = $(this).closest('.form-instance');

        var $sub_inputs = $form.find('.alias-subdomain, input[data-field="subdomain"], input[data-field="sub-with-domain"]');
        var $new_inputs = $form.find('.alias-newdomain, input[data-field="domain"]');

        if ($(this).attr('value') == 'subdomain') {
            $sub_inputs.show().prop('disabled', false);
            $new_inputs.prop('disabled', true).hide();
        } else {
            $sub_inputs.hide().prop('disabled', true);
            $new_inputs.prop('disabled', false).show();
        }
    });

    $('#dynamic-form').on('update', 'input[data-field="sub-with-domain"]', function (event) {
        var $form = $(this).closest('.form-instance');
        var subdomain = $form.find('input[data-field="subdomain"]').val();
        var domain = $form.find('input[data-field="dns_hdomain_id"]').select2('data');
        var value = '';

        if (domain && domain.text) {
            value = (subdomain.length > 0 ? (subdomain + '.') : '') + domain.text;
        }
        $(this).val(value).trigger('change');
    });

    $('#dynamic-form').on('change', 'input[data-field="subdomain"]', function () {
        var $form = $(this).closest('.form-instance');
        $form.find('input[data-field="sub-with-domain"]').trigger('update');
    });
JS
);