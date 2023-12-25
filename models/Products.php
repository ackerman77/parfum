<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "products".
 *
 * @property int $id_product
 * @property string $name
 * @property float $price
 * @property int|null $category_id
 * @property string|null $description
 * @property string|null $image
 *
 * @property Category $category
 * @property Orders[] $orders
 */
class Products extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_product', 'name', 'price'], 'required'],
            [['id_product', 'category_id'], 'integer'],
            [['price'], 'number'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 100],
            [['image'], 'string', 'max' => 255],
            [['id_product'], 'unique'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id_category']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_product' => 'Id Product',
            'name' => 'Name',
            'price' => 'Price',
            'category_id' => 'Category ID',
            'description' => 'Description',
            'image' => 'Image',
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id_category' => 'category_id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Orders::class, ['product_id' => 'id_product']);
    }
}
