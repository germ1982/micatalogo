<?php

use yii\db\Migration;

/** Banner opcional para celulares: un logo apaisado no entra bien en una pantalla angosta */
class m261114_000005_banner_movil extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%emprendimiento}}', 'banner_movil', $this->string(255)->after('banner'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%emprendimiento}}', 'banner_movil');
    }
}
