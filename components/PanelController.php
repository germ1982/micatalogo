<?php

namespace app\components;

use app\models\Usuario;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * Base de los controladores del panel: pide sesión iniciada y, salvo que
 * $soloAdmin sea false, rol de administrador. Las acciones de $post solo aceptan POST.
 */
class PanelController extends Controller
{
    /** @var bool false = también pueden entrar los editores */
    protected bool $soloAdmin = true;

    /** @var string[] acciones que modifican datos sin formulario propio */
    protected array $post = [];

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => fn() => !$this->soloAdmin || $this->usuario()->esAdmin(),
                ]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => array_fill_keys($this->post, ['POST']),
            ],
        ];
    }

    /**
     * Si un envío supera post_max_size, PHP descarta todos los datos (también el token CSRF)
     * y Yii mostraría "no se pudieron verificar los datos". Se avisa algo entendible.
     */
    public function beforeAction($action)
    {
        $pedido = Yii::$app->request;
        if ($pedido->isPost && empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            Yii::$app->session->setFlash('error', 'Las fotos pesan demasiado para subirlas juntas (el servidor acepta hasta '
                . ini_get('post_max_size') . 'B por vez). Probá con menos fotos por vez.');
            $this->redirect($pedido->referrer ?: ['/panel/publicacion/index']);

            return false;
        }

        return parent::beforeAction($action);
    }

    protected function usuario(): Usuario
    {
        return Yii::$app->user->identity;
    }

    /** Vuelve a la página anterior (si es de este sitio) o a $porDefecto */
    protected function volver(array|string $porDefecto)
    {
        $volver = (string)Yii::$app->request->post('volver', Yii::$app->request->get('volver', ''));
        if ($volver !== '' && str_starts_with($volver, '/') && !str_starts_with($volver, '//') && !str_contains($volver, '\\')) {
            return $this->redirect($volver);
        }

        return $this->redirect($porDefecto);
    }
}
