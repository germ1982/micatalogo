<?php

use yii\db\Migration;

/** Usuarios del panel: no hay registro público, los crea un administrador. */
class m261112_000001_usuario extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%usuario}}', [
            'id' => $this->primaryKey(),
            'usuario' => $this->string(40)->notNull()->unique(),
            'nombre' => $this->string(80)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'rol' => $this->string(10)->notNull()->defaultValue('editor'),
            'activo' => $this->boolean()->notNull()->defaultValue(true),
            'creado_en' => $this->dateTime()->notNull(),
            'ultimo_ingreso' => $this->dateTime(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function safeDown()
    {
        $this->dropTable('{{%usuario}}');
    }
}
