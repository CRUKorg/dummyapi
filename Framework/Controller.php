<?php
// Require the parent model as well for inheritance.
require_once('Model.php');

/**
 * Framework Parent Controller.
 * Parent controller that provides functions to children.
 *
 * @package: Framework
 * @category: Controller
 * @author: Kris Pomphrey <kris@krispomphrey.co.uk>
 */
class Controller{
  /**
  * Variable that will hold other the current controllers model object.
  * @var object
  */
	public $model;

  /**
  * Variable that will hold other the current controllers view object.
  * @var object
  */
	public $view;

  /**
  * Variable will hold the layout to render for the controller.
  * @var string
  */
	public $layout = 'index';

  /**
  * Variable that will hold other the router object.
  * @var object
  */
	public $router;
	public $queue = array();
	public $messages = array();
	// Protect the controller (i.e. needs login).
	public $protected = 0;
	public $login = 0;
	// An array of ALLOW or DENY access levels.
	public $auth = array();

  /**
   * Implements debug();
   *
   * Function to output debug information.
   */
	public function debug($data){
		$config = new Config();
		if($config->debug == 1){
			echo '<pre>';
			var_dump($data);
			echo '</pre>';
		}
	}

  /**
   * Implements __construct();
   *
   * The main Controller constructor.
   */
	public function __construct(&$router){
		$this->router = &$router;
		$this->pre_init();
		$this->init();
	}

  /**
   * Implements pre_init();
   *
   * pre_init is fired before a page is rendered.
   * It is generally used to attach assets.
   */
	public function pre_init(){}

  /**
   * Implements init();
   *
   * Fired when a page is ready to be rendered.
   */
	public function init(){}

  /**
   * Implements render();
   *
   * Render is used to include the layout difined in the context.
   */
	public function render($view, $admin = false){
		if($admin) $path = ADMIN_LAYOUT_ROOT;
		else $path = LAYOUT_ROOT;
		include_once($path."{$this->layout}.php");
	}

  /**
   * Implements view();
   *
   * Includes the view file defined.
   */
	public function view($view, $admin = false){
		if($admin) $path = ADMIN_VIEW_ROOT;
		else $path = VIEW_ROOT;
		include_once($path."{$view}.php");
	}

  /**
   * Implements layout();
   *
   * Includes the layout file defined.
   */
	public function layout($layout, $admin = false){
		if($admin) $path = ADMIN_LAYOUT_ROOT;
		else $path = LAYOUT_ROOT;
		include_once($path."{$layout}.php");
	}

  /**
   * Implements model();
   *
   * Includes the model file defined.
   * Sets up the model in the context.
   */
	public function model($model){
		include_once(MODEL_ROOT."{$model}.php");
		$model = $model.'Model';
		$this->model = new $model($this->router);
	}

  /**
   * Implements incl();
   *
   * Include a custom php file (minus .php).
   */
	public function incl($inc){
		include_once("{$inc}.php");
	}

  /**
   * Implements asset();
   *
   * Add an asset (js/css) to the queue array.
   */
	public function asset($type, $file, $admin = false){
		$path = null;
		if($admin) $path = '/Framework/Admin';
		$this->queue[$type][] = "$path/assets/$type/$file";
	}

  /**
   * Implements flush_queue();
   *
   * Echos everything in the queue (js/css).
   * TODO: Remove HTML from code.
   */
	public function flush_queue(){
		if(!empty($this->queue) && is_array($this->queue)){
			foreach($this->queue as $key => $value){
				foreach($value as $q){
					switch($key){
						case 'css': echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"$q\" />\n"; break;
						case 'js': echo "<script src=\"$q\"></script>\n"; break;
					}
				}
			}
		}
	}
}
