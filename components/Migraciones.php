<?php

namespace app\components;

use Yii;
use yii\db\Migration;

/**
 * Aplica las migraciones de migrations/ desde la web, para hostings sin consola
 * (InfinityFree). Usa la misma tabla {{%migration}} que "php yii migrate",
 * así se pueden mezclar las dos formas.
 */
class Migraciones
{
    private const TABLA = '{{%migration}}';

    /** Nombres de las migraciones que faltan aplicar, en orden */
    public static function pendientes(): array
    {
        self::crearTabla();
        $aplicadas = array_flip(Yii::$app->db->createCommand('SELECT version FROM ' . self::TABLA)->queryColumn());
        $todas = [];
        foreach (glob(Yii::getAlias('@app/migrations') . '/m*.php') as $archivo) {
            $nombre = basename($archivo, '.php');
            if (preg_match('/^m\d{6}_\d{6}_\w+$/', $nombre) && !isset($aplicadas[$nombre])) {
                $todas[] = $nombre;
            }
        }
        sort($todas);

        return $todas;
    }

    /**
     * Aplica las pendientes una por una. Se detiene en la primera que falle.
     * @return array ['aplicadas' => string[], 'error' => string|null]
     */
    public static function aplicar(): array
    {
        $resultado = ['aplicadas' => [], 'error' => null];
        foreach (self::pendientes() as $nombre) {
            require_once Yii::getAlias("@app/migrations/$nombre.php");
            /** @var Migration $migracion */
            $migracion = new $nombre(['db' => Yii::$app->db, 'compact' => true]);
            ob_start();   // las migraciones escriben su progreso con echo
            try {
                $ok = $migracion->up() !== false;
                $salida = ob_get_clean();
            } catch (\Throwable $e) {
                ob_end_clean();
                $ok = false;
                $salida = $e->getMessage();
            }
            if (!$ok) {
                $resultado['error'] = "Falló $nombre: " . trim(strip_tags((string)$salida));
                Yii::error($resultado['error'], __METHOD__);
                break;
            }
            Yii::$app->db->createCommand()->insert(self::TABLA, ['version' => $nombre, 'apply_time' => time()])->execute();
            $resultado['aplicadas'][] = $nombre;
        }
        // Con DEBUG = 0 el esquema de las tablas queda en caché: hay que renovarlo
        Yii::$app->db->schema->refresh();
        Yii::$app->cache->flush();

        return $resultado;
    }

    private static function crearTabla(): void
    {
        $db = Yii::$app->db;
        if ($db->schema->getTableSchema(self::TABLA, true) !== null) {
            return;
        }
        $db->createCommand()->createTable(self::TABLA, [
            'version' => 'varchar(180) NOT NULL PRIMARY KEY',
            'apply_time' => 'integer',
        ])->execute();
        $db->createCommand()->insert(self::TABLA, ['version' => 'm000000_000000_base', 'apply_time' => time()])->execute();
    }
}
