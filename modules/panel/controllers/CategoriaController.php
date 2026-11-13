<?php

namespace app\modules\panel\controllers;

use app\components\PanelController;
use app\models\Categoria;
use app\models\CategoriaBusqueda;
use Yii;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;

class CategoriaController extends PanelController
{
    protected array $post = ['borrar'];

    public function actionIndex()
    {
        $busqueda = new CategoriaBusqueda();

        return $this->render('index', [
            'busqueda' => $busqueda,
            'proveedor' => $busqueda->buscar(Yii::$app->request->queryParams),
        ]);
    }

    public function actionCrear()
    {
        return $this->guardar(new Categoria());
    }

    public function actionEditar(int $id)
    {
        return $this->guardar($this->buscar($id));
    }

    private function guardar(Categoria $categoria)
    {
        if ($categoria->load(Yii::$app->request->post()) && $categoria->save()) {
            Yii::$app->session->setFlash('success', 'Categoría guardada.');

            return $this->redirect(['index']);
        }

        return $this->render('formulario', ['categoria' => $categoria]);
    }

    public function actionBorrar(int $id)
    {
        $categoria = $this->buscar($id);
        $cantidad = (int)$categoria->getProductos()->count();
        $categoria->delete();
        Yii::$app->session->setFlash('success', 'Se borró la categoría «' . Html::encode($categoria->nombre) . '».'
            . match ($cantidad) {
                0 => '',
                1 => ' Su publicación quedó sin categoría.',
                default => " Sus $cantidad publicaciones quedaron sin categoría.",
            });

        return $this->redirect(['index']);
    }

    private function buscar(int $id): Categoria
    {
        return Categoria::findOne($id) ?? throw new NotFoundHttpException('La categoría no existe.');
    }
}
