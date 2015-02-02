<?php

namespace branchonline\combinedrecord\files;

use yii\db\ActiveQuery;
use yii\db\Exception;

/**
 * Description of ActiveCombinedQuery
 *
 * @author jap
 */
class ActiveCombinedQuery extends ActiveQuery {

    public $combined_class;
    public $language_class;

    public function populate($rows) {
        if (empty($rows)) {
            return [];
        }

        $models = $this->createModels($rows);
        if (!empty($this->join) && $this->indexBy === null) {
            $models = $this->removeDuplicatedModels($models);
        }
        if (!empty($this->with)) {
            $this->findWith($this->with, $models);
        }
        if (!$this->asArray) {
            foreach ($models as $model) {
                $model->afterFind();
            }
        }

        return $models;
    }

    private function removeDuplicatedModels($models) {
        $hash = [];
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();

        if (count($pks) > 1) {
            foreach ($models as $i => $model) {
                $key = [];
                foreach ($pks as $pk) {
                    $key[] = $model[$pk];
                }
                $key = serialize($key);
                if (isset($hash[$key])) {
                    unset($models[$i]);
                } else {
                    $hash[$key] = true;
                }
            }
        } else {
            $pk = reset($pks);
            foreach ($models as $i => $model) {
                $key = $model[$pk];
                if (isset($hash[$key])) {
                    unset($models[$i]);
                } elseif ($key !== null) {
                    $hash[$key] = true;
                }
            }
        }

        return array_values($models);
    }

    private function createModels($rows) {
        $models = [];
        if ($this->asArray) {
            throw new Exception('Function not (yet) supported in a combinedrecord');
        } else {
            /* @var $class ActiveRecord */
            $class = $this->modelClass;
            if ($this->indexBy === null) {
                foreach ($rows as $row) {
                    $general_record = $class::instantiate($row);
                    $class::populateRecord($general_record, $row);

                    $model_class = $this->combined_class;
                    $model = new $model_class($this->modelClass, $this->language_class);
                    $model->setGeneralRecord($general_record);
                    $model->loadOtherRecord($general_record->id);

                    $models[] = $model;
                }
            } else {
                foreach ($rows as $row) {
                    $general_record = $class::instantiate($row);
                    $class::populateRecord($general_record, $row);

                    $model_class = $this->combined_class;
                    $model = new $model_class($this->modelClass, $this->language_class);
                    $model->setGeneralRecord($general_record);
                    $model->loadOtherRecord($general_record->id);
                    if (is_string($this->indexBy)) {
                        $key = $model->{$this->indexBy};
                    } else {
                        $key = call_user_func($this->indexBy, $model);
                    }
                    $models[$key] = $model;
                }
            }
        }

        return $models;
    }

    public function sum($q, $db = null) {
        throw new Exception('Function not supported yet');
    }

    public function average($q, $db = null) {
        throw new Exception('Function not supported yet');
    }

    public function min($q, $db = null) {
        throw new Exception('Function not supported yet');
    }

    public function max($q, $db = null) {
        throw new Exception('Function not supported yet');
    }

    public function scalar($db = null) {
        throw new Exception('Function not supported yet');
    }

    public function column($db = null) {
        throw new Exception('Function not supported yet');
    }

    public function exists($db = null) {
        throw new Exception('Function not supported yet');
    }

}
