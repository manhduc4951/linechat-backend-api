<?php

class Api_StickerPackageController extends Qsoft_Rest_Controller
{

    protected $_daoClass = 'Dao_StickerPackage';

    /**
     * Search list of sticker package
     */
    public function indexAction()
    {
        $stickerPackage = $this->getDao()->fetchAll();

        $this->success(array('sticker-package' => $stickerPackage->toEndUserArray()));
    }

}
