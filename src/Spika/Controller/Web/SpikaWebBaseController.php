<?php

/*
 * This file is part of the Silex framework.
 *
 * Copyright (c) 2013 clover studio official account
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spika\Controller\Web;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Guzzle\Http\Client;
use Guzzle\Plugin\Async\AsyncPlugin;
use Spika\Controller\FileController;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;

class SpikaWebBaseController implements ControllerProviderInterface
{
    
    var $language = array();
    var $messages = array();
    var $loginedUser = null;
    
    public function __construct(){
        
        $currentLanguage = "en";
        if(defined("DEFAULT_LANGUAGE")){
            $currentLanguage = DEFAULT_LANGUAGE;
        }
        
        $languageFile = __DIR__."/../../../../config/i18n/{$currentLanguage}.ini";
        
        $this->language = parse_ini_file($languageFile);
        
    }
    
    public function connect(Application $app)
    {

        $this->app = $app;
        $controllers = $app['controllers_factory'];
        $this->loginedUser = $this->app['session']->get('user');
        return $controllers;        
    }
    
    public function render($tempalteFile,$params){
        
        $user = $this->app['session']->get('user');
        $params['loginedUser'] = $user;
        
        $params['isAdmin'] = $user['_id'] == 1;
        
        
        $params['lang'] = $this->language;
        $params['ROOT_URL'] = ROOT_URL;
        
        if(isset($this->messages['info']))
            $params['infoMessage'] = $this->messages['info'];
            
        if(isset($this->messages['error']))
            $params['errorMessage'] = $this->messages['error'];

        return $this->app['twig']->render($tempalteFile,$params);
            
    }
    
    public function setInfoAlert($message){
        
        $this->messages['info'] = $message;
        
    }
    
    public function setErrorAlert($message){
        
        $this->messages['error'] = $message;
        
    }
    
    public function savePicture($file){
    
        $uploadDirPath = __DIR__.'/../../../../' . FileController::$fileDirName . '/';
        $fileName = \Spika\Utils::randString(20, 20) . time();

        // resize and save file
        $imagine = new Imagine();
        $image = $imagine->open($file->getPathname());
        $size = $image->getSize();
        
        $targetSize = $size->getWidth();
        if($size->getHeight() < $size->getWidth())
            $targetSize = $size->getHeight();
            
        $originX = ($size->getWidth() - $targetSize) / 2;
        $originY = ($size->getHeight() - $targetSize) / 2;
        
        $image->crop(new Point($originX,$originY), new Box($targetSize,$targetSize))
                ->resize(new Box(640, 640))
                ->save($uploadDirPath.$fileName,array('format'=>'jpg'));

        return $fileName;
        
    }
    
    public function saveThumb($file){
    
        $uploadDirPath = __DIR__.'/../../../../' . FileController::$fileDirName . '/';
        $fileName = \Spika\Utils::randString(20, 20) . time();

        // resize and save file
        $imagine = new Imagine();
        $image = $imagine->open($file->getPathname());
        $size = $image->getSize();
        
        $targetSize = $size->getWidth();
        if($size->getHeight() < $size->getWidth())
            $targetSize = $size->getHeight();
            
        $originX = ($size->getWidth() - $targetSize) / 2;
        $originY = ($size->getHeight() - $targetSize) / 2;
        
        $image->crop(new Point($originX,$originY), new Box($targetSize,$targetSize))
                ->resize(new Box(120, 120))
                ->save($uploadDirPath.$fileName,array('format'=>'jpg'));

        return $fileName;

    }
    
    public function checkPermission(){
        
        return $this->loginedUser['_id'] == SUPPORT_USER_ID;
        
    }
            
}

?>
