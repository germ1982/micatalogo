<?php

namespace app\components;

use app\models\Emprendimiento;
use Yii;

/**
 * Cotización del dólar para pasar a pesos los precios de referencia en dólares.
 *
 * - Si el emprendimiento cargó una cotización manual, se usa esa.
 * - Si no, se consulta dolarapi.com (gratis, sin clave) como mucho cada 30 minutos
 *   y se guarda en el emprendimiento. Si la API no responde, queda la última guardada.
 */
class Dolar
{
    public const TIPOS = [
        'blue' => 'Dólar blue',
        'oficial' => 'Dólar oficial',
        'bolsa' => 'Dólar MEP (bolsa)',
        'tarjeta' => 'Dólar tarjeta',
    ];

    private const URL = 'https://dolarapi.com/v1/dolares/';
    private const MINUTOS = 30;

    private static ?float $_valor = null;
    private static bool $_leido = false;

    /** Pesos por dólar, o null si nunca se pudo obtener */
    public static function cotizacion(): ?float
    {
        if (self::$_leido) {
            return self::$_valor;
        }
        self::$_leido = true;
        $sitio = Emprendimiento::actual();

        if ($sitio->dolar_manual !== null && (float)$sitio->dolar_manual > 0) {
            return self::$_valor = (float)$sitio->dolar_manual;
        }

        $vieja = $sitio->dolar_actualizado === null
            || strtotime($sitio->dolar_actualizado) < time() - self::MINUTOS * 60;
        // Si la API falló hace poco no se vuelve a probar en cada visita
        if ($vieja && !$sitio->isNewRecord && Yii::$app->cache->add('dolar-intento', 1, 300)) {
            self::actualizar($sitio);
        }

        return self::$_valor = $sitio->dolar_valor !== null ? (float)$sitio->dolar_valor : null;
    }

    /** Consulta la API y guarda la cotización. Devuelve true si la pudo leer */
    public static function actualizar(?Emprendimiento $sitio = null): bool
    {
        $sitio ??= Emprendimiento::actual();
        $tipo = isset(self::TIPOS[$sitio->dolar_tipo]) ? $sitio->dolar_tipo : 'blue';
        $contexto = stream_context_create(['http' => ['timeout' => 3, 'header' => "Accept: application/json\r\n"]]);
        $json = @file_get_contents(self::URL . $tipo, false, $contexto);
        $datos = $json ? json_decode($json, true) : null;
        $venta = (float)($datos['venta'] ?? 0);
        if ($venta <= 0) {
            Yii::warning("No se pudo leer la cotización del dólar $tipo", __METHOD__);

            return false;
        }
        $sitio->updateAttributes(['dolar_valor' => $venta, 'dolar_actualizado' => date('Y-m-d H:i:s')]);
        self::$_leido = false;

        return true;
    }

    /** "al dólar blue de hoy" o "a la cotización del vendedor", para explicar el precio en la ficha */
    public static function explicacion(): string
    {
        $sitio = Emprendimiento::actual();
        if ($sitio->dolar_manual !== null && (float)$sitio->dolar_manual > 0) {
            return 'a la cotización del vendedor';
        }

        return 'al ' . mb_strtolower(self::TIPOS[$sitio->dolar_tipo] ?? 'dólar') . ' de hoy';
    }
}
