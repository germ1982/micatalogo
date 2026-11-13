<?php

namespace app\modules\panel\controllers;

use app\components\PanelController;
use app\models\Usuario;
use app\models\UsuarioBusqueda;
use Yii;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;

class UsuarioController extends PanelController
{
    protected array $post = ['borrar'];

    public function actionIndex()
    {
        $busqueda = new UsuarioBusqueda();

        return $this->render('index', [
            'busqueda' => $busqueda,
            'proveedor' => $busqueda->buscar(Yii::$app->request->queryParams),
        ]);
    }

    public function actionCrear()
    {
        return $this->guardar(new Usuario(['rol' => Usuario::ROL_EDITOR, 'activo' => true]));
    }

    public function actionEditar(int $id)
    {
        return $this->guardar($this->buscar($id));
    }

    private function guardar(Usuario $usuario)
    {
        if ($usuario->load(Yii::$app->request->post()) && $usuario->validate()) {
            $esUnoMismo = $usuario->id === $this->usuario()->id;
            if ($esUnoMismo && (!$usuario->activo || !$usuario->esAdmin())) {
                $usuario->addError('rol', 'No podés quitarte el rol de administrador ni desactivarte a vos mismo.');
            } elseif ($this->quedariaSinAdmin($usuario)) {
                $usuario->addError('rol', 'Tiene que quedar al menos un administrador activo.');
            } else {
                $usuario->save(false);
                Yii::$app->session->setFlash('success', 'Usuario guardado.');

                return $this->redirect(['index']);
            }
        }
        $usuario->clave = '';

        return $this->render('formulario', ['usuario' => $usuario]);
    }

    public function actionBorrar(int $id)
    {
        $usuario = $this->buscar($id);
        if ($usuario->id === $this->usuario()->id) {
            Yii::$app->session->setFlash('error', 'No podés borrar tu propio usuario.');
        } elseif ($usuario->esAdmin() && $usuario->activo && Usuario::adminsActivos() <= 1) {
            Yii::$app->session->setFlash('error', 'Es el único administrador activo: no se puede borrar.');
        } else {
            $usuario->delete();
            Yii::$app->session->setFlash('success', 'Se borró el usuario «' . Html::encode($usuario->usuario) . '».');
        }

        return $this->redirect(['index']);
    }

    /** ¿El cambio deja al sitio sin ningún administrador activo? */
    private function quedariaSinAdmin(Usuario $usuario): bool
    {
        if ($usuario->isNewRecord) {
            return false;
        }
        $eraAdminActivo = $usuario->getOldAttribute('rol') === Usuario::ROL_ADMIN && $usuario->getOldAttribute('activo');
        $siguesiendo = $usuario->esAdmin() && $usuario->activo;

        return $eraAdminActivo && !$siguesiendo && Usuario::adminsActivos() <= 1;
    }

    private function buscar(int $id): Usuario
    {
        return Usuario::findOne($id) ?? throw new NotFoundHttpException('El usuario no existe.');
    }
}
