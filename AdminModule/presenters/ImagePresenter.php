<?php

namespace AdminModule;

require LIBS_DIR.'/imagine-v0.3.0.phar';

use Nette\Application\Responses\JsonResponse; 
use Nette\Image;

class ImagePresenter extends \BasePresenter {
	
	public function actionUpload()
	{
		$file = $this->getHttpRequest()->getFile('file');
		$this->getHttpResponse()->setHeader('X-Frame-Options', NULL);
		$image = $file->toImage();
		$baseFileName = md5(time())."_".$file->getSanitizedName();
		$path = 'uploads/photos';
		$image->save(WWW_DIR.'/'.$path.'/'.$baseFileName, 100);
		unset($image);
		// $image->resize(210, 230);
		// $image->save(WWW_DIR.'/'.$path.'/t_'.$baseFileName, 100);
				

		$imagine = new \Imagine\Gd\Imagine();
		$transformation = new \Imagine\Filter\Transformation();
		$transformation->thumbnail(new \Imagine\Image\Box(210, 230), \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND);
		$transformation->apply($imagine->open(WWW_DIR.'/'.$path.'/'.$baseFileName))
        	->save(WWW_DIR.'/'.$path.'/t_'.$baseFileName);


		$url = "http://spse-po.sk/newsletter/system/";

		$res = new JsonResponse(array ( 'filelink' => $url.$path.'/'.$baseFileName, 'thumb' => $url.$path.'/t_'.$baseFileName) );
		$this->sendResponse($res); 
	}

}