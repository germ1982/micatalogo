<?php

namespace app\modules\panel\controllers;

use app\components\PanelController;
use app\models\AcercaFoto;
use app\models\AcercaParrafo;
use Yii;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/** Párrafos (con fotos) de la página "Acerca de" */
class AcercaController extends PanelController
{
    protected array $post = ['borrar', 'borrar-foto', 'mover'];

    public function actionIndex()
    {
        return $this->render('index', [
            'parrafos' => AcercaParrafo::find()->with('fotos')->orderBy(['orden' => SORT_ASC, 'id' => SORT_ASC])->all(),
        ]);
    }

    public function actionCrear()
    {
        return $this->guardar(new AcercaParrafo());
    }

    public function actionEditar(int $id)
    {
        return $this->guardar($this->buscar($id));
    }

    private function guardar(AcercaParrafo $parrafo)
    {
        if ($parrafo->load(Yii::$app->request->post())) {
            $parrafo->fotosNuevas = UploadedFile::getInstances($parrafo, 'fotosNuevas');
            if ($parrafo->save()) {
                $errores = $parrafo->guardarFotos();
                Yii::$app->session->setFlash($errores ? 'warning' : 'success', $errores
                    ? 'El párrafo se guardó, pero:<br>' . implode('<br>', array_map([Html::class, 'encode'], $errores))
                    : 'Párrafo guardado.');

                return $this->redirect(['index']);
            }
        }

        return $this->render('formulario', ['parrafo' => $parrafo]);
    }

    public function actionBorrar(int $id)
    {
        $this->buscar($id)->delete();
        Yii::$app->session->setFlash('success', 'Párrafo borrado.');

        return $this->redirect(['index']);
    }

    public function actionBorrarFoto(int $id)
    {
        $foto = AcercaFoto::findOne($id) ?? throw new NotFoundHttpException('La foto no existe.');
        $foto->delete();
        Yii::$app->session->setFlash('success', 'Foto borrada.');

        return $this->redirect(['editar', 'id' => $foto->parrafo_id]);
    }

    /** Sube o baja un párrafo intercambiando su orden con el vecino */
    public function actionMover(int $id, string $hacia)
    {
        $parrafos = AcercaParrafo::find()->orderBy(['orden' => SORT_ASC, 'id' => SORT_ASC])->all();
        foreach ($parrafos as $i => $p) {
            $p->orden = $i + 1;
        }
        foreach ($parrafos as $i => $p) {
            $j = $hacia === 'arriba' ? $i - 1 : $i + 1;
            if ($p->id === $id && isset($parrafos[$j])) {
                [$p->orden, $parrafos[$j]->orden] = [$parrafos[$j]->orden, $p->orden];
                break;
            }
        }
        foreach ($parrafos as $p) {
            $p->save(false, ['orden']);
        }

        return $this->redirect(['index']);
    }

    private function buscar(int $id): AcercaParrafo
    {
        return AcercaParrafo::findOne($id) ?? throw new NotFoundHttpException('El párrafo no existe.');
    }
}
