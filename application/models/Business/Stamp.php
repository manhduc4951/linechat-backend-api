<?php
/**
 * Business_Stamp class
 * 
 * @package LineChatApp
 * @subpackage Business
 * @author ducdm
 */
class Business_Stamp
{    
    /**
     * Stamp DAO
     * 
     * @var Dao_Stamp
     */
    protected $stampDao;
    
    
    /**
     * Constructor
     * 
     * @return Business_Stamp
     */
    public function __construct()
    {        
        $this->stampDao = new Dao_Stamp();
		
        return $this;
    }
    
    /**
     * Insert new record from modify action
     * 
     * @param Dto_Stamp $itemDto
     * @param Zend_Form $form
     * @return array
     */    
    public function createStamp(Dto_Stamp $itemDto, Zend_Form $form)
    {
        $this->stampDao->getAdapter()->beginTransaction();
        $id = $this->stampDao->insert($itemDto);        
        //unzip and save each item(images) in zip package to database, table: stamp_item
        $this->unzip($itemDto->getZipPath(), $id);
        //rename zip file        
        $oldZipPath = $itemDto->getZipPath();        
        $itemDto->stamp_zip_package = $id.'.zip';
        $itemDto->public_date = date('Y-m-d h:i:s', strtotime($itemDto->public_date));        
        $this->stampDao->update($itemDto);        
        rename($oldZipPath, $itemDto->getZipPath());        
        //make dir for each item in one package        
        mkdir(realpath(dirname($itemDto->getZipPath())).DIRECTORY_SEPARATOR.$id);     
        // unzip each file to dir
        $result_upload = $this->unzipFiles($itemDto->getZipPath(), $id);
        
        if($result_upload['status'] == false) {            
            $this->stampDao->getAdapter()->rollBack();
            $form->rollback();
            unlink(realpath($itemDto->getZipPath()));            
            Qsoft_Helper_File::delete_files(realpath(dirname($itemDto->getZipPath())).DIRECTORY_SEPARATOR.$id, true, 1);
            
            return $result_upload;
        }
        
        $this->stampDao->getAdapter()->commit();
        return array('status'=>true);    
    }
    
    /**
     * Update an existing record from modify action
     * 
     * @param mixed $itemDto
     * @param mixed $form
     * @param mixed $oldDto
     * @param mixed $array_field_name
     * @return array
     */
    public function updateStamp($itemDto, $form, $oldDto, $array_field_name)
    {        
        $array_field_upload = array('stamp_small_image' => 'getSmallImagePath',
                                    'stamp_large_image' => 'getLargeImagePath',
                                    'stamp_zip_package' => 'getZipPath',                                     
                                    );
        foreach ($array_field_upload as $field_name => $path)
        { 
            if($itemDto->{$field_name} == null) {                        
                unset($array_field_name[array_search($field_name,$array_field_name)]);
                unset($itemDto->{$field_name});            
            } else {                
                @unlink(realpath($itemDto->{$path}($oldDto->{$field_name})));                
                if($itemDto->stamp_zip_package) {                    
                    rename($itemDto->{$path}(), $itemDto->{$path}($itemDto->stamp_id.'.zip'));
                    $itemDto->{$field_name} = $itemDto->stamp_id.'.zip';
                    //delete all files and updated new files                    
                    $files = glob(realpath(dirname($itemDto->{$path}())).DIRECTORY_SEPARATOR.$itemDto->stamp_id.DIRECTORY_SEPARATOR.'*');
                    foreach($files as $file) { unlink($file); }
                    // unzip files to directory
                    $this->unzipFiles($itemDto->{$path}(), $itemDto->stamp_id);
                }                     
            }   
        }        
        $itemDto->updated_at = Qsoft_Helper_Datetime::currentTime();
        $itemDto->public_date = date('Y-m-d h:i:s', strtotime($itemDto->public_date));
        $array_field_name[] = 'updated_at';
        unset($array_field_name[array_search('back',$array_field_name)]);        
               
        $this->stampDao->update($itemDto, $array_field_name);
        
        // unzip and update each item(images) in ios_package to database: ONLY IOS_PACKAGE
        if (isset($itemDto->stamp_zip_package)) {
            $stampItemDao = new Dao_StampItem();
            $stampItemDao->deleteBy('stamp_id', $itemDto->stamp_id);
            $this->unzip($itemDto->getZipPath(), $itemDto->stamp_id);    
        }
        
        return array('status' => true);    
    }
    
    /**
     * Check valid file upload
     * 
     * @param mixed $itemDto
     * @return int Count errors
     */
    public function checkFileUpload($itemDto)
    {
        $count_error = 0;
        if($itemDto->stamp_zip_package) {
            $result_upload = $this->unzipFiles($itemDto->getZipPath(), null, true);
            if ($result_upload['status'] == false) {
                $count_error++;
            }
            Qsoft_Helper_File::delete_files(Zend_Registry::get('app_config')->stamp->zip->tmp); 
            
        }        
        if($count_error > 0) {
            if($itemDto->getZipPath()) unlink($itemDto->getZipPath());
        }
        return $count_error;            
    }
    
    /**
     * Unzip stamp package and save each item(image) to database
     * 
     * @param string $filePath Path to zip package ios
     * @param integer $stamp_id
     * @return Business_Stamp
     */    
    protected function unzip($filePath, $stamp_id)
    {
        $archive = ezcArchiveZip::open($filePath);        
        $fileNames = array();
        while( $archive->valid() )
        {            
            $entry = $archive->current();
            if ($entry->isFile()) {
                if(substr_count($entry->getPath(), '/') == 0 and substr_count($entry->getPath(), "\\") == 0 ) {
                    $img = $entry->getPath();
                    $ext = (substr_count($img,'_') > 0) ? '.' . pathinfo($img, PATHINFO_EXTENSION) : '';
                    $fileNames[] = substr($img, 0, strrpos($img, '_')).$ext ?: $img;
                }  
            }
            $archive->next();
        }
        
        $fileUniques = array_count_values($fileNames);
        $stampItemDao = new Dao_StampItem();
        foreach ($fileUniques as $stamp_item_file_name => $stamp_item_length)
        {
            $stampItemDto = new Dto_StampItem();
            $stampItemDto->stamp_item_file_name = $stamp_item_file_name;
            $stampItemDto->stamp_item_length = $stamp_item_length;            
            $stampItemDto->detectStampType();
            $stampItemDto->stamp_id = $stamp_id;
            $stampItemDao->insert($stampItemDto);
        }
        
        return $this;
    }
    
    /**
     * Unzip file to directory
     * 
     * @param string $file
     * @param integer $stamp_id directory name
     * @param string $tmp_dir tmp directory
     * @param boolean $unzipSubFolders unzip all subfolders also
     * @return Business_Stamp
     */    
    protected function unzipFiles($file, $stamp_id, $tmp_dir = null, $unzipSubFolders = false)
    {
        $archive = ezcArchive::open($file);
        $count = 0;        
        $dir = ($tmp_dir === null) ? realpath(dirname($file)).DIRECTORY_SEPARATOR.$stamp_id
                                  : Zend_Registry::get('app_config')->stamp->zip->tmp ;
        while( $archive->valid() )
        {
            $entry = $archive->current();
            if($entry->isFile()) {                
                //don't unzip file in subfolders
                if($unzipSubFolders OR (substr_count($entry->getPath(), '/') == 0 and substr_count($entry->getPath(), "\\") == 0 )) {
                    $archive->extractCurrent($dir);
                    $count++;    
                }  
            }
            $archive->next();
            
        }
        
        if ($count == 0) {
            return array('status' => false, 'error_code' => ERROR_ZIP_FILE_IS_EMPTY);
        } else {
            return array('status' => true);
        }
    }
}