<?php

namespace app\models;

use app\components\Fotos;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $producto_id
 * @property string $archivo
 * @property string $miniatura
 * @property int $orden
 */
class ProductoFoto extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%producto_foto}}';
    }

    public function afterDelete()
    {
        parent::afterDelete();
        Fotos::borrar($this->archivo);
        Fotos::borrar($this->miniatura);
    }
}
