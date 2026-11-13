<?php

namespace app\models;

use app\components\Fotos;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * Párrafo de la página "Acerca de", con sus fotos a la derecha.
 *
 * @property int $id
 * @property string|null $titulo
 * @property string $texto
 * @property int $orden
 * @property AcercaFoto[] $fotos
 */
class AcercaParrafo extends ActiveRecord
{
    /** @var UploadedFile[] */
    public $fotosNuevas = [];

    public static function tableName()
    {
        return '{{%acerca_parrafo}}';
    }

    public function rules()
    {
        return [
            [['titulo', 'texto'], 'trim'],
            ['texto', 'required'],
            ['titulo', 'string', 'max' => 120],
            ['texto', 'string', 'max' => 5000],
            ['titulo', 'default', 'value' => null],
            ['orden', 'integer'],
            ['fotosNuevas', 'image', 'skipOnEmpty' => true, 'maxFiles' => 6,
                'extensions' => 'jpg, jpeg, png, webp', 'checkExtensionByMimeType' => true,
                'maxSize' => \Yii::$app->params['fotoMaximoMb'] * 1024 * 1024],
        ];
    }

    public function attributeLabels()
    {
        return [
            'titulo' => 'Título (opcional)',
            'texto' => 'Texto',
            'orden' => 'Orden',
            'fotosNuevas' => 'Agregar fotos',
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert && !$this->orden) {
            $this->orden = (int)static::find()->max('orden') + 1;
        }

        return parent::beforeSave($insert);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        Fotos::borrarCarpeta('acerca/' . $this->id);
    }

    public function getFotos()
    {
        return $this->hasMany(AcercaFoto::class, ['parrafo_id' => 'id'])->orderBy(['orden' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /** @return string[] errores */
    public function guardarFotos(): array
    {
        $errores = [];
        $orden = (int)AcercaFoto::find()->where(['parrafo_id' => $this->id])->max('orden');
        foreach ($this->fotosNuevas as $subida) {
            $r = Fotos::guardar($subida, 'acerca/' . $this->id);
            if (is_string($r)) {
                $errores[] = $r;
                continue;
            }
            (new AcercaFoto(['parrafo_id' => $this->id, 'archivo' => $r[0], 'miniatura' => $r[1], 'orden' => ++$orden]))->save(false);
        }

        return $errores;
    }
}
