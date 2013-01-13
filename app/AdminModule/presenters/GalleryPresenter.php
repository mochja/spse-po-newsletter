<?php

namespace AdminModule;

use Nette\Application\Responses\JsonResponse;
use Nette\Utils\Strings;
use Nette\Utils\Finder;
use Nette\Image;

class GalleryPresenter extends \BasePresenter
{

	private $path = '/uploads/photos';

	public function actionUpload()
	{
		$json = file_get_contents('php://input');
		$req = json_decode($json);

		foreach( $req->files as $file ) {
			if (count($file) != 3) {
				continue;
			}
			$filename = substr(trim($file[2]), 0, 15);
			$name = Strings::random(8).'_'.Strings::webalize($filename).'.jpg';
			$picture = Image::fromString( base64_decode(substr($file[0], 22)) );
			$picture->save( WWW_DIR.$this->path.'/'.$name, 100);
			$picture = Image::fromString( base64_decode(substr($file[1], 22)) );
			$picture->save( WWW_DIR.$this->path.'/t_'.$name, 100);
		}

		$res = new JsonResponse(array(
			"uploaded" => count($req->files)
		));
		$this->sendResponse($res);

	}

	public function actionPictures()
	{
		$basePath = preg_replace("#https?://[^/]+#A", "", rtrim($this->context->httpRequest->url->baseUrl, "/"));

		$filelist = array();
		foreach (Finder::findFiles('t_*.jpg')->in(WWW_DIR.$this->path) as $key => $file)
		{
			$title = $file->getFilename();
			if ($offset = strrpos($title, '_')) {
				$title = substr($title, $offset+1);
			}
			if ($offset = strrpos($title, '.')) {
				$title = substr($title, 0, $offset);
			}
			$filelist[] = array(
				'thumb' => $basePath.'/system/'.$this->path.'/'.$file->getFilename(),
				'image' => $basePath.'/system/'.$this->path.'/'.substr($file->getFilename(), 2),
				'title' => $title,
				'folder' => 'default'
			);
		}
		$res = new JsonResponse($filelist);
		$this->sendResponse($res);
	}

}