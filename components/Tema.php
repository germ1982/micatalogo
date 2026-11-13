<?php

namespace app\components;

use app\models\Emprendimiento;
use Yii;

/**
 * Estilo visual del sitio. Es uno solo para todos los visitantes:
 * lo elige un administrador en el panel y queda guardado en el emprendimiento.
 */
class Tema
{
    public static function actual(): string
    {
        $tema = Emprendimiento::actual()->tema;

        return isset(Yii::$app->params['temas'][$tema]) ? $tema : Yii::$app->params['temaPredeterminado'];
    }

    public static function datos(?string $tema = null): array
    {
        return Yii::$app->params['temas'][$tema ?? self::actual()];
    }

    public static function lista(): array
    {
        return Yii::$app->params['temas'];
    }

    /** Link de Google Fonts con la fuente de títulos de un tema (o de varios) */
    public static function urlFuentes(array $temas): string
    {
        $familias = [];
        foreach ($temas as $tema) {
            $familias[self::datos($tema)['fuente']] = true;
        }
        $partes = array_map(fn($f) => 'family=' . str_replace(' ', '+', $f) . ':wght@500;700', array_keys($familias));

        return 'https://fonts.googleapis.com/css2?' . implode('&', $partes) . '&display=swap';
    }
}
