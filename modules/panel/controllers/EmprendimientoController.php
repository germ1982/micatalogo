<?php

namespace app\modules\panel\controllers;

use app\components\Dolar;
use app\components\PanelController;
use app\models\Emprendimiento;
use Yii;
use yii\helpers\Html;
use yii\web\UploadedFile;

/** Datos del emprendimiento (nombre, banner, contacto) y estilo del sitio */
class EmprendimientoController extends PanelController
{
    protected array $post = ['quitar-banner', 'tema', 'actualizar-dolar'];

    public function actionIndex()
    {
        $datos = Emprendimiento::actual();
        if ($datos->load(Yii::$app->request->post())) {
            $datos->bannerNuevo = UploadedFile::getInstance($datos, 'bannerNuevo');
            $datos->bannerMovilNuevo = UploadedFile::getInstance($datos, 'bannerMovilNuevo');
            $cambioDolar = $datos->isAttributeChanged('dolar_tipo');
            if ($datos->save()) {
                if ($cambioDolar) {
                    Dolar::actualizar($datos);
                }
                $errores = $datos->guardarBanners();
                Yii::$app->session->setFlash($errores ? 'warning' : 'success', $errores
                    ? 'Los datos se guardaron, pero el banner no: ' . implode(' ', array_map([Html::class, 'encode'], $errores))
                    : 'Datos guardados.');

                return $this->refresh();
            }
        }

        return $this->render('index', ['datos' => $datos]);
    }

    public function actionQuitarBanner(string $cual = 'banner')
    {
        $movil = $cual === 'banner_movil';
        Emprendimiento::actual()->quitarBanner($movil ? 'banner_movil' : 'banner');
        Yii::$app->session->setFlash('success', $movil ? 'Se quitó el banner para celular.' : 'Se quitó la imagen del banner.');

        return $this->redirect(['index']);
    }

    public function actionActualizarDolar()
    {
        if (Dolar::actualizar()) {
            $sitio = Emprendimiento::actual();
            Yii::$app->session->setFlash('success', 'Cotización actualizada: '
                . Html::encode(Dolar::TIPOS[$sitio->dolar_tipo]) . ' a $ ' . number_format((float)$sitio->dolar_valor, 2, ',', '.') . '.');
        } else {
            Yii::$app->session->setFlash('error', 'No se pudo consultar la cotización en este momento. Se sigue usando la última guardada.');
        }

        return $this->redirect(['index', '#' => 'dolar']);
    }

    public function actionEstilo()
    {
        return $this->render('estilo', ['actual' => Emprendimiento::actual()->tema]);
    }

    public function actionTema()
    {
        $datos = Emprendimiento::actual();
        $tema = (string)Yii::$app->request->post('tema');
        if (isset(Yii::$app->params['temas'][$tema])) {
            $datos->updateAttributes(['tema' => $tema]);
            Yii::$app->session->setFlash('success', 'Ahora el sitio usa el estilo <b>' . Html::encode(Yii::$app->params['temas'][$tema]['nombre']) . '</b> para todos los visitantes.');
        }

        return $this->redirect(['estilo']);
    }
}
