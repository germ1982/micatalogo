<?php

use yii\db\Migration;

/**
 * Datos del emprendimiento (una sola fila: nombre, banner, contacto y estilo)
 * y los párrafos de "Acerca de", cada uno con sus fotos.
 */
class m261112_000003_emprendimiento extends Migration
{
    public function safeUp()
    {
        $opciones = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        $this->createTable('{{%emprendimiento}}', [
            'id' => $this->primaryKey(),
            'nombre' => $this->string(80)->notNull(),
            'eslogan' => $this->string(160),
            'descripcion' => $this->string(300),
            'banner' => $this->string(255),
            'whatsapp' => $this->string(30),
            'telefono' => $this->string(40),
            'email' => $this->string(120),
            'direccion' => $this->string(160),
            'horario' => $this->string(160),
            'instagram' => $this->string(120),
            'facebook' => $this->string(160),
            'tema' => $this->string(30)->notNull()->defaultValue('generico'),
        ], $opciones);
        $this->insert('{{%emprendimiento}}', [
            'nombre' => 'Mi Catálogo',
            'eslogan' => 'Todo lo que tenemos para vos',
            'tema' => 'generico',
        ]);

        $this->createTable('{{%acerca_parrafo}}', [
            'id' => $this->primaryKey(),
            'titulo' => $this->string(120),
            'texto' => $this->text()->notNull(),
            'orden' => $this->integer()->notNull()->defaultValue(0),
        ], $opciones);

        $this->createTable('{{%acerca_foto}}', [
            'id' => $this->primaryKey(),
            'parrafo_id' => $this->integer()->notNull(),
            'archivo' => $this->string(255)->notNull(),
            'miniatura' => $this->string(255)->notNull(),
            'orden' => $this->integer()->notNull()->defaultValue(0),
        ], $opciones);
        $this->addForeignKey('fk_acerca_foto_parrafo', '{{%acerca_foto}}', 'parrafo_id',
            '{{%acerca_parrafo}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%acerca_foto}}');
        $this->dropTable('{{%acerca_parrafo}}');
        $this->dropTable('{{%emprendimiento}}');
    }
}
