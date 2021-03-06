<?php

namespace DevGroup\AdminUtils\columns;

use DevGroup\AdminUtils\AdminModule;
use DevGroup\AdminUtils\Helper;
use kartik\icons\Icon;
use yii\grid\Column;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class ActionColumn is an advanced ActionColumn for modern bootstrap admin grids
 *
 * @package DevGroup\AdminUtils\columns
 */
class ActionColumn extends Column
{
    public $buttons = [];

    private $defaultButtons = [];

    public $appendReturnUrl = true;

    public $buttonSizeClass = 'btn-sm';

    /**
     * @var array Params to append to every button's URL if button doesn't redefine it.
     */
    public $appendUrlParams = [];

    public function init()
    {
        parent::init();
        $this->defaultButtons = [
            'edit' => [
                'url' => 'edit',
                'icon' => 'pencil',
                'class' => 'btn-primary',
                'label' => AdminModule::t('app', 'Edit'),
            ],
            'delete' => [
                'url' => 'delete',
                'icon' => 'trash-o',
                'class' => 'btn-danger',
                'label' => AdminModule::t('app', 'Delete'),
                'options' => [
                    'data-action' => 'delete'
                ],
            ],
        ];
        if (empty($this->buttons) === true) {
            $this->buttons = $this->defaultButtons;
        }
        $this->grid->view->registerAssetBundle('kartik\icons\FontAwesomeAsset');
    }

    /**
     * Creates a URL for the given action and model.
     * This method is called for each button and each row.
     *
     * @param string $action the button name (or action ID)
     * @param \yii\db\ActiveRecord $model the data model
     * @param mixed $key the key associated with the data model
     * @param bool $appendReturnUrl custom return url for each button
     * @param array $urlAppend custom append url for each button
     * @param string $keyParam custom param if $key is string
     * @param array $attrs list of model attributes used in route params
     *
     * @return string the created URL
     */
    public function createUrl(
        $action,
        $model,
        $key,
        $appendReturnUrl,
        $urlAppend,
        $keyParam = 'id',
        $attrs = []
    ) {
        $params = [];
        if (is_array($key)) {
            $params = $key;
        } else {
            if (is_null($keyParam) === false) {
                $params = [$keyParam => (string)$key];
            }
        }
        $params[0] = $action;
        foreach ($attrs as $attrName) {
            if ($attrName === 'model') {
                $params['model'] = $model;
            } else {
                $params[$attrName] = $model->getAttribute($attrName);
            }
        }
        $params = ArrayHelper::merge($params, $urlAppend);

        if ($appendReturnUrl) {
            $params['returnUrl'] = Helper::returnUrl();
        }
        return Url::toRoute($params);

    }

    /**
     * Renders cell content(buttons)
     *
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     *
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $data = Html::beginTag('div', ['class' => 'btn-group']);
        if ($this->buttons instanceof \Closure) {
            $buttons = call_user_func($this->buttons, $model, $key, $index, $this);
        } else {
            $buttons = $this->buttons;
        }
        foreach ($buttons as $buttonName => $button) {
            if ($buttonName === 'delete' &&
                ArrayHelper::getValue($button, 'options.data-action') === 'delete'
            ) {
                $button = ArrayHelper::merge(
                    [
                        'options' => [
                            'data-title' => AdminModule::t('app', 'Delete'),
                            'data-close' => AdminModule::t('app', 'Close'),
                            'data-text' => AdminModule::t('app', 'Are you sure you want to delete this item?')
                        ]
                    ],
                    $button
                );
            }
            $appendReturnUrl = ArrayHelper::getValue($button, 'appendReturnUrl', $this->appendReturnUrl);
            $urlAppend = ArrayHelper::getValue($button, 'urlAppend', $this->appendUrlParams);
            $keyParam = ArrayHelper::getValue($button, 'keyParam', 'id');
            $attrs = ArrayHelper::getValue($button, 'attrs', []);
            Html::addCssClass($button, 'btn');
            Html::addCssClass($button, $this->buttonSizeClass);
            $buttonText = isset($button['text']) ? ' ' . $button['text'] : '';
            $icon = empty($button['icon']) ? '' : Icon::show($button['icon'], [], 'fa');
            if (!empty($icon) && !empty($buttonText)) {
                $buttonText = '&nbsp;' . $buttonText;
            }
            $data .= Html::a(
                $icon . $buttonText,
                $this->createUrl(
                    $button['url'],
                    $model,
                    $key,
                    $appendReturnUrl,
                    $urlAppend,
                    $keyParam,
                    $attrs
                ),
                ArrayHelper::merge(
                    isset($button['options']) ? $button['options'] : [],
                    [
                        'class' => $button['class'],
                        'title' => $button['label'],
                    ]
                )
            ) . ' ';
        }
        $data .= '</div>';
        return $data;
    }
}
