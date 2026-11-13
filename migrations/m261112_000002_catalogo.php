<?php

use yii\db\Migration;

/** Categorías, productos (publicaciones) y sus fotos. */
class m261112_000002_catalogo extends Migration
{
    public function safeUp()
    {
        $opciones = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        $this->createTable('{{%categoria}}', [
            'id' => $this->primaryKey(),
            'nombre' => $this->string(60)->notNull()->unique(),
        ], $opciones);

        $this->createTable('{{%producto}}', [
            'id' => $this->primaryKey(),
            'categoria_id' => $this->integer(),
            'titulo' => $this->string(120)->notNull(),
            'descripcion' => $this->text(),
            'precio' => $this->decimal(12, 2),
            'talle' => $this->string(60),
            'color' => $this->string(60),
            'material' => $this->string(80),
            'disponible' => $this->boolean()->notNull()->defaultValue(true),
            'creado_en' => $this->dateTime()->notNull(),
            'actualizado_en' => $this->dateTime()->notNull(),
        ], $opciones);
        $this->createIndex('idx_producto_disponible', '{{%producto}}', ['disponible', 'creado_en']);
        $this->addForeignKey('fk_producto_categoria', '{{%producto}}', 'categoria_id',
            '{{%categoria}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%producto_foto}}', [
            'id' => $this->primaryKey(),
            'producto_id' => $this->integer()->notNull(),
            'archivo' => $this->string(255)->notNull(),
            'miniatura' => $this->string(255)->notNull(),
            'orden' => $this->integer()->notNull()->defaultValue(0),
        ], $opciones);
        $this->addForeignKey('fk_producto_foto_producto', '{{%producto_foto}}', 'producto_id',
            '{{%producto}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%producto_foto}}');
        $this->dropTable('{{%producto}}');
        $this->dropTable('{{%categoria}}');
    }
}
