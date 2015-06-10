<?php
namespace CakephpBlueimpUpload\Model\Entity;

use Cake\ORM\Entity;

/**
 * AlaxosUpload Entity.
 */
class Upload extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'original_filename' => true,
        'unique_filename' => true,
        'subfolder' => true,
        'mimetype' => true,
        'size' => true,
        'hash' => true,
        'upload_id' => true,
        'complete' => true,
        'label' => true,
        'created_by' => true,
        'modified_by' => true,
        'upload' => true,
    ];
}
