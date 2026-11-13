<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/** Filtros de la grilla de usuarios del panel */
class UsuarioBusqueda extends Usuario
{
    public function rules()
    {
        return [
            ['activo', 'boolean'],
            [['usuario', 'nombre', 'rol'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function buscar(array $params): ActiveDataProvider
    {
        $q = Usuario::find();
        $dp = new ActiveDataProvider([
            'query' => $q,
            'sort' => ['defaultOrder' => ['usuario' => SORT_ASC]],
            'pagination' => ['pageSize' => 20],
        ]);
        if (!($this->load($params) && $this->validate())) {
            return $dp;
        }
        $q->andFilterWhere(['rol' => $this->rol, 'activo' => $this->activo])
            ->andFilterWhere(['like', 'usuario', $this->usuario])
            ->andFilterWhere(['like', 'nombre', $this->nombre]);

        return $dp;
    }
}
