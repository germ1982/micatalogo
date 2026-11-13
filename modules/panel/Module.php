<?php

namespace app\modules\panel;

/**
 * Panel de control: solo para usuarios logueados.
 * Los editores manejan las publicaciones; el resto es para administradores.
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\panel\controllers';
    public $layout = '@app/views/layouts/panel';
}
