<?php

/**
 * This source file is a part of the Knife CLI Tool for Fork CMS.
 * More information can be found on http://www.fork-cms.com
 *
 * @package		knife
 *
 * @author		Jelmer Snoeck <jelmer.snoeck@netlash.com>
 * @since		0.2
 */
class KnifeModuleGenerator extends KnifeBaseGenerator
{
	/**
	 * The module name.
	 *
	 * @var	string
	 */
	private $moduleName;

	/**
	 * The actions
	 *
	 * @var array
	 */
	private $moduleActions;

	/**
	 * This starts the generator.
	 */
	public function init()
	{
		// name given?
		if(!isset($this->arg[0])) throw new Exception('Please specify a module name');

		// clean the name
		$this->moduleName = $this->cleanString($this->arg[0]);

		// create the module
		$return = $this->createModule();

		// error handling
		if(!$return) $this->errorHandler(__CLASS__, 'createModule');
		else $this->successHandler('The module "' . ucfirst($this->moduleName) . '" is created.');
	}

	/**
	 * Create the directories
	 */
	private function createDirs()
	{
		// the backend
		$backendDirs = array(
						'main' => BACKENDPATH . 'modules/' . $this->buildDirName($this->moduleName),
						'sub' => array(
									'actions', 'ajax',
									'engine' => array('cronjobs'),
									'installer' => array('data'),
									'js', 'widgets',
									'layout' => array('templates', 'widgets')
						)
		);

		// make the backend directories
		$this->makeDirs($backendDirs);

		// the frontend
		$frontendDirs = array(
							'main' => FRONTENDPATH . 'modules/' . $this->buildDirName($this->moduleName),
							'sub' => array(
										'actions', 'ajax', 'engine', 'widgets',
										'layout' => array('templates', 'widgets'),
										'js'
							)
		);

		// make the frontend directories
		$this->makeDirs($frontendDirs);
	}

	/**
	 * Create the files
	 */
	private function createFiles()
	{
		/*
		 * Backend files
		 */
		$backendPath = BACKENDPATH . 'modules/' . $this->buildDirName($this->moduleName) . '/';

		// model file
		$modelInput = $this->replaceFileInfo(CLIPATH . 'knife/module/base/backend/model.php');
		$this->makeFile($backendPath . 'engine/model.php', $modelInput);

		// index action
		$indexInput = $this->replaceFileInfo(CLIPATH . 'knife/action/base/backend/index.php');
		$this->makeFile($backendPath . 'actions/index.php', $indexInput);
		$indexInput = $this->replaceFileInfo(CLIPATH . 'knife/action/base/backend/index.tpl');
		$this->makeFile($backendPath . 'layout/templates/index.tpl', $indexInput);

		// config file
		$configInput = $this->replaceFileInfo(CLIPATH . 'knife/module/base/backend/config.php');
		$this->makeFile($backendPath . 'config.php', $configInput);

		// info
		$infoInput = $this->replaceFileInfo(CLIPATH . 'knife/module/base/backend/info.xml');
		$this->makeFile($backendPath . 'info.xml', $infoInput);

		$installInput = $this->replaceFileInfo(CLIPATH . 'knife/module/base/backend/install.php');
		$this->makeFile($backendPath . 'installer/install.php', $installInput);

		// locale
		$localeInput = $this->replaceFileInfo(CLIPATH . 'knife/module/base/backend/locale.xml');
		$this->makeFile($backendPath . 'installer/data/locale.xml', $localeInput);

		// install sql file
		$this->makeFile($backendPath . 'installer/data/install.sql');

		// javascript
		$jsInput = $this->replaceFileInfo(CLIPATH . 'knife/module/base/backend/javascript.js');
		$this->makeFile($backendPath . 'js/' . strtolower($this->moduleName) . '.js', $jsInput);

		/*
		 * Frontend files
		 */
		$frontendPath = FRONTENDPATH . 'modules/' . $this->buildDirName($this->moduleName) . '/';

		// model
		$modelInput = $this->replaceFileInfo(CLIPATH . 'knife/module/base/frontend/model.php');
		$this->makeFile($frontendPath . 'engine/model.php', $modelInput);

		// config
		$configInput = $this->replaceFileInfo(CLIPATH . 'knife/module/base/frontend/config.php');
		$this->makeFile($frontendPath . 'config.php', $configInput);

		// javascript
		$jsInput = $this->replaceFileInfo(CLIPATH . 'knife/module/base/frontend/javascript.js');
		$this->makeFile($frontendPath . 'js/' . strtolower($this->moduleName) . '.js', $jsInput);

		// index action
		$indexInput = $this->replaceFileInfo(CLIPATH . 'knife/action/base/frontend/index.php');
		$this->makeFile($frontendPath . 'actions/index.php', $indexInput);
		$indexInput = $this->replaceFileInfo(CLIPATH . 'knife/action/base/frontend/index.tpl');
		$this->makeFile($frontendPath . 'layout/templates/index.tpl', $indexInput);
	}

	/**
	 * This action creates a module. This will not overwrite an existing module.
	 *
	 * The data needed for this action: 'modulename'
	 * The optional data for this action: -f 'frontendaction1:frontendaction2' -b 'backendaction1:backendaction2'
	 *
	 * Example: ft module blog -f 'detail:category' -b 'add:edit'
	 * This will create the module 'blog' with the frontendactions detail and category, and the backend actions add and edit.
	 */
	protected function createModule()
	{
		// module already exists
		if(is_dir(FRONTENDPATH . 'modules/' . strtolower($this->moduleName)) || is_dir(BACKENDPATH . 'modules/' . strtolower($this->moduleName))) return false;

		// create the directories
		$this->createDirs();

		// create the files
		$this->createFiles();

		// insert into the database
		if(!$this->databaseInfo()) return false;
		// @todo action stuff

		return true;
	}

	/**
	 * Create the database info
	 */
	private function databaseInfo()
	{

	}

	/**
	 * Replaces all the info in a file
	 *
	 * @return	string
	 * @param	string $file		The file to replace the info from
	 */
	private function replaceFileInfo($file)
	{
		// replace
		$fileInput = $this->readFile($file);
		$fileInput = str_replace('classname', $this->buildName($this->moduleName), $fileInput);
		$fileInput = str_replace('subname', strtolower($this->buildName($this->moduleName)), $fileInput);
		$fileInput = str_replace('versionname', VERSION, $fileInput);
		$fileInput = str_replace('authorname', AUTHOR, $fileInput);

		// return
		return $fileInput;
	}
}