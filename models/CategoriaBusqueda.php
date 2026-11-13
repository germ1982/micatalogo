<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/** Filtros de la grilla de categorías del panel */
class CategoriaBusqueda extends Categoria
{
    public function rules()
    {
        return [['nombre', 'safe']];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function buscar(array $params): ActiveDataProvider
    {
        $q = Categoria::find()->alias('c')
            ->select(['c.*', 'cantidad' => '(SELECT COUNT(*) FROM {{%producto}} p WHERE p.categoria_id = c.id)']);
        $dp = new ActiveDataProvider([
            'query' => $q,
            'sort' => ['defaultOrder' => ['nombre' => SORT_ASC], 'attributes' => ['nombre', 'cantidad']],
            'pagination' => ['pageSize' => 50],
        ]);
        if ($this->load($params) && $this->validate()) {
            $q->andFilterWhere(['like', 'c.nombre', $this->nombre]);
        }

        return $dp;
    }
}
