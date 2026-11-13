<?php

namespace app\commands;

use app\models\Usuario;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Crea usuarios desde la consola (sirve para el primer administrador).
 *
 *   php yii admin/crear
 *   php yii admin/crear ana "Ana Pérez" --rol=editor
 */
class AdminController extends Controller
{
    /** @var string admin o editor */
    public $rol = Usuario::ROL_ADMIN;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['rol']);
    }

    public function actionCrear(string $usuario = '', string $nombre = '')
    {
        $usuario = $usuario ?: Console::prompt('Usuario:', ['required' => true]);
        $nombre = $nombre ?: Console::prompt('Nombre:', ['required' => true]);
        $clave = Console::prompt('Contraseña (mínimo 8 caracteres):', ['required' => true]);
        $repetida = Console::prompt('Repetí la contraseña:', ['required' => true]);
        if ($clave !== $repetida) {
            $this->stderr("Las contraseñas no coinciden.\n", Console::FG_RED);

            return ExitCode::DATAERR;
        }

        $nuevo = new Usuario([
            'usuario' => $usuario,
            'nombre' => $nombre,
            'rol' => $this->rol,
            'activo' => true,
            'clave' => $clave,
        ]);
        if (!$nuevo->save()) {
            foreach ($nuevo->getFirstErrors() as $error) {
                $this->stderr("- $error\n", Console::FG_RED);
            }

            return ExitCode::DATAERR;
        }
        $this->stdout("Listo: se creó el usuario «{$nuevo->usuario}» ({$nuevo->rol}).\n", Console::FG_GREEN);

        return ExitCode::OK;
    }
}
