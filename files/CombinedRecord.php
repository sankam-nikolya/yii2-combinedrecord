<?php

namespace branchonline\combinedrecord\files;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecordInterface;

/**
 * Description of CombinedRecord
 *
 * @author jap
 */
class CombinedRecord extends Model implements ActiveRecordInterface {

    /**
     * @event Event an event that is triggered after the record is created and populated with query result.
     */
    const EVENT_AFTER_FIND = 'afterFind';

    private $_new_record;
    private $_general_class;
    private $_general_record;
    private $_general_attributes;
    private $_other_class;
    private $_other_record;
    private $_other_attributes;
    private $_join_table_fk_to_general_table;

    public function __construct($general_class, $other_class) {
        parent::__construct();

        $this->_general_class = $general_class;
        $this->_other_class = $other_class;
        $this->_join_table_fk_to_general_table = $other_class::fkToGeneralTable();

        $this->_new_record = true;
        $this->_general_record = new $general_class;
        $this->_other_record = new $other_class;

        if (!is_null($this->_general_record) && !is_null($this->_other_record)) {
            $this->_general_attributes = $this->_general_record->attributes();
            $this->_other_attributes = $this->_other_record->attributes();
        }
    }

    public function setGeneralRecord($record) {
        $this->_new_record = false;
        $this->_general_record = $record;
    }

    public function setLanguageRecord($record) {
        $this->_other_record = $record;
    }

    public function loadOtherRecord($fk_id) {
        $language_class = $this->_other_class;
        $this->_other_record = $language_class::find()->where([$this->_join_table_fk_to_general_table => $fk_id])->one();
    }

    public static function findByClasses($general_class, $language_class) {
        $active_combined_query = Yii::createObject(ActiveCombinedQuery::className(), [$general_class]);
        $active_combined_query->combined_class = get_called_class();
        $active_combined_query->language_class = $language_class;
        return $active_combined_query;
    }

    public function load($data, $formName = NULL) {
        if (empty($data)) {
            return false;
        } else {
            $data_general = [];
            $data_language = [];

            foreach ($data['CombinedRecord'] as $k => $v) {
                if (in_array($k, $this->_general_attributes)) {
                    $data_general[$k] = $v;
                } else if (in_array($k, $this->_other_attributes)) {
                    $data_language[$k] = $v;
                }
            }

            $loaded_general_data = $this->_general_record->load($data_general, '');
            $loaded_language_data = $this->_other_record->load($data_language, '');

            return ($loaded_general_data && $loaded_language_data);
        }
    }

    private function _addExternalErrors($errors) {
        foreach ($errors as $k => $v) {
            $this->addError($k, isset($v[0]) ? $v[0] : 'Unknown error');
        }
    }

    public function validate($attributeNames = null, $clearErrors = true) {
        if ($clearErrors) {
            $this->clearErrors();
        }

        if (!$this->beforeValidate()) {
            return false;
        }

        $validated_general_record = $this->_general_record->validate();

        // Remove FK from validation if this is a new record
        $language_attributes_to_validate = $this->_other_attributes;

        if ($this->isNewRecord()) {
            $key = array_search($this->_join_table_fk_to_general_table, $language_attributes_to_validate);
            unset($language_attributes_to_validate[$key]);
        }

        $validated_language_record = $this->_other_record->validate($language_attributes_to_validate);

        if (!$validated_general_record) {
            $this->_addExternalErrors($this->_general_record->getErrors());
        }

        if (!$validated_language_record) {
            $this->_addExternalErrors($this->_other_record->getErrors());
        }

        $this->afterValidate();

        return ($validated_general_record && $validated_language_record);
    }

    public function save($runValidation = true, $attributeNames = null) {
        if ($runValidation && !$this->validate($attributeNames)) {
            return false;
        }

        if ($this->isNewRecord()) {
            return $this->insertModels();
        } else {
            return $this->updateModels() !== false;
        }
    }

    public function isNewRecord() {
        return $this->_new_record;
    }

    public function insertModels() {
        if ($this->_general_record->save()) {
            $fk = $this->_join_table_fk_to_general_table;
            $this->_other_record->$fk = $this->_general_record->id;

            if ($this->_other_record->save()) {
                return true;
            } else {
                $this->_general_record->delete();
            }
        } else {
            return false;
        }
    }

    public function updateModels() {
        return ($this->_general_record->save(false) && $this->_other_record->save(false));
    }

    public function __get($name) {
        if ($this->_general_record->hasAttribute($name)) {
            return $this->_general_record->$name;
        } else if ($this->_other_record->hasAttribute($name)) {
            return $this->_other_record->$name;
        }
    }

    public function __set($name, $value) {
        if ($this->_general_record->hasAttribute($name)) {
            $this->_general_record->$name = $value;
        } else if ($this->_other_record->hasAttribute($name)) {
            $this->_other_record->$name = $value;
        }
    }

    public function attributes() {
        return array_merge($this->_general_record->attributes(), $this->_other_record->attributes());
    }

    public function afterFind() {
        $this->trigger(self::EVENT_AFTER_FIND);
    }

    public function delete() {
        
    }

    public function equals($record) {
        
    }

    public function getAttribute($name) {
        
    }

    public function getIsNewRecord() {
        
    }

    public function getOldPrimaryKey($asArray = false) {
        
    }

    public function getPrimaryKey($asArray = false) {
        
    }

    public function getRelation($name, $throwException = true) {
        
    }

    public function hasAttribute($name) {
        
    }

    public function insert($runValidation = true, $attributes = null) {
        
    }

    public function link($name, $model, $extraColumns = array()) {
        
    }

    public function setAttribute($name, $value) {
        
    }

    public function unlink($name, $model, $delete = false) {
        
    }

    public function update($runValidation = true, $attributeNames = null) {
        
    }

    public static function deleteAll($condition = null) {
        
    }

    public static function find() {
        
    }

    public static function findAll($condition) {
        
    }

    public static function findOne($condition) {
        
    }

    public static function getDb() {
        
    }

    public static function isPrimaryKey($keys) {
        
    }

    public static function primaryKey() {
        
    }

    public static function updateAll($attributes, $condition = null) {
        
    }

}
