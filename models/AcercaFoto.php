<?php

namespace app\models;

use app\components\Fotos;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $parrafo_id
 * @property string $archivo
 * @property string $miniatura
 * @property int $orden
 */
class AcercaFoto extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%acerca_foto}}';
    }

    public function afterDelete()
    {
        parent::afterDelete();
        Fotos::borrar($this->archivo);
        Fotos::borrar($this->miniatura);
    }
}
