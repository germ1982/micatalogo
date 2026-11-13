<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property int $id
 * @property string $nombre
 * @property Producto[] $productos
 */
class Categoria extends ActiveRecord
{
    /** Cantidad de publicaciones (la calcula la grilla del panel) */
    public $cantidad;

    public static function tableName()
    {
        return '{{%categoria}}';
    }

    public function rules()
    {
        return [
            ['nombre', 'trim'],
            ['nombre', 'required'],
            ['nombre', 'string', 'max' => 60],
            ['nombre', 'unique', 'message' => 'Ya hay una categoría con ese nombre.'],
        ];
    }

    public function attributeLabels()
    {
        return ['nombre' => 'Nombre'];
    }

    public function getProductos()
    {
        return $this->hasMany(Producto::class, ['categoria_id' => 'id']);
    }

    /** [id => nombre] ordenadas alfabéticamente, para los desplegables */
    public static function lista(): array
    {
        return ArrayHelper::map(static::find()->orderBy('nombre')->all(), 'id', 'nombre');
    }

    /** Categorías con al menos un producto visible para el público (o todos, si es admin) */
    public static function listaConProductos(bool $todos): array
    {
        $q = static::find()->alias('c')->innerJoinWith('productos p', false)
            ->select(['c.id', 'c.nombre'])->distinct()->orderBy('c.nombre');
        if (!$todos) {
            $q->andWhere(['p.disponible' => 1]);
        }

        return ArrayHelper::map($q->asArray()->all(), 'id', 'nombre');
    }
}
