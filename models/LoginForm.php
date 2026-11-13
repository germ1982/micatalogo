<?php

namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $usuario;
    public $clave;
    public $recordarme = true;

    private $_usuario = false;

    public function rules()
    {
        return [
            [['usuario', 'clave'], 'required'],
            ['usuario', 'trim'],
            ['recordarme', 'boolean'],
            ['clave', 'validarClave'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'usuario' => 'Usuario',
            'clave' => 'Contraseña',
            'recordarme' => 'Recordarme en este dispositivo',
        ];
    }

    public function validarClave($atributo)
    {
        if ($this->hasErrors()) {
            return;
        }
        $usuario = $this->getUsuario();
        if (!$usuario || !$usuario->validarClave($this->clave)) {
            $this->addError($atributo, 'Usuario o contraseña incorrectos.');
        }
    }

    public function ingresar(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $usuario = $this->getUsuario();
        $usuario->updateAttributes(['ultimo_ingreso' => date('Y-m-d H:i:s')]);

        return Yii::$app->user->login($usuario, $this->recordarme ? 3600 * 24 * 30 : 0);
    }

    private function getUsuario(): ?Usuario
    {
        if ($this->_usuario === false) {
            $this->_usuario = Usuario::findOne(['usuario' => $this->usuario, 'activo' => 1]);
        }

        return $this->_usuario;
    }
}
