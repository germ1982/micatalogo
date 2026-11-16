<?php

namespace app\controllers;

use app\components\Migraciones;
use app\models\Usuario;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Mantenimiento para hostings sin consola (InfinityFree), protegido con la clave
 * MANTENIMIENTO_CLAVE del .env de cada sitio (la genera deploy/publicar.php):
 *
 *   /mantenimiento?clave=...           ver y aplicar las actualizaciones de la base
 *   /mantenimiento/migrar              lo mismo, en JSON, para el script de publicar
 *   /mantenimiento/instalar?clave=...  crear el primer administrador (solo si no hay ninguno)
 *
 * Usa su propio diseño mínimo: antes de migrar todavía no existen las tablas del sitio.
 */
class MantenimientoController extends Controller
{
    public $layout = 'mantenimiento';

    public function behaviors()
    {
        return [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['migrar' => ['POST']]],
        ];
    }

    public function beforeAction($action)
    {
        // El script de publicar llama a "migrar" sin sesión: lo protege la clave, no el CSRF
        if ($action->id === 'migrar') {
            $this->enableCsrfValidation = false;
        }
        if (!parent::beforeAction($action)) {
            return false;
        }
        $clave = (string)(Yii::$app->request->post('clave') ?? Yii::$app->request->get('clave', ''));
        $env = require Yii::getAlias('@app/config/env.php');
        $correcta = (string)($env['MANTENIMIENTO_CLAVE'] ?? '');
        if ($correcta === '' || !hash_equals($correcta, $clave)) {
            throw new NotFoundHttpException('Página no encontrada.');
        }

        return true;
    }

    public function actionIndex(string $clave)
    {
        $resultado = null;
        if (Yii::$app->request->isPost) {
            $resultado = Migraciones::aplicar();
        }

        return $this->render('index', [
            'clave' => $clave,
            'resultado' => $resultado,
            'pendientes' => Migraciones::pendientes(),
            'hayUsuarios' => $this->hayUsuarios(),
        ]);
    }

    public function actionMigrar()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $resultado = Migraciones::aplicar();

        return $resultado + ['ok' => $resultado['error'] === null, 'sinUsuarios' => !$this->hayUsuarios()];
    }

    public function actionInstalar(string $clave)
    {
        if (Migraciones::pendientes()) {
            return $this->redirect(['index', 'clave' => $clave]);
        }
        if ($this->hayUsuarios()) {
            throw new NotFoundHttpException('El sitio ya tiene administrador: entrá desde Ingresar.');
        }
        $usuario = new Usuario(['rol' => Usuario::ROL_ADMIN, 'activo' => true]);
        if ($usuario->load(Yii::$app->request->post())) {
            $usuario->rol = Usuario::ROL_ADMIN;
            $usuario->activo = true;
            if ($usuario->clave !== Yii::$app->request->post('repetir')) {
                $usuario->addError('clave', 'Las contraseñas no coinciden.');
            } elseif ($usuario->save()) {
                Yii::$app->user->login($usuario, 3600 * 24 * 30);
                Yii::$app->session->setFlash('success', '¡Listo! Ya sos administrador. Ahora cargá los datos del emprendimiento.');

                return $this->redirect(['/panel/emprendimiento/index']);
            }
        }
        $usuario->clave = '';

        return $this->render('instalar', ['usuario' => $usuario, 'clave' => $clave]);
    }

    private function hayUsuarios(): bool
    {
        $db = Yii::$app->db;
        if ($db->schema->getTableSchema('{{%usuario}}', true) === null) {
            return false;
        }

        return (bool)Usuario::find()->limit(1)->count();
    }
}
