<?php

namespace app\controllers;

use app\models\AcercaParrafo;
use app\models\Categoria;
use app\models\LoginForm;
use app\models\Producto;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['salir'],
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['salir' => ['POST']],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => ['class' => 'yii\web\ErrorAction'],
        ];
    }

    /** Portada: banner, buscador, categorías, orden y la grilla de productos */
    public function actionIndex(string $q = '', $categoria = null, string $orden = 'recientes')
    {
        $logueado = !Yii::$app->user->isGuest;
        $categoria = ctype_digit((string)$categoria) ? (int)$categoria : null;
        if (!isset(Producto::ORDENES[$orden])) {
            $orden = 'recientes';
        }
        $q = mb_substr(trim($q), 0, 100);

        return $this->render('index', [
            'productos' => Producto::catalogo($q, $categoria, $orden, $logueado),
            'categorias' => Categoria::listaConProductos($logueado),
            'q' => $q,
            'categoria' => $categoria,
            'orden' => $orden,
        ]);
    }

    public function actionProducto(int $id, ?string $slug = null)
    {
        $producto = Producto::find()->with(['fotos', 'categoria'])->where(['id' => $id])->one();
        if (!$producto || (!$producto->disponible && Yii::$app->user->isGuest)) {
            throw new NotFoundHttpException('Esta publicación no existe o ya no está disponible.');
        }
        // Un solo link por producto: si el título cambió, se redirige al link nuevo
        if ($slug !== $producto->slug) {
            return $this->redirect($producto->url, 301);
        }

        return $this->render('producto', ['producto' => $producto]);
    }

    public function actionAcerca()
    {
        return $this->render('acerca', [
            'parrafos' => AcercaParrafo::find()->with('fotos')->orderBy(['orden' => SORT_ASC, 'id' => SORT_ASC])->all(),
        ]);
    }

    public function actionContacto()
    {
        return $this->render('contacto');
    }

    public function actionIngresar()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $modelo = new LoginForm();
        if ($modelo->load(Yii::$app->request->post()) && $modelo->ingresar()) {
            return $this->goBack();
        }
        $modelo->clave = '';

        return $this->render('ingresar', ['modelo' => $modelo]);
    }

    public function actionSalir()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
