<?php

use yii\db\Migration;

/**
 * Precios atados al dólar: cada publicación puede tener precio fijo en pesos o
 * un precio de referencia en dólares que se pasa a pesos con la cotización del día.
 * El emprendimiento guarda qué dólar usar, una cotización manual opcional y la última leída.
 */
class m261113_000004_precio_dolar extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%producto}}', 'precio_fijo', $this->boolean()->notNull()->defaultValue(true)->after('precio'));
        $this->addColumn('{{%producto}}', 'precio_usd', $this->decimal(12, 2)->after('precio_fijo'));

        $this->addColumn('{{%emprendimiento}}', 'dolar_tipo', $this->string(20)->notNull()->defaultValue('blue'));
        $this->addColumn('{{%emprendimiento}}', 'dolar_manual', $this->decimal(12, 2));
        $this->addColumn('{{%emprendimiento}}', 'dolar_valor', $this->decimal(12, 2));
        $this->addColumn('{{%emprendimiento}}', 'dolar_actualizado', $this->dateTime());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%emprendimiento}}', 'dolar_actualizado');
        $this->dropColumn('{{%emprendimiento}}', 'dolar_valor');
        $this->dropColumn('{{%emprendimiento}}', 'dolar_manual');
        $this->dropColumn('{{%emprendimiento}}', 'dolar_tipo');
        $this->dropColumn('{{%producto}}', 'precio_usd');
        $this->dropColumn('{{%producto}}', 'precio_fijo');
    }
}
