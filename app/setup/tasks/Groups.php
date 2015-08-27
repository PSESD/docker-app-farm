<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace canis\appFarm\setup\tasks;

use canis\appFarm\models\Group;
use canis\appFarm\models\Relation;

/**
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Groups extends \canis\setup\tasks\BaseTask
{
    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return 'Groups';
    }
    /**
     * Get base groups.
     */
    public function getBaseGroups()
    {
        return ['Top' => ['Users' => ['Administrators' => ['Super Administrators']], 'Public']];
    }

    /**
     * @inheritdoc
     */
    public function test($groups = null)
    {
        if ($groups === null) {
            $groups = $this->baseGroups;
        }
        foreach ($groups as $group => $subGroups) {
            if (!is_numeric($group)) {
                if (!Group::find()->disableAccessCheck()->where(['name' => $group])->one()) {
                    return false;
                }
                if (!$this->test($subGroups)) {
                    return false;
                }
            } else {
                if (!Group::find()->disableAccessCheck()->where(['name' => $subGroups])->one()) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $groups = $this->baseGroups;
        array_walk($groups, [$this, 'groupWalker']);

        return empty($this->errors);
    }

    /**
     *
     */
    public function groupWalker(&$item, $key, $mparent = null)
    {
        if (is_array($item)) {
            $parent  = Group::find()->disableAccessCheck()->where(['name' => $key])->one();
            if (empty($parent)) {
                $parent = new Group();
                //$parent->disableAcl();
                $parent->name = $key;
                $parent->system = preg_replace('/ /', '_', strtolower($parent->name));
                $parent->level = $this->getGroupLevel($key);

                if (!$parent->save()) {
                    $this->errors[] = "Failed to create group {$key}!";

                    return false;
                }
                if (!empty($mparent)) {
                    $r = new Relation();
                    $r->parent_object_id = $mparent;
                    $r->child_object_id = $parent->id;
                    $r->active = 1;
                    if (!$r->save()) {
                        $this->errors[] = "Failed to create group relationship {$key}!";

                        return false;
                    }
                }
            }
            $item = array_walk($item, [$this, 'groupWalker'], $parent->id);
        } else {
            $sitem = Group::find()->disableAccessCheck()->where(['name' => $item])->one();
            if (empty($sitem)) {
                $sitem = new Group();
                //$sitem->disableAcl();
                $sitem->name = $item;
                $sitem->system = preg_replace('/ /', '_', strtolower($sitem->name));
                $sitem->level = $this->getGroupLevel($item);

                if (!$sitem->save()) {
                    $this->errors[] = "Failed to create group {$item}!";

                    return false;
                }
                if (!empty($mparent)) {
                    $r = new Relation();
                    $r->parent_object_id = $mparent;
                    $r->child_object_id = $sitem->id;
                    $r->active = 1;
                    if (!$r->save()) {
                        $this->errors[] = "Failed to create group relationship {$key}!";

                        return false;
                    }
                }
            }
            $setup->registry['Group'][$item] = $sitem->id;
        }
    }

    /**
     * Get group level.
     */
    public function getGroupLevel($k)
    {
        switch ($k) {
        case 'Super Administrators':
            return 1001;
            break;
        case 'Administrators':
            return 1000;
            break;
        case 'Top':
            return 0;
            break;
        default:
            return 100;
            break;
        }
    }
}
