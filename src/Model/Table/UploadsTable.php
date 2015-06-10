<?php
namespace CakephpBlueimpUpload\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use CakephpBlueimpUpload\Model\Entity\AlaxosUpload;

/**
 * AlaxosUploads Model
 */
class UploadsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $table = Configure::check('CakephpBlueimpUpload.upload_table') ? Configure::read('CakephpBlueimpUpload.upload_table') : 'uploads';
        
        $this->table($table);
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');
        
        $validator
            ->requirePresence('upload_id', 'create')
            ->notEmpty('upload_id');
        
        $validator
            ->requirePresence('original_filename', 'create')
            ->notEmpty('original_filename');
            
        $validator
            ->requirePresence('unique_filename', 'create')
            ->notEmpty('unique_filename');
            
        $validator
            ->allowEmpty('subfolder');
            
        $validator
            ->allowEmpty('mimetype');
            
        $validator
            ->add('size', 'valid', ['rule' => 'numeric'])
            ->requirePresence('size', 'create')
            ->notEmpty('size');
            
        $validator
            ->requirePresence('hash', 'create')
            ->notEmpty('hash');
            
        $validator
            ->add('upload_complete', 'valid', ['rule' => 'boolean'])
            ->requirePresence('upload_complete', 'create')
            ->notEmpty('upload_complete');
            
        $validator
            ->allowEmpty('label');
            
        $validator
            ->add('created_by', 'valid', ['rule' => 'numeric'])
            ->requirePresence('created_by', 'create')
            ->notEmpty('created_by');
            
        $validator
            ->add('modified_by', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('modified_by');

        return $validator;
    }
}
