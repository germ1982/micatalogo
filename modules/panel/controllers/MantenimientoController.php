<?php

namespace app\modules\panel\controllers;

use app\components\Migraciones;
use app\components\PanelController;
use Yii;
use yii\helpers\Html;

/** Botón del panel para aplicar las actualizaciones pendientes de la base (solo administradores) */
class MantenimientoController extends PanelController
{
    protected array $post = ['aplicar'];

    public function actionAplicar()
    {
        $r = Migraciones::aplicar();
        if ($r['error']) {
            Yii::$app->session->setFlash('error', Html::encode($r['error']));
        } else {
            Yii::$app->session->setFlash('success', $r['aplicadas']
                ? 'Base de datos actualizada (' . count($r['aplicadas']) . ' cambios).'
                : 'La base de datos ya estaba al día.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['/panel/publicacion/index']);
    }
}
