<?php

namespace app\models;

use app\components\Fotos;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * Publicación del catálogo.
 *
 * @property int $id
 * @property int|null $categoria_id
 * @property string $titulo
 * @property string|null $descripcion
 * @property string|null $precio
 * @property string|null $talle
 * @property string|null $color
 * @property string|null $material
 * @property bool $disponible
 * @property string $creado_en
 * @property string $actualizado_en
 * @property Categoria|null $categoria
 * @property ProductoFoto[] $fotos
 * @property ProductoFoto|null $fotoPrincipal
 */
class Producto extends ActiveRecord
{
    /** Opciones del orden del catálogo (la primera es la predeterminada) */
    public const ORDENES = [
        'recientes' => 'Más recientes',
        'categoria' => 'Por categoría',
        'alfabetico' => 'Alfabético (A-Z)',
        'precio-menor' => 'Precio: menor a mayor',
        'precio-mayor' => 'Precio: mayor a menor',
    ];

    /** @var UploadedFile[] fotos nuevas del formulario */
    public $fotosNuevas = [];

    public static function tableName()
    {
        return '{{%producto}}';
    }

    public function rules()
    {
        return [
            [['titulo', 'descripcion', 'talle', 'color', 'material'], 'trim'],
            ['titulo', 'required'],
            ['titulo', 'string', 'max' => 120],
            ['descripcion', 'string', 'max' => 5000],
            [['talle', 'color'], 'string', 'max' => 60],
            ['material', 'string', 'max' => 80],
            ['precio', 'filter', 'filter' => [self::class, 'normalizarPrecio']],
            // Sin validación en el navegador: "12.500,50" recién se normaliza en el servidor
            ['precio', 'number', 'min' => 0, 'max' => 9999999999, 'enableClientValidation' => false,
                'message' => 'Escribí solo el número, por ejemplo 12500 o 12.500,50.'],
            ['categoria_id', 'exist', 'targetClass' => Categoria::class, 'targetAttribute' => 'id'],
            ['disponible', 'boolean'],
            [['descripcion', 'talle', 'color', 'material', 'precio', 'categoria_id'], 'default', 'value' => null],
            ['fotosNuevas', 'image', 'skipOnEmpty' => true, 'maxFiles' => Yii::$app->params['fotosPorProducto'],
                'extensions' => 'jpg, jpeg, png, webp', 'checkExtensionByMimeType' => true,
                'maxSize' => Yii::$app->params['fotoMaximoMb'] * 1024 * 1024],
        ];
    }

    public function attributeLabels()
    {
        return [
            'categoria_id' => 'Categoría',
            'titulo' => 'Título',
            'descripcion' => 'Descripción',
            'precio' => 'Precio',
            'talle' => 'Talle',
            'color' => 'Color',
            'material' => 'Material',
            'disponible' => 'Disponible',
            'creado_en' => 'Publicado',
            'actualizado_en' => 'Actualizado',
            'fotosNuevas' => 'Agregar fotos',
        ];
    }

    /** "12.500,50" o "12.500" → "12500.50" / "12500". Vacío → null */
    public static function normalizarPrecio($valor)
    {
        $valor = trim(str_replace(['$', ' '], '', (string)$valor));
        if ($valor === '') {
            return null;
        }
        if (str_contains($valor, ',')) {
            $valor = str_replace(['.', ','], ['', '.'], $valor);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $valor)) {
            $valor = str_replace('.', '', $valor);
        }

        return $valor;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $ahora = date('Y-m-d H:i:s');
        if ($insert) {
            $this->creado_en = $ahora;
        }
        $this->actualizado_en = $ahora;

        return true;
    }

    public function afterDelete()
    {
        parent::afterDelete();
        Fotos::borrarCarpeta('productos/' . $this->id);
    }

    public function getCategoria()
    {
        return $this->hasOne(Categoria::class, ['id' => 'categoria_id']);
    }

    public function getFotos()
    {
        return $this->hasMany(ProductoFoto::class, ['producto_id' => 'id'])->orderBy(['orden' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /** Guarda las fotos subidas. Devuelve los errores (vacío si salió todo bien) */
    public function guardarFotos(): array
    {
        $errores = [];
        $orden = (int)ProductoFoto::find()->where(['producto_id' => $this->id])->max('orden');
        $lugar = Yii::$app->params['fotosPorProducto'] - (int)ProductoFoto::find()->where(['producto_id' => $this->id])->count();
        foreach ($this->fotosNuevas as $subida) {
            if ($lugar-- <= 0) {
                $errores[] = 'Se llegó al máximo de ' . Yii::$app->params['fotosPorProducto'] . ' fotos por publicación.';
                break;
            }
            $r = Fotos::guardar($subida, 'productos/' . $this->id);
            if (is_string($r)) {
                $errores[] = $r;
                continue;
            }
            $foto = new ProductoFoto(['producto_id' => $this->id, 'archivo' => $r[0], 'miniatura' => $r[1], 'orden' => ++$orden]);
            $foto->save(false);
        }

        return $errores;
    }

    public function getFotoPrincipal(): ?ProductoFoto
    {
        return $this->fotos[0] ?? null;
    }

    public function getSlug(): string
    {
        return Inflector::slug(StringHelper::truncate($this->titulo, 60, ''));
    }

    public function getUrl(bool $absoluta = false): string
    {
        return Url::to(['/site/producto', 'id' => $this->id, 'slug' => $this->getSlug()], $absoluta);
    }

    public function getPrecioTexto(): string
    {
        if ($this->precio === null || $this->precio === '') {
            return 'Consultar';
        }
        $decimales = fmod((float)$this->precio, 1) > 0 ? 2 : 0;

        return '$ ' . number_format((float)$this->precio, $decimales, ',', '.');
    }

    /** Talle, color y material, solo los que están cargados */
    public function getDetalles(): array
    {
        return array_filter([
            'Talle' => $this->talle,
            'Color' => $this->color,
            'Material' => $this->material,
        ], fn($v) => $v !== null && $v !== '');
    }

    /**
     * Productos del catálogo público con buscador, categoría y orden.
     * $todos = true (usuario logueado): incluye los no disponibles.
     */
    public static function catalogo(string $texto, ?int $categoria, string $orden, bool $todos): ActiveDataProvider
    {
        $q = static::find()->alias('p')->joinWith('categoria c')->with('fotos');
        if (!$todos) {
            $q->andWhere(['p.disponible' => 1]);
        }
        if ($categoria) {
            $q->andWhere(['p.categoria_id' => $categoria]);
        }
        // Cada palabra tiene que aparecer en alguno de los campos
        foreach (preg_split('/\s+/u', trim($texto), -1, PREG_SPLIT_NO_EMPTY) as $palabra) {
            $q->andWhere(['or',
                ['like', 'p.titulo', $palabra],
                ['like', 'p.descripcion', $palabra],
                ['like', 'p.talle', $palabra],
                ['like', 'p.color', $palabra],
                ['like', 'p.material', $palabra],
                ['like', 'c.nombre', $palabra],
            ]);
        }

        $sinPrecioAlFinal = new \yii\db\Expression('p.precio IS NULL');
        $q->orderBy(match ($orden) {
            'categoria' => [new \yii\db\Expression('c.nombre IS NULL'), 'c.nombre' => SORT_ASC, 'p.creado_en' => SORT_DESC],
            'alfabetico' => ['p.titulo' => SORT_ASC],
            'precio-menor' => [$sinPrecioAlFinal, 'p.precio' => SORT_ASC],
            'precio-mayor' => [$sinPrecioAlFinal, 'p.precio' => SORT_DESC],
            default => ['p.creado_en' => SORT_DESC, 'p.id' => SORT_DESC],
        });

        return new ActiveDataProvider([
            'query' => $q,
            'sort' => false,
            'pagination' => ['pageSize' => Yii::$app->params['productosPorPagina']],
        ]);
    }
}
