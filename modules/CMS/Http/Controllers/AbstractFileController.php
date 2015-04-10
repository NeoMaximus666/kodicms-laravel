<?php namespace KodiCMS\CMS\Http\Controllers;

use KodiCMS\CMS\Assets\Core as Assets;
use KodiCMS\Users\Model\UserRole;

abstract class AbstractFileController extends System\BackendController {

	/**
	 * @var FileCollection
	 */
	protected $collection;

	/**
	 * @var string
	 */
	protected $sectionPrefix;

	/**
	 * @var array
	 */
	protected $editors = NULL;

	/**
	 * @return FileCollection
	 */
	abstract protected function getCollection();

	/**
	 * @return string
	 */
	abstract protected function getSectionPrefix();

	public function before()
	{
		parent::before();
		$this->collection = $this->getCollection();
		$this->sectionPrefix = $this->getSectionPrefix();
	}

	public function getIndex()
	{
		$this->setContent("{$this->sectionPrefix}.list", [
			'collection' => $this->collection
		]);
	}

	public function getCreate()
	{
		$file = $this->getFile();
		$roles = UserRole::lists('name', 'name');

		$this->setTitle(trans("{$this->moduleNamespace}{$this->sectionPrefix}.title.create"));
		$this->templateScripts['FILE'] = $file->toArray();

		$this->setContent("{$this->sectionPrefix}.create", compact('file', 'roles'));
	}

	public function postCreate()
	{
		$data = $this->request->all();
		$file = $this->getFile();

		$file->fill(array_only($data, ['name', 'content', 'editor', 'roles']));

		$validator = $file->validator();

		if ($validator->fails()) {
			$this->throwValidationException(
				$this->request, $validator
			);
		}

		$this->collection
			->saveFile($file)
			->saveSettings();

		return $this->smartRedirect(['name' => $file->getName()])
			->with('success', trans("{$this->moduleNamespace}{$this->sectionPrefix}.messages.created", ['name' => $file->getName()]));
	}

	public function getEdit($filename)
	{
		$file = $this->getFile($filename);
		$roles = UserRole::lists('name', 'name');

		$this->setTitle(trans("{$this->moduleNamespace}{$this->sectionPrefix}.title.edit", [
			'name' => $file->getName()
		]));

		$this->templateScripts['FILE'] = $file->toArray();

		$this->setContent("{$this->sectionPrefix}.edit", compact('file', 'roles'));
	}

	public function postEdit($filename)
	{
		$data = $this->request->all();

		$file = $this->getFile($filename);
		$file->fill(array_only($data, ['name', 'content', 'editor', 'roles']));
		$validator = $file->validator();

		if ($validator->fails()) {
			$this->throwValidationException(
				$this->request, $validator
			);
		}

		$this->collection
			->saveFile($file)
			->saveSettings();

		return $this->smartRedirect(['name' => $file->getName()])
			->with('success', trans("{$this->moduleNamespace}{$this->sectionPrefix}.messages.updated", ['name' => $file->getName()]));
	}

	public function getDelete($filename)
	{
		$this->autoRender = FALSE;

		$file = $this->getFile($filename);

		if($file->delete())
		{
			return $this->smartRedirect()
				->with('success', trans("{$this->moduleNamespace}{$this->sectionPrefix}.messages.deleted", ['name' => $file->getName()]));
		}

		return $this->smartRedirect()
			->withErrors(trans("{$this->moduleNamespace}{$this->sectionPrefix}.messages.not_deleted"));
	}

	public function getFile($filename = NULL)
	{
		if(is_array($this->editors))
		{
			Assets::package($this->editors);
		}

		if(is_null($filename))
		{
			return $this->collection->newFile();
		}

		if($file =$this->collection->findFile($filename))
		{
			return $file;
		}

		$this->throwFailException($this->smartRedirect()->withErrors(trans("{$this->moduleNamespace}{$this->sectionPrefix}.messages.not_found")));
	}
}
