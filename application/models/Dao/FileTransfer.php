<?php

/**
 * Dao_FileTransfer
 * 
 * @package Dao
 * @author duyld
 */
class Dao_FileTransfer extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'file_transfer';
    
    protected $_rowClass = 'Dto_FileTransfer';
    
    /**
     * Mark all file as deleted
     * 
     * @param mixed $idArray
     * @return void
     */
    public function dontDisplayImage($idArray = array())
    {
        if (!is_array($idArray)) {
            $idArray = array($idArray);
        }

        if ($idArray) {
            $where = $this->getAdapter()->quoteInto('id IN (?)', $idArray);
            $this->getAdapter()->update($this->_name, array('file_block' => 1), $where);
        }
    }
    
}
