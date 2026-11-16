<?php

namespace app\models;

use app\components\Dolar;
use app\components\Fotos;
use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * Datos del emprendimiento: una sola fila con nombre, banner, contacto y estilo del sitio.
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $eslogan
 * @property string|null $descripcion
 * @property string|null $banner
 * @property string|null $banner_movil
 * @property string|null $whatsapp
 * @property string|null $telefono
 * @property string|null $email
 * @property string|null $direccion
 * @property string|null $horario
 * @property string|null $instagram
 * @property string|null $facebook
 * @property string $tema
 * @property string $dolar_tipo   blue, oficial, bolsa o tarjeta
 * @property string|null $dolar_manual  si está cargada, se usa en lugar de la de la API
 * @property string|null $dolar_valor   última cotización leída de la API
 * @property string|null $dolar_actualizado
 */
class Emprendimiento extends ActiveRecord
{
    /** @var UploadedFile|null */
    public $bannerNuevo;
    /** @var UploadedFile|null versión para pantallas angostas (si no hay, se usa el banner general) */
    public $bannerMovilNuevo;

    private static ?Emprendimiento $_actual = null;

    public static function tableName()
    {
        return '{{%emprendimiento}}';
    }

    /** Los datos del sitio (se leen una sola vez por pedido) */
    public static function actual(): self
    {
        if (self::$_actual === null) {
            self::$_actual = static::find()->orderBy('id')->one()
                ?? new static(['nombre' => 'Mi Catálogo', 'tema' => Yii::$app->params['temaPredeterminado']]);
        }

        return self::$_actual;
    }

    public function rules()
    {
        $textos = ['nombre', 'eslogan', 'descripcion', 'whatsapp', 'telefono', 'email', 'direccion', 'horario', 'instagram', 'facebook'];

        return [
            [$textos, 'trim'],
            ['nombre', 'required'],
            ['nombre', 'string', 'max' => 80],
            ['eslogan', 'string', 'max' => 160],
            ['descripcion', 'string', 'max' => 300],
            ['whatsapp', 'filter', 'filter' => fn($v) => preg_replace('/\D+/', '', (string)$v)],
            ['whatsapp', 'string', 'min' => 8, 'max' => 20, 'tooShort' => 'Escribí el número completo con código de país, por ejemplo 5492991234567.'],
            ['telefono', 'string', 'max' => 40],
            ['email', 'email'],
            [['direccion', 'horario'], 'string', 'max' => 160],
            ['instagram', 'match', 'pattern' => '/^@?[\w.]{1,60}$/', 'message' => 'Escribí solo el usuario de Instagram (ej. miemprendimiento).'],
            ['instagram', 'filter', 'filter' => fn($v) => ltrim((string)$v, '@')],
            ['facebook', 'url', 'defaultScheme' => 'https'],
            ['tema', 'in', 'range' => array_keys(Yii::$app->params['temas'])],
            ['dolar_tipo', 'in', 'range' => array_keys(Dolar::TIPOS)],
            ['dolar_manual', 'filter', 'filter' => [Producto::class, 'normalizarPrecio']],
            ['dolar_manual', 'number', 'min' => 0, 'enableClientValidation' => false, 'message' => 'Escribí solo el número, por ejemplo 1565 o 1.565,50.'],
            ['dolar_manual', 'default', 'value' => null],
            [array_diff($textos, ['nombre']), 'default', 'value' => null],
            [['bannerNuevo', 'bannerMovilNuevo'], 'image', 'skipOnEmpty' => true, 'extensions' => 'jpg, jpeg, png, webp',
                'checkExtensionByMimeType' => true, 'maxSize' => Yii::$app->params['fotoMaximoMb'] * 1024 * 1024],
        ];
    }

    public function attributeLabels()
    {
        return [
            'nombre' => 'Nombre del emprendimiento',
            'eslogan' => 'Frase del banner',
            'descripcion' => 'Descripción corta (se ve al compartir el link)',
            'whatsapp' => 'WhatsApp (con código de país, ej. 5492991234567)',
            'telefono' => 'Teléfono',
            'email' => 'Correo electrónico',
            'direccion' => 'Dirección',
            'horario' => 'Horario de atención',
            'instagram' => 'Instagram (usuario)',
            'facebook' => 'Facebook (link de la página)',
            'tema' => 'Estilo del sitio',
            'dolar_tipo' => 'Dólar para calcular los precios',
            'dolar_manual' => 'Cotización manual (opcional)',
            'bannerNuevo' => 'Imagen del banner',
            'bannerMovilNuevo' => 'Banner para celular (opcional)',
        ];
    }

    /**
     * Guarda los banners subidos (el general y el de celular) reemplazando los anteriores.
     * Devuelve los errores (vacío si salió todo bien).
     */
    public function guardarBanners(): array
    {
        $errores = [];
        foreach (['banner' => $this->bannerNuevo, 'banner_movil' => $this->bannerMovilNuevo] as $campo => $subida) {
            if (!$subida) {
                continue;
            }
            $r = Fotos::guardar($subida, 'banner', Yii::$app->params['fotoLadoBanner']);
            if (is_string($r)) {
                $errores[] = $r;
                continue;
            }
            Fotos::borrar($r[1]);   // el banner no usa miniatura
            Fotos::borrar($this->$campo);
            $this->updateAttributes([$campo => $r[0]]);
        }

        return $errores;
    }

    /** @param string $campo banner o banner_movil */
    public function quitarBanner(string $campo = 'banner'): void
    {
        Fotos::borrar($this->$campo);
        $this->updateAttributes([$campo => null]);
    }

    public function getWhatsappUrl(string $texto = ''): ?string
    {
        if (!$this->whatsapp) {
            return null;
        }

        return 'https://wa.me/' . $this->whatsapp . ($texto !== '' ? '?text=' . rawurlencode($texto) : '');
    }
}
