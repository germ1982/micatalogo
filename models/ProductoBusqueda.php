<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/** Filtros de la grilla de publicaciones del panel */
class ProductoBusqueda extends Producto
{
    public function rules()
    {
        return [
            [['id', 'categoria_id'], 'integer'],
            ['disponible', 'boolean'],
            [['titulo', 'talle', 'color', 'material', 'precio', 'creado_en'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function buscar(array $params): ActiveDataProvider
    {
        $q = Producto::find()->with(['categoria', 'fotos']);
        $dp = new ActiveDataProvider([
            'query' => $q,
            'sort' => ['defaultOrder' => ['creado_en' => SORT_DESC]],
            'pagination' => ['pageSize' => 20],
        ]);
        if (!($this->load($params) && $this->validate())) {
            return $dp;
        }
        $q->andFilterWhere(['id' => $this->id, 'categoria_id' => $this->categoria_id, 'disponible' => $this->disponible])
            ->andFilterWhere(['like', 'titulo', $this->titulo])
            ->andFilterWhere(['like', 'talle', $this->talle])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'material', $this->material]);
        if ($this->precio !== null && $this->precio !== '') {
            $q->andWhere(['precio' => Producto::normalizarPrecio($this->precio)]);
        }
        // Fecha como dd/mm/aaaa
        if ($this->creado_en && preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', trim($this->creado_en), $m)) {
            $q->andWhere(['DATE(creado_en)' => sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1])]);
        }

        return $dp;
    }
}
