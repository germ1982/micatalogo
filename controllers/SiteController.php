<?php

namespace app\controllers;

use app\models\AcercaParrafo;
use app\models\Categoria;
use app\models\Emprendimiento;
use app\models\LoginForm;
use app\models\Producto;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\StringHelper;
use yii\helpers\Url;
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

        // Habilitados / no habilitados (solo con sesión iniciada). Las casillas destildadas no se
        // envían, así que "estado=1" marca que el filtro se usó: sin él se muestran los dos.
        $ver = ['si' => true, 'no' => true];
        if ($logueado && Yii::$app->request->get('estado') !== null) {
            $elegidos = (array)Yii::$app->request->get('ver', []);
            $ver = ['si' => in_array('si', $elegidos, true), 'no' => in_array('no', $elegidos, true)];
        }
        $estados = $logueado ? array_keys(array_filter([1 => $ver['si'], 0 => $ver['no']])) : [1];

        return $this->render('index', [
            'productos' => Producto::catalogo($q, $categoria, $orden, $estados),
            'categorias' => Categoria::listaConProductos($logueado),
            'q' => $q,
            'categoria' => $categoria,
            'orden' => $orden,
            'ver' => $ver,
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

    /**
     * Tarjeta para compartir (vista previa de WhatsApp, Facebook...): solo las etiquetas
     * Open Graph y una redirección inmediata a la ficha o al catálogo.
     *
     * InfinityFree frena a los programas que no ejecutan JavaScript (los robots de las
     * vistas previas) en todas las páginas, salvo en las direcciones que terminan como un
     * archivo de imagen. Por eso esta dirección termina en .jpg, aunque devuelve HTML.
     */
    public function actionTarjeta(?int $id = null)
    {
        $sitio = Emprendimiento::actual();
        $datos = [
            'titulo' => $sitio->nombre,
            'descripcion' => $sitio->descripcion ?: ($sitio->eslogan ?: 'Catálogo online de ' . $sitio->nombre),
            'imagen' => $sitio->banner ?: $sitio->banner_movil,
            'destino' => Url::home(true),
            'tipo' => 'website',
        ];
        if ($id !== null) {
            $producto = Producto::find()->with('fotos')->where(['id' => $id])->one();
            if (!$producto || (!$producto->disponible && Yii::$app->user->isGuest)) {
                throw new NotFoundHttpException('Esta publicación no existe o ya no está disponible.');
            }
            $resumen = StringHelper::truncate(preg_replace('/\s+/u', ' ', (string)$producto->descripcion), 180);
            $datos = [
                'titulo' => $producto->titulo . ' · ' . $producto->precioTexto,
                'descripcion' => $resumen ?: 'Mirá esta publicación en el catálogo de ' . $sitio->nombre,
                'imagen' => $producto->fotos ? $producto->fotos[0]->archivo : $datos['imagen'],
                'destino' => $producto->getUrl(true),
                'tipo' => 'product',
            ];
        }
        Yii::$app->response->headers->set('X-Robots-Tag', 'noindex');

        return $this->renderPartial('tarjeta', $datos + ['sitio' => $sitio]);
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
