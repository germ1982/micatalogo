<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Usuario del panel. Roles: admin (todo) y editor (solo publicaciones).
 *
 * @property int $id
 * @property string $usuario
 * @property string $nombre
 * @property string $password_hash
 * @property string $auth_key
 * @property string $rol
 * @property bool $activo
 * @property string $creado_en
 * @property string|null $ultimo_ingreso
 */
class Usuario extends ActiveRecord implements IdentityInterface
{
    public const ROL_ADMIN = 'admin';
    public const ROL_EDITOR = 'editor';

    public const ROLES = [
        self::ROL_ADMIN => 'Administrador',
        self::ROL_EDITOR => 'Editor (solo publicaciones)',
    ];

    /** Contraseña nueva escrita en el formulario (no se guarda tal cual) */
    public $clave;

    public static function tableName()
    {
        return '{{%usuario}}';
    }

    public function rules()
    {
        return [
            [['usuario', 'nombre', 'rol'], 'required'],
            [['usuario', 'nombre'], 'trim'],
            // Sirve un nombre corto (ana) o un email (ana@gmail.com)
            ['usuario', 'match', 'pattern' => '/^[a-z0-9._@+\-]{3,40}$/i',
                'message' => 'Usá de 3 a 40 caracteres sin espacios: letras, números, punto, guion o @ (sirve tu email).'],
            ['usuario', 'unique', 'message' => 'Ese nombre de usuario ya existe.'],
            ['nombre', 'string', 'max' => 80],
            ['rol', 'in', 'range' => array_keys(self::ROLES)],
            ['activo', 'boolean'],
            ['clave', 'required', 'when' => fn() => $this->isNewRecord,
                'whenClient' => 'function () { return ' . ($this->isNewRecord ? 'true' : 'false') . '; }'],
            ['clave', 'string', 'min' => 8, 'max' => 72, 'tooShort' => 'La contraseña tiene que tener al menos 8 caracteres.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'usuario' => 'Usuario',
            'nombre' => 'Nombre',
            'rol' => 'Rol',
            'activo' => 'Activo',
            'clave' => $this->isNewRecord ? 'Contraseña' : 'Nueva contraseña (dejar vacío para no cambiarla)',
            'creado_en' => 'Creado',
            'ultimo_ingreso' => 'Último ingreso',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->auth_key = Yii::$app->security->generateRandomString(32);
            $this->creado_en = date('Y-m-d H:i:s');
        }
        if ($this->clave !== null && $this->clave !== '') {
            $this->password_hash = Yii::$app->security->generatePasswordHash($this->clave);
            if (!$insert) {
                // Cambiar la contraseña cierra los "recordarme" de otros dispositivos
                $this->auth_key = Yii::$app->security->generateRandomString(32);
            }
        }

        return true;
    }

    public function esAdmin(): bool
    {
        return $this->rol === self::ROL_ADMIN;
    }

    public function validarClave(string $clave): bool
    {
        return Yii::$app->security->validatePassword($clave, $this->password_hash);
    }

    /** Cuántos administradores activos quedan (para no dejar el sitio sin ninguno) */
    public static function adminsActivos(): int
    {
        return (int)static::find()->where(['rol' => self::ROL_ADMIN, 'activo' => 1])->count();
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'activo' => 1]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return hash_equals((string)$this->auth_key, (string)$authKey);
    }
}
