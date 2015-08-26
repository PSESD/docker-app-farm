<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\wdf\models;

use Yii;
use canis\components\db\behaviors\ActiveTaxonomy;
use canis\components\db\behaviors\PrimaryRelation;
use canis\components\db\behaviors\QueryTaxonomy;

/**
 * Relation is the model class for table "relation".
 */
class Relation extends \canis\db\models\Relation
{
    /**
    * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            // 'Taxonomy' => [
            //     'class' => ActiveTaxonomy::className(),
            //     'viaModelClass' => 'RelationTaxonomy',
            //     'relationKey' => 'relation_id',
            // ],
            // 'PrimaryRelation' => [
            //     'class' => PrimaryRelation::className(),
            // ]
        ]);
    }

    public static function queryBehaviors()
    {
        return array_merge(parent::queryBehaviors(),
            [
                // 'Taxonomy' => [
                //     'class' => QueryTaxonomy::className(),
                //     'viaModelClass' => 'RelationTaxonomy',
                //     'relationKey' => 'relation_id',
                // ],
            ]
        );
    }

    public function addFields($caller, &$fields, $relationship, $owner)
    {
        $baseField = ['model' => $this];
        if (isset($this->id)) {
            $fields['relation:id'] = $caller->createField('id', $owner, $baseField);
        }
        if (!empty($relationship->taxonomy)
                && ($taxonomyItem = Yii::$app->collectors['taxonomies']->getOne($relationship->taxonomy))
                && ($taxonomy = $taxonomyItem->object)
                && $taxonomy) {
            $fieldName = 'relation:taxonomy_id';
            $fieldSchema = $caller->createColumnSchema('taxonomy_id', ['type' => 'taxonomy', 'phpType' => 'object', 'dbType' => 'taxonomy', 'allowNull' => true]);

            $fields[$fieldName] = $caller->createTaxonomyField($fieldSchema, $taxonomyItem, $owner, $baseField);
        }
    }
}
