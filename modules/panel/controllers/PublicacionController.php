<?php

namespace app\modules\panel\controllers;

use app\components\PanelController;
use app\models\Producto;
use app\models\ProductoBusqueda;
use app\models\ProductoFoto;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/** ABM de publicaciones: lo usan administradores y editores */
class PublicacionController extends PanelController
{
    protected bool $soloAdmin = false;
    protected array $post = ['borrar', 'disponible', 'borrar-foto', 'principal-foto'];

    public function actionIndex()
    {
        $busqueda = new ProductoBusqueda();

        return $this->render('index', [
            'busqueda' => $busqueda,
            'proveedor' => $busqueda->buscar(Yii::$app->request->queryParams),
        ]);
    }

    public function actionCrear()
    {
        $producto = new Producto(['disponible' => true]);

        return $this->guardar($producto, 'crear');
    }

    public function actionEditar(int $id)
    {
        return $this->guardar($this->buscar($id), 'editar');
    }

    private function guardar(Producto $producto, string $vista)
    {
        if ($producto->load(Yii::$app->request->post())) {
            $producto->fotosNuevas = UploadedFile::getInstances($producto, 'fotosNuevas');
            if ($producto->save()) {
                $errores = $producto->guardarFotos();
                if ($errores) {
                    Yii::$app->session->setFlash('warning', 'La publicación se guardó, pero:<br>' . implode('<br>', array_map('yii\helpers\Html::encode', $errores)));
                } else {
                    Yii::$app->session->setFlash('success', 'Publicación guardada.');
                }
                if (Yii::$app->request->post('seguir')) {
                    return $this->redirect(['editar', 'id' => $producto->id, 'volver' => Yii::$app->request->post('volver')]);
                }

                return $this->volver(['index']);
            }
        }

        return $this->render($vista, ['producto' => $producto]);
    }

    public function actionBorrar(int $id)
    {
        $producto = $this->buscar($id);
        $producto->delete();
        Yii::$app->session->setFlash('success', 'Se borró la publicación «' . \yii\helpers\Html::encode($producto->titulo) . '».');

        return $this->volver(['index']);
    }

    /** Habilita o deshabilita la publicación (botón de la tarjeta del catálogo y de la grilla) */
    public function actionDisponible(int $id)
    {
        $producto = $this->buscar($id);
        $producto->disponible = !$producto->disponible;
        $producto->save(false, ['disponible', 'actualizado_en']);
        Yii::$app->session->setFlash('success', $producto->disponible
            ? 'La publicación ahora está <b>disponible</b> para el público.'
            : 'La publicación quedó <b>no disponible</b>: el público ya no la ve.');

        return $this->volver(['index']);
    }

    public function actionBorrarFoto(int $id)
    {
        $foto = ProductoFoto::findOne($id) ?? throw new NotFoundHttpException('La foto no existe.');
        $foto->delete();
        Yii::$app->session->setFlash('success', 'Foto borrada.');

        return $this->redirect(['editar', 'id' => $foto->producto_id, 'volver' => Yii::$app->request->post('volver')]);
    }

    /** Pone la foto primera: es la que se ve en la tarjeta y al compartir */
    public function actionPrincipalFoto(int $id)
    {
        $foto = ProductoFoto::findOne($id) ?? throw new NotFoundHttpException('La foto no existe.');
        $minimo = (int)ProductoFoto::find()->where(['producto_id' => $foto->producto_id])->min('orden');
        $foto->updateAttributes(['orden' => $minimo - 1]);
        Yii::$app->session->setFlash('success', 'Foto principal cambiada.');

        return $this->redirect(['editar', 'id' => $foto->producto_id, 'volver' => Yii::$app->request->post('volver')]);
    }

    private function buscar(int $id): Producto
    {
        return Producto::findOne($id) ?? throw new NotFoundHttpException('La publicación no existe.');
    }
}
